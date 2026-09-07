<?php

namespace App\Modules\Platform\Support;

/**
 * The vocabulary of mechanics a case can use.
 *
 * A case manifest declares which mechanics it has as bare slugs; this is where
 * those slugs get a name and an explanation. It lives in the platform, not in
 * a case, because a mechanic is a capability of the engine that cases opt into
 * — the same "interrogation" must mean the same thing on every case page.
 *
 * Copy, not configuration: it is content the marketing pages render, so it
 * belongs in code that can be reviewed rather than in config/.
 */
class Mechanics
{
    /**
     * @return array<string, array{name: string, summary: string, detail: string, ai: bool}>
     */
    public static function all(): array
    {
        return [
            'inbox' => [
                'name' => 'Bandeja de entrada',
                'summary' => 'El expediente llega por correo, en tiempo real.',
                'detail' => 'Cada jugador tiene su propia bandeja y recibe correos reales durante la partida: informes policiales, comunicados, mensajes de la detective a cargo. No se entrega todo de golpe — el caso se va abriendo mientras juegan.',
                'ai' => false,
            ],
            'timeline' => [
                'name' => 'Eventos cronometrados',
                'summary' => 'La investigación avanza sola con el reloj.',
                'detail' => 'El caso tiene una línea de tiempo propia: a los 10 minutos llega el expediente, a los 45 un mensaje de voz, a los 75 se habilitan las acusaciones. El Game Master puede pausar, reanudar o adelantar un evento cuando la mesa lo necesita.',
                'ai' => false,
            ],
            'gallery' => [
                'name' => 'Evidencia visual',
                'summary' => 'Fotos, recortes de prensa y documentos escaneados.',
                'detail' => 'Reportes de autopsia, etiquetas de evidencia, capturas de redes sociales, recibos, registros de entrada. Se ven como lo que son y se pueden abrir a tamaño completo para leer la letra pequeña.',
                'ai' => false,
            ],
            'audio' => [
                'name' => 'Mensajes de voz',
                'summary' => 'Grabaciones que hay que escuchar, no leer.',
                'detail' => 'Llamadas y notas de voz que llegan adjuntas al correo. Un dato dicho en voz alta se siente distinto a uno escrito, y una llamada anónima que solo le llega a un jugador cambia la conversación de la mesa.',
                'ai' => true,
            ],
            'interrogation' => [
                'name' => 'Interrogatorios',
                'summary' => 'Pregúntale a los sospechosos y te responden.',
                'detail' => 'Un chat con cada persona del caso, con un número limitado de preguntas. Cada sospechoso pertenece al primer jugador que lo interroga de verdad, así que el equipo tiene que repartirse y después compartir lo que averiguó.',
                'ai' => true,
            ],
            'accusation' => [
                'name' => 'Acusación final',
                'summary' => 'Quién, con qué y por qué.',
                'detail' => 'Al final cada jugador entrega su acusación. El Game Master ve todas juntas y las compara antes de revelar la solución.',
                'ai' => false,
            ],
        ];
    }

    /**
     * Every mechanic as a flat list with its slug folded in, ready to render.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function list(): array
    {
        $list = [];

        foreach (self::all() as $slug => $mechanic) {
            $list[] = $mechanic + ['slug' => $slug];
        }

        return $list;
    }

    /**
     * Split into the mechanics that need AI and the ones that do not. The
     * public AI page is built on being explicit about exactly this.
     *
     * @return array{withAi: array<int, array<string, mixed>>, withoutAi: array<int, array<string, mixed>>}
     */
    public static function partitionByAi(): array
    {
        $withAi = [];
        $withoutAi = [];

        foreach (self::list() as $mechanic) {
            $mechanic['ai'] ? $withAi[] = $mechanic : $withoutAi[] = $mechanic;
        }

        return ['withAi' => $withAi, 'withoutAi' => $withoutAi];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Resolve a case's mechanic slugs into full entries, skipping any slug the
     * platform does not know — an unrecognised mechanic should not break a
     * catalog page.
     *
     * @param  string[]  $slugs
     * @return array<int, array<string, mixed>>
     */
    public static function describe(array $slugs): array
    {
        $described = [];

        foreach ($slugs as $slug) {
            if ($mechanic = self::find($slug)) {
                $described[] = $mechanic + ['slug' => $slug];
            }
        }

        return $described;
    }
}
