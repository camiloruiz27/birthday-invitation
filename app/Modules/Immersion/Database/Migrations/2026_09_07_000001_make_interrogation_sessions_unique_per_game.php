<?php

use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deleteDuplicateSessions();

        Schema::table('immersion_interrogation_sessions', function (Blueprint $table) {
            // MySQL/InnoDB usa el unique(player_id, suspect_slug) para
            // respaldar la foreign key de player_id (no tenia otro indice
            // propio). Sin un indice de reemplazo, MySQL rechaza el DROP
            // del unique con el error 1553 ("needed in a foreign key
            // constraint") - le damos ese reemplazo primero.
            $table->index('player_id');
            $table->dropUnique(['player_id', 'suspect_slug']);
            $table->unique(['game_id', 'suspect_slug']);
        });
    }

    public function down(): void
    {
        Schema::table('immersion_interrogation_sessions', function (Blueprint $table) {
            $table->unique(['player_id', 'suspect_slug']);
            $table->dropUnique(['game_id', 'suspect_slug']);
            $table->dropIndex(['player_id']);
        });
    }

    /**
     * A partir de este cambio, una sesion de interrogatorio es unica por
     * (game_id, suspect_slug) en vez de (player_id, suspect_slug). Si ya
     * existian varios jugadores interrogando al mismo sospechoso en una
     * misma partida (dato real de pruebas), hay que quedarse con una sola
     * fila por grupo antes de poder crear el nuevo indice unico. Se conserva
     * la sesion con mas preguntas usadas (la mas avanzada); en empate, la de
     * menor id. Los mensajes de las sesiones descartadas se borran solos por
     * el cascadeOnDelete() de immersion_interrogation_messages.
     */
    private function deleteDuplicateSessions(): void
    {
        $groups = InterrogationSession::query()
            ->selectRaw('game_id, suspect_slug')
            ->groupBy('game_id', 'suspect_slug')
            ->havingRaw('count(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $sessions = InterrogationSession::query()
                ->where('game_id', $group->game_id)
                ->where('suspect_slug', $group->suspect_slug)
                ->orderByDesc('questions_used')
                ->orderBy('id')
                ->get();

            $sessions->skip(1)->each(fn (InterrogationSession $session) => $session->delete());
        }
    }
};
