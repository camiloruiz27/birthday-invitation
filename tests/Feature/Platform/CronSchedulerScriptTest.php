<?php

namespace Tests\Feature\Platform;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

/**
 * The script the hosting panel's cron runs every minute. Booting Laravel just to
 * learn "nothing is due yet" is a cost of its own (CPU, and a database connection
 * whenever the tick then looks), so the script reads the moment the application
 * left for it and leaves without starting PHP when it is not here yet.
 *
 * It is a shell script, so it is tested as one: copied into a scratch project,
 * with a stand-in for PHP that records what it was asked to run.
 */
class CronSchedulerScriptTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $probe = new Process(['sh', '-c', 'echo ok']);
        $probe->run();

        if (trim($probe->getOutput()) !== 'ok') {
            $this->markTestSkipped('No POSIX sh on this machine.');
        }

        $this->dir = str_replace('\\', '/', sys_get_temp_dir()).'/cron-script-'.uniqid();

        File::makeDirectory($this->dir.'/storage/framework', 0777, true);
        File::makeDirectory($this->dir.'/storage/logs', 0777, true);
        File::makeDirectory($this->dir.'/bin', 0777, true);

        File::copy(base_path('cron-scheduler.sh'), $this->dir.'/cron-scheduler.sh');

        // A stand-in for the PHP binary: records its arguments and succeeds.
        File::put($this->dir.'/bin/php', "#!/bin/sh\necho \"\$@\" >> \"{$this->dir}/calls.txt\"\nexit 0\n");
        @chmod($this->dir.'/bin/php', 0755);
    }

    protected function tearDown(): void
    {
        if (isset($this->dir)) {
            File::deleteDirectory($this->dir);
        }

        parent::tearDown();
    }

    /** @return array{0: int, 1: string} exit code and what PHP was asked to run */
    private function runScript(int $now, int $minute, ?string $next, array $env = []): array
    {
        if ($next !== null) {
            File::put($this->dir.'/storage/framework/next-run', $next);
        }

        $process = new Process(['sh', $this->dir.'/cron-scheduler.sh'], $this->dir, array_merge([
            'PHP_BIN' => $this->dir.'/bin/php',
            'CRON_NOW' => (string) $now,
            'CRON_MINUTE' => str_pad((string) $minute, 2, '0', STR_PAD_LEFT),
        ], $env));
        $process->run();

        $calls = File::exists($this->dir.'/calls.txt') ? trim(File::get($this->dir.'/calls.txt')) : '';
        File::delete($this->dir.'/calls.txt');

        return [$process->getExitCode(), $calls];
    }

    public function test_it_does_not_start_php_when_nothing_is_due(): void
    {
        [$exit, $calls] = $this->runScript(now: 1000, minute: 3, next: '1600');

        $this->assertSame(0, $exit);
        $this->assertSame('', $calls);
        $this->assertFileDoesNotExist($this->dir.'/storage/logs/cron-scheduler.log', 'Not even a log line: nothing ran.');
    }

    public function test_it_runs_just_the_tick_when_something_is_due(): void
    {
        [, $calls] = $this->runScript(now: 1700, minute: 3, next: '1600');

        $this->assertSame('artisan immersion:tick', $calls);
    }

    public function test_a_moment_exactly_now_is_due(): void
    {
        [, $calls] = $this->runScript(now: 1600, minute: 3, next: '1600');

        $this->assertSame('artisan immersion:tick', $calls);
    }

    public function test_every_five_minutes_it_runs_the_whole_scheduler_whatever_the_file_says(): void
    {
        foreach ([0, 5, 10, 25, 55] as $minute) {
            [, $calls] = $this->runScript(now: 1000, minute: $minute, next: '9999999999');

            $this->assertSame('artisan schedule:run', $calls, "minute {$minute}");
        }
    }

    public function test_other_minutes_do_not_run_the_scheduler(): void
    {
        foreach ([1, 2, 3, 4, 6, 8, 9, 11] as $minute) {
            [, $calls] = $this->runScript(now: 5000, minute: $minute, next: '0');

            $this->assertSame('artisan immersion:tick', $calls, "minute {$minute}");
        }
    }

    public function test_an_unknown_moment_runs_the_tick(): void
    {
        // No file at all, an empty one, rubbish, and the application's own "wake now".
        foreach ([null, '', 'abc', '-5', '0'] as $next) {
            @unlink($this->dir.'/storage/framework/next-run');

            [, $calls] = $this->runScript(now: 1000, minute: 3, next: $next);

            $this->assertSame('artisan immersion:tick', $calls, var_export($next, true));
        }
    }

    public function test_it_reports_what_artisan_returned(): void
    {
        File::put($this->dir.'/bin/php', "#!/bin/sh\nexit 3\n");

        [$exit] = $this->runScript(now: 1000, minute: 5, next: null);

        $this->assertSame(3, $exit);
        $this->assertStringContainsString('exit code 3', File::get($this->dir.'/storage/logs/cron-scheduler.log'));
    }

    public function test_it_logs_which_command_it_ran(): void
    {
        $this->runScript(now: 1000, minute: 5, next: null);

        $this->assertStringContainsString('Cron started (schedule:run)', File::get($this->dir.'/storage/logs/cron-scheduler.log'));
    }

    public function test_the_log_is_trimmed_when_it_grows_past_the_limit(): void
    {
        $lines = [];
        foreach (range(1, 5000) as $i) {
            $lines[] = "[old] line {$i}";
        }
        File::put($this->dir.'/storage/logs/cron-scheduler.log', implode("\n", $lines)."\n");

        $this->runScript(now: 1000, minute: 5, next: null, env: ['LOG_MAX_BYTES' => '1000']);

        $log = File::get($this->dir.'/storage/logs/cron-scheduler.log');

        $this->assertLessThan(2200, substr_count($log, "\n"), 'Only the last lines are kept.');
        $this->assertStringContainsString('line 5000', $log, 'The newest history survives.');
        $this->assertStringNotContainsString('line 10]', $log);
        $this->assertStringContainsString('Cron started', $log);
    }

    public function test_a_small_log_is_left_alone(): void
    {
        File::put($this->dir.'/storage/logs/cron-scheduler.log', "[old] a\n[old] b\n");

        $this->runScript(now: 1000, minute: 5, next: null);

        $log = File::get($this->dir.'/storage/logs/cron-scheduler.log');

        $this->assertStringContainsString('[old] a', $log);
        $this->assertStringContainsString('[old] b', $log);
    }

    public function test_it_is_valid_posix_sh(): void
    {
        $process = new Process(['sh', '-n', base_path('cron-scheduler.sh')]);
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
    }
}
