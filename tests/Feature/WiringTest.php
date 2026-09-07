<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

/**
 * Guards the seams between PHP and the front end, which no other test touches
 * because both halves look fine on their own.
 *
 * A `route('foo')` naming a route that does not exist throws in the browser and
 * blanks the page. An `Inertia::render('Foo/Bar')` with no matching file does
 * the same. Both are one careless rename away at any time, and neither shows up
 * in a build.
 */
class WiringTest extends TestCase
{
    /**
     * @return array<int, SplFileInfo>
     */
    private function filesIn(string $path, string $extension): array
    {
        if (! is_dir($path)) {
            return [];
        }

        $files = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                $files[] = $file;
            }
        }

        return $files;
    }

    public function test_every_route_named_in_the_front_end_exists(): void
    {
        $registered = collect(Route::getRoutes()->getRoutesByName())->keys()->all();

        $missing = [];

        foreach ($this->filesIn(resource_path('js'), 'jsx') as $file) {
            preg_match_all(
                "/route\(\s*'([a-zA-Z0-9_.\-]+)'/",
                (string) file_get_contents($file->getPathname()),
                $matches
            );

            foreach ($matches[1] as $name) {
                if (! in_array($name, $registered, true)) {
                    $missing[] = sprintf(
                        '%s references route("%s")',
                        str_replace(resource_path('js').DIRECTORY_SEPARATOR, '', $file->getPathname()),
                        $name
                    );
                }
            }
        }

        $this->assertSame([], array_unique($missing), "Broken links:\n".implode("\n", array_unique($missing)));
    }

    public function test_every_inertia_page_rendered_by_a_controller_exists(): void
    {
        $missing = [];

        foreach ($this->filesIn(app_path(), 'php') as $file) {
            preg_match_all(
                "/Inertia::render\(\s*'([A-Za-z0-9\/_-]+)'/",
                (string) file_get_contents($file->getPathname()),
                $matches
            );

            foreach ($matches[1] as $component) {
                $page = resource_path('js/Pages/'.$component.'.jsx');

                if (! is_file($page)) {
                    $missing[] = sprintf(
                        '%s renders "%s" but Pages/%s.jsx does not exist',
                        str_replace(app_path().DIRECTORY_SEPARATOR, '', $file->getPathname()),
                        $component,
                        $component
                    );
                }
            }
        }

        $this->assertSame([], $missing, "Missing pages:\n".implode("\n", $missing));
    }

    public function test_every_page_component_is_reachable(): void
    {
        // A page nobody renders is dead weight that still gets bundled, and it
        // usually means a controller was changed and its old page left behind.
        $rendered = [];

        foreach ($this->filesIn(app_path(), 'php') as $file) {
            preg_match_all(
                "/Inertia::render\(\s*'([A-Za-z0-9\/_-]+)'/",
                (string) file_get_contents($file->getPathname()),
                $matches
            );

            $rendered = array_merge($rendered, $matches[1]);
        }

        $orphans = [];

        foreach ($this->filesIn(resource_path('js/Pages'), 'jsx') as $file) {
            $component = str_replace(
                [resource_path('js/Pages').DIRECTORY_SEPARATOR, '.jsx', DIRECTORY_SEPARATOR],
                ['', '', '/'],
                $file->getPathname()
            );

            if (! in_array($component, $rendered, true)) {
                $orphans[] = $component;
            }
        }

        $this->assertSame([], $orphans, "Pages no controller renders:\n".implode("\n", $orphans));
    }

    public function test_no_page_still_imports_a_deleted_component(): void
    {
        $broken = [];

        foreach ($this->filesIn(resource_path('js'), 'jsx') as $file) {
            preg_match_all(
                "/^import\s+.*?from\s+'(\.[^']+)'/m",
                (string) file_get_contents($file->getPathname()),
                $matches
            );

            foreach ($matches[1] as $relative) {
                $base = dirname($file->getPathname()).DIRECTORY_SEPARATOR.$relative;

                $exists = is_file($base)
                    || is_file($base.'.jsx')
                    || is_file($base.'.js');

                if (! $exists) {
                    $broken[] = sprintf(
                        '%s imports %s',
                        str_replace(resource_path('js').DIRECTORY_SEPARATOR, '', $file->getPathname()),
                        $relative
                    );
                }
            }
        }

        $this->assertSame([], $broken, "Broken imports:\n".implode("\n", $broken));
    }
}
