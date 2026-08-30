<?php

namespace App\Modules\Immersion\Support;

/**
 * Linea de tiempo por defecto que se adjunta a TODA partida nueva (creada
 * desde el panel del GM o desde el seeder de demo). Editar los tiempos o
 * los textos redactados para la mecanica se hace aqui, sin tocar
 * controladores ni jobs.
 *
 * Los eventos que citan un "Sobre" (source_file) usan el contenido original
 * del caso tal cual, sin modificarlo. Los eventos marcados abajo como
 * "REDACTADO PARA LA MECANICA" son texto nuevo, escrito para esta partida
 * (no viene del material original), pero se mantiene consistente con los
 * hechos ya establecidos en sobre-1/2/3 y no introduce sospechosos, pruebas
 * ni coartadas nuevas.
 */
class DefaultTimeline
{
    public static function events(): array
    {
        return [
            [
                'type' => 'email',
                'trigger_offset_minutes' => 10,
                'title' => 'Sobre 1 - Expediente del caso',
                'source_file' => 'sobres/sobre-1.md',
                'delivery_mode' => 'all',
            ],
            [
                'type' => 'email',
                'trigger_offset_minutes' => 30,
                'title' => 'Actualizacion de la Detective Reed',
                // REDACTADO PARA LA MECANICA: no existe palabra por palabra en el
                // material original. Solo recapitula hechos ya enviados en sobre-1.
                'body_markdown' => <<<'MD'
                    **Actualizacion preliminar - Caso SF 554301**

                    A quien corresponda,

                    Les escribo para mantenerlos al tanto del estado de la investigacion
                    sobre la muerte de Steve Jacobs. Como ya saben, el informe forense
                    confirmo altos niveles de digitoxina en su sangre, y el frasco de
                    suplementos hallado en el bano presenta manipulacion evidente. No hay
                    senales de entrada forzada ni de violencia fisica, lo que sigue
                    apuntando a alguien con acceso directo a la habitacion 803 o a los
                    suplementos de la victima antes de esa noche.

                    La pieza de ajedrez - una torre blanca - sigue sin una explicacion
                    clara. No descarto que sea una firma deliberada de quien hizo esto,
                    pero por ahora la tratamos como una pista abierta, no como una
                    conclusion.

                    Les pedire que revisen con cuidado cualquier comunicacion adicional
                    que reciban en los proximos dias; en un caso con tantos involucrados
                    en HelixCare, es facil perder de vista un detalle pequeno que termina
                    siendo el que cierra el caso.

                    Sgt. Det. Michelle Reed
                    Departamento de Policia de San Francisco
                    MD,
                'delivery_mode' => 'all',
            ],
            [
                'type' => 'email',
                'trigger_offset_minutes' => 35,
                'title' => 'Sobre 2 - HelixCare e interrogatorios',
                'source_file' => 'sobres/sobre-2.md',
                'delivery_mode' => 'all',
                // Este correo, ademas del contenido verbatim del sobre, incluye
                // un boton para ir directo al interrogatorio de sospechosos
                // (Mecanica 7), si el GM la tiene habilitada.
                'cta_interrogation' => true,
            ],
            [
                'type' => 'audio_email',
                'trigger_offset_minutes' => 45,
                'title' => 'Mensaje de voz - Laboratorio forense',
                // REDACTADO PARA LA MECANICA: guion de TTS, voz del Dr. Goddard
                // (ya nombrado en el informe de laboratorio de sobre-1). Solo
                // reafirma en audio hallazgos que ya estan por escrito.
                'audio_script' => <<<'TXT'
                    Detective Reed, habla el doctor Goddard, del laboratorio forense.
                    Queria darle un adelanto verbal de lo que ya esta en mi reporte
                    escrito, para que el equipo lo tenga presente cuanto antes. Las
                    huellas del frasco de suplementos corresponden a Steve Jacobs y a
                    Daniel Blake; hay zonas borradas compatibles con el uso de guantes.
                    La concentracion de digitoxina dentro de una de las capsulas
                    coincide con lo que encontramos en el cuerpo de la victima, asi que
                    no hay duda de que ese frasco es el origen. Sobre la pieza de
                    ajedrez, seguimos sin huellas ni mensaje asociado. Le mando el
                    informe completo por escrito en un momento.
                    TXT,
                'delivery_mode' => 'all',
            ],
            [
                'type' => 'audio_email',
                'trigger_offset_minutes' => 50,
                'title' => 'Llamada de numero desconocido',
                // REDACTADO PARA LA MECANICA: llamada anonima ambigua (posible red
                // herring), solo para un jugador al azar. No contradice el material
                // original: se apoya en la torre blanca ya revelada en sobre-1.
                'audio_script' => <<<'TXT'
                    Numero desconocido. La llamada suena distorsionada.

                    ...hola? Se que estas investigando lo de Jacobs. La torre blanca no
                    es un accidente ni una casualidad, y esta no es la primera vez que
                    pasa. Si de verdad quieres entender que significa esa pieza, deja
                    de mirar solo el hotel. Mira quien mas ha aparecido con una torre
                    blanca cerca. No voy a decir nada mas por este medio.

                    La llamada se corta.
                    TXT,
                'delivery_mode' => 'random_player',
            ],
            [
                'type' => 'email',
                'trigger_offset_minutes' => 55,
                'title' => 'Alerta de monitoreo - Canal "Torre Blanca"',
                // El encabezado de una linea es marco de la mecanica (redactado);
                // el bloque bajo "---" es cita verbatim de sobre-3.md, sin resumir.
                'body_markdown' => <<<'MD'
                    Nuestro equipo de monitoreo de redes detecto actividad relevante
                    para el caso en un canal de YouTube. Contenido capturado a
                    continuacion, sin editar:

                    ---

                    ## Canal de YouTube "Torre Blanca"

                    Canal: **TORRE BLANCA** (@torreblanca) - 100 suscriptores - 53 videos
                    Descripcion: "Todas las noticias resumidas en un solo lugar..."

                    Videos destacados:
                    - "Steve Jacobs vendio informacion confidencial a cambio de millones. Pruebas claras." (Diciembre 2023)
                    - "10 hombres podridos hasta la medula. El mundo estaria mejor sin ellos." (Enero 2024)
                    - "Lista de 5,238 victimas afectadas por el fraude de HelixCare" (Marzo 2023)
                    MD,
                'delivery_mode' => 'all',
            ],
            [
                'type' => 'email',
                'trigger_offset_minutes' => 60,
                'title' => 'Sobre 3 - Redes, teorias y recibos',
                'source_file' => 'sobres/sobre-3.md',
                'delivery_mode' => 'all',
            ],
            [
                'type' => 'unlock',
                'trigger_offset_minutes' => 75,
                'title' => 'Fase de acusaciones habilitada',
                'body_markdown' => 'El Game Master habilito el formulario de acusacion final.',
                'delivery_mode' => 'all',
            ],
        ];
    }
}
