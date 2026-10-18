<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\EarlyRelease;
use Illuminate\Http\RedirectResponse;

/**
 * "Ya lei toda la informacion": a player says they finished reading what has
 * arrived. See EarlyRelease for what happens when the whole table has.
 *
 * Pressing it twice is harmless, so a double click or a retry after a slow
 * response needs no special handling here.
 */
class EnvelopeReadyController extends Controller
{
    public function store(Player $player): RedirectResponse
    {
        abort_unless(EarlyRelease::enabled(), 404);

        $result = EarlyRelease::confirm($player);

        abort_if($result === 'not_joined', 403, 'Acepta primero el aviso de privacidad.');
        abort_if($result === 'unavailable', 403, 'Ahora no hay un sobre que se pueda adelantar.');

        return back()->with('status', match ($result) {
            'released' => 'Todo el equipo termino de leer: llego el siguiente sobre.',
            default => 'Listo. Esperamos a que el resto del equipo termine de leer.',
        });
    }
}
