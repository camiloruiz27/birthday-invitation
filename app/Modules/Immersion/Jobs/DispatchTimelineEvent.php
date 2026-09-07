<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DispatchTimelineEvent implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The event is claimed under a lock before anything slow happens, so a
     * duplicate can never send twice; uniqueness just stops the queue filling
     * with jobs that will immediately no-op.
     */
    public int $tries = 2;

    public int $backoff = 30;

    /** Long enough for text-to-speech plus the whole mailing round. */
    public int $timeout = 240;

    public function __construct(public int $eventId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->eventId;
    }

    public function handle(SpeechProvider $audio): void
    {
        // La reclamacion del evento (bloqueo + marcar sent_at) es una
        // transaccion corta, solo de base de datos. Las llamadas externas
        // lentas (TTS de Gemini, envio de correo) van FUERA de la
        // transaccion: mantenerlas dentro dejaba la conexion a MySQL abierta
        // el tiempo suficiente para que un host remoto la cerrara por
        // inactividad ("MySQL server has gone away").
        $event = DB::transaction(function () {
            /** @var TimelineEvent|null $event */
            $event = TimelineEvent::query()->lockForUpdate()->find($this->eventId);

            if (! $event || $event->isSent()) {
                return null;
            }

            // Si aplica, elige y persiste ya al destinatario al azar, para
            // que ningun otro disparo concurrente de este mismo evento
            // pueda elegir a otra persona.
            TimelineRecipients::resolve($event);

            $event->sent_at = now();
            $event->save();

            return $event;
        });

        if (! $event) {
            return;
        }

        $this->unlockMechanics($event);

        $recipients = TimelineRecipients::resolve($event);

        if ($event->isAudio() && $event->audio_script && ! $event->audio_path) {
            $event->audio_path = $audio->synthesize($event->id, $event->audio_script);
            $event->audio_status = $event->audio_path
                ? TimelineEvent::AUDIO_READY
                : TimelineEvent::AUDIO_FAILED;
            $this->saveWithReconnect($event);
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new CaseTimelineMail($event, $recipient));
            } catch (\Throwable $exception) {
                // Un correo individual fallido (SMTP lento, etc.) no debe
                // impedir que le llegue a los demas destinatarios; el evento
                // ya quedo marcado como enviado, asi que no se reintentara solo.
                report($exception);
            }
        }
    }

    /**
     * Opens up whatever this event is meant to make available.
     *
     * Only in automatic mode: with a directing Game Master, unlocking is their
     * call and this must not take it away from them.
     *
     * An event flagged cta_interrogation is one that points players at the
     * suspects, so it is by definition the moment interrogation becomes
     * available. Reusing that flag means a case author controls the timing by
     * placing it, with no extra schema.
     */
    private function unlockMechanics(TimelineEvent $event): void
    {
        $game = $event->game;

        if (! $game->isAutomatic()) {
            return;
        }

        if ($event->cta_interrogation && ! $game->interrogation_enabled) {
            $game->update(['interrogation_enabled' => true]);
        }
    }

    /**
     * Guarda el modelo tolerando una conexion a MySQL que se cayo por
     * inactividad mientras esperabamos una llamada externa lenta.
     */
    private function saveWithReconnect(TimelineEvent $event): void
    {
        try {
            $event->save();
        } catch (QueryException $exception) {
            if (! str_contains($exception->getMessage(), 'gone away')) {
                throw $exception;
            }

            DB::reconnect();
            $event->save();
        }
    }
}
