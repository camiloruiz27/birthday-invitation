<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Services\GeminiAudioService;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DispatchTimelineEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $eventId)
    {
    }

    public function handle(GeminiAudioService $audio): void
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

        $recipients = TimelineRecipients::resolve($event);

        if ($event->isAudio() && $event->audio_script && ! $event->audio_path) {
            $event->audio_path = $audio->synthesize($event->id, $event->audio_script);
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
