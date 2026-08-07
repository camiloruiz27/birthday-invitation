<?php

namespace App\Support;

class CoupleActivityLibrary
{
    public static function days(): array
    {
        return [
            1 => ['day' => 1, 'title' => 'Llegada y reconexion', 'setting' => 'Primeras horas del viaje, hotel, caminata o una cena tranquila.'],
            2 => ['day' => 2, 'title' => 'Confianza y juego', 'setting' => 'Dia de playa, piscina, descanso o plan lento para disfrutarse.'],
            3 => ['day' => 3, 'title' => 'Deseo y complicidad', 'setting' => 'Atardecer, noche romantica o regreso al hotel despues de salir.'],
            4 => ['day' => 4, 'title' => 'Celebracion y cierre', 'setting' => 'Ultimo dia: celebrar, agradecer y cerrar el viaje con intencion.'],
        ];
    }

    public static function locations(): array
    {
        return [
            'hotel' => 'Hotel / habitacion',
            'playa' => 'Playa / piscina',
            'restaurante' => 'Restaurante / bar',
            'caminata' => 'Caminando',
            'transporte' => 'Transporte',
            'noche' => 'Noche privada',
            'descanso' => 'Descanso',
        ];
    }

    public static function moods(): array
    {
        return [
            'tiernos' => 'Tiernos',
            'juguetones' => 'Juguetones',
            'coquetos' => 'Coquetos',
            'intensos' => 'Intensos',
            'cansados' => 'Cansados pero conectados',
        ];
    }

    public static function intentions(): array
    {
        return [
            'conversar' => 'Conversar mejor',
            'reir' => 'Jugar y reir',
            'seducir' => 'Subir deseo',
            'calmar' => 'Bajar ritmo',
            'cerrar' => 'Cerrar el dia',
        ];
    }

    public static function gameModes(): array
    {
        return [
            [
                'key' => 'roulette',
                'title' => 'Ruleta de momento',
                'description' => 'Elige al azar lugar, intencion y nivel. Si sale algo que no encaja, bajen un nivel o pasen.',
            ],
            [
                'key' => 'cards',
                'title' => 'Cartas por turnos',
                'description' => 'Cada uno toma una carta y responde o ejecuta. El otro puede pedir mas detalle o proponer una version suave.',
            ],
            [
                'key' => 'dice',
                'title' => 'Dados de accion',
                'description' => 'Un dado define intensidad y otro define accion: hablar, mirar, tocar, guiar, pausar o cerrar.',
            ],
            [
                'key' => 'timer',
                'title' => 'Timer de conexion',
                'description' => 'Pongan 3, 5 o 10 minutos. Durante ese tiempo solo siguen la actividad elegida y luego conversan.',
            ],
            [
                'key' => 'mission',
                'title' => 'Mision del dia',
                'description' => 'Una tarea discreta para sostener la conexion durante el viaje: una frase, una mirada o una promesa.',
            ],
        ];
    }

    public static function intimacyConcepts(): array
    {
        return [
            [
                'title' => 'Presencia antes que tecnica',
                'level' => 'Nivel 2-3',
                'context' => 'Cualquier lugar',
                'description' => 'La idea central es bajar el ritmo, observar al otro y convertir la atencion en deseo. Sirve antes de cualquier juego o conversacion profunda.',
                'practice' => 'Tomen 90 segundos para respirar juntos, mirarse y decir que quieren sentir hoy.',
            ],
            [
                'title' => 'Ritual de inicio',
                'level' => 'Nivel 3-4',
                'context' => 'Privado o discreto',
                'description' => 'Inspirado en la dimension ritual del Kamasutra: preparar el ambiente, elegir palabras, cuidar el cuerpo y acordar limites antes de subir intensidad.',
                'practice' => 'Definan una frase de inicio, una palabra para pausar y una forma de cerrar con cuidado.',
            ],
            [
                'title' => 'Juego de ritmos',
                'level' => 'Nivel 3-5',
                'context' => 'Privado',
                'description' => 'Explorar ritmo lento, pausa, cercania y cambio de intensidad sin perseguir rendimiento. El objetivo es comunicacion y placer compartido.',
                'practice' => 'Alternen tres ritmos: lento, jugueton y quieto. Despues digan cual conecto mas.',
            ],
            [
                'title' => 'Consentimiento activo',
                'level' => 'Todos',
                'context' => 'Siempre',
                'description' => 'Cada propuesta debe poder responderse con si, tal vez, no, pausa o mas suave. Esto vuelve los niveles altos mas seguros y mas disfrutables.',
                'practice' => 'Antes de una actividad intensa, cada uno completa: hoy si quiero, hoy tal vez, hoy no quiero.',
            ],
        ];
    }

    public static function positionSuggestions(): array
    {
        return [
            [
                'title' => 'Frente a frente sentado',
                'level' => 3,
                'privacy' => 'privado',
                'focus' => 'Mirada, respiracion y conversacion cercana.',
                'guide' => 'Sientense frente a frente y usen las manos solo para comunicar cercania. Ideal para empezar suave antes de decidir si suben intensidad.',
                'consent_cue' => 'Pregunta: quieres que sigamos asi, mas cerca o mas lento?',
            ],
            [
                'title' => 'Abrazo lateral',
                'level' => 3,
                'privacy' => 'privado',
                'focus' => 'Cuidado, descanso y ternura fisica.',
                'guide' => 'Recostados de lado, mantengan contacto comodo y hablen bajo. Funciona cuando estan cansados pero quieren sentirse conectados.',
                'consent_cue' => 'Pregunta: esto se siente cuidado o prefieres espacio?',
            ],
            [
                'title' => 'Guia por turnos',
                'level' => 4,
                'privacy' => 'privado',
                'focus' => 'Comunicar deseo sin prisa.',
                'guide' => 'Una persona guia durante pocos minutos y la otra solo responde con mas, menos, pausa o cambia. Luego intercambian.',
                'consent_cue' => 'Regla: cualquier pausa se respeta sin explicacion inmediata.',
            ],
            [
                'title' => 'Ritual de cercania intensa',
                'level' => 5,
                'privacy' => 'privado',
                'focus' => 'Deseo, limites y cierre consciente.',
                'guide' => 'Preparen el espacio, acuerden lo que si/no/tal vez, elijan ritmo y definan como quieren cerrar. No se recomienda iniciar sin hablar primero.',
                'consent_cue' => 'Pregunta: que necesitas para sentirte libre, deseado y seguro?',
            ],
        ];
    }

    public static function levelSixRituals(): array
    {
        return [
            self::activity('l6-consent-contract', 4, 6, 'hotel', 'ritual', 'Contrato de deseo de esta noche', 'Cada uno define tres si claros, tres no claros y tres tal vez. Despues elijan una palabra de inicio, una palabra de pausa y una senal para bajar intensidad sin explicar de inmediato.', 'privado', 'Consentimiento activo'),
            self::activity('l6-private-fantasy-map', 4, 6, 'noche', 'fantasia', 'Mapa privado de fantasia', 'Construyan una fantasia compartida en palabras: ambiente, energia, roles emocionales, frases permitidas, limites y forma de cierre. Solo entra al juego lo que ambos acepten claramente.', 'privado', 'Conversacion de deseo'),
            self::activity('l6-sensual-script', 4, 6, 'hotel', 'cartas', 'Guion sensual por turnos', 'Una persona propone el inicio con palabras, mirada y ritmo; la otra ajusta con mas, menos, pausa o cambia. Intercambien liderazgo cada pocos minutos.', 'privado', 'Ritual inspirado en Kamasutra'),
            self::activity('l6-intensity-ladder', 4, 6, 'noche', 'timer', 'Escalera de intensidad', 'Suban en cuatro pasos: presencia, deseo verbal, cercania privada y decision compartida. En cada paso pregunten si siguen, bajan o pausan.', 'privado', 'Sensate focus'),
            self::activity('l6-command-cards', 4, 6, 'hotel', 'cartas', 'Cartas de direccion consensuada', 'Por turnos, una persona da una indicacion sensual no grafica sobre ritmo, distancia, palabras o energia. La otra puede aceptar, editar o pasar.', 'privado', 'Juego de direccion segura'),
            self::activity('l6-confession-room', 4, 6, 'noche', 'conversacion', 'Habitacion de confesiones', 'Completen frases directas: hoy quiero sentir..., me provoca cuando..., deseo que me hables con energia..., mi limite de hoy es...', 'privado', 'Deseo declarado'),
            self::activity('l6-private-scene', 4, 6, 'hotel', 'fantasia', 'Escena privada editable', 'Disenen una escena en cinco acuerdos: quien inicia, que energia tendra, que palabras si, que queda fuera y como sabran que ambos siguen presentes.', 'privado', 'Fantasia consensuada'),
            self::activity('l6-aftercare-close', 4, 6, 'descanso', 'cierre', 'Cierre de cuidado', 'Despues de cualquier momento intenso, cierren con agua, abrazo, palabras de cuidado y una pregunta honesta sobre como se sintio.', 'privado', 'Aftercare'),
        ];
    }

    public static function levelSixGuides(): array
    {
        return [
            [
                'title' => 'Inicio directo',
                'instruction' => 'Apaguen distracciones, acuerden privacidad y digan una frase clara de deseo sin pedir rendimiento. Ejemplo: quiero una noche lenta, intensa y cuidada contigo.',
                'check' => 'Ambos responden: si quiero, mas suave o pausa.',
            ],
            [
                'title' => 'Direccion por turnos',
                'instruction' => 'Durante tres minutos una persona dirige solo con palabras sobre energia, ritmo, cercania y tono. La otra responde con mas, menos, sigue, cambia o pausa.',
                'check' => 'Cambien de turno solo cuando ambos esten comodos.',
            ],
            [
                'title' => 'Fantasia con bordes',
                'instruction' => 'Describan una escena privada usando ambiente, actitud, palabras y limite. Mantengan la fantasia editable: nada queda aprobado hasta que ambos lo confirmen.',
                'check' => 'Cada idea debe tener respuesta: si, tal vez con cambios, no o despues.',
            ],
            [
                'title' => 'Escalera intensa',
                'instruction' => 'Suban de presencia a deseo declarado, luego a juego privado y finalmente a una decision compartida sobre si continuar o cerrar. No salten pasos.',
                'check' => 'Antes de subir: seguimos, bajamos o paramos?',
            ],
            [
                'title' => 'Cierre obligatorio',
                'instruction' => 'Al terminar, vuelvan a hablarse con cuidado. Digan que les gusto, que ajustarian y que necesitan ahora: abrazo, silencio, agua o palabras.',
                'check' => 'No se evalua desempeno; se cuida la conexion.',
            ],
        ];
    }

    public static function levelSixRouletteOptions(): array
    {
        $path = resource_path('data/level-six-roulette.json');

        if (is_file($path)) {
            $data = json_decode((string) file_get_contents($path), true);

            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'nivel_2' => [
                'descripcion' => 'Intimo y jugueton',
                'exclusivas_hombre' => [
                    'Camilo propone una actividad privada y consensuada.',
                ],
                'exclusivas_mujer' => [
                    'Isabella propone una actividad privada y consensuada.',
                ],
                'compartidas' => [
                    'Ambos acuerdan una actividad privada, cuidada y con opcion de pausa.',
                ],
            ],
            'nivel_3' => [
                'descripcion' => 'Mas sensual',
                'exclusivas_hombre' => [],
                'exclusivas_mujer' => [],
                'compartidas' => [],
            ],
            'nivel_4' => [
                'descripcion' => 'Mas directo',
                'exclusivas_hombre' => [],
                'exclusivas_mujer' => [],
                'compartidas' => [],
            ],
            'nivel_5' => [
                'descripcion' => 'Sin limite automatico de avance',
                'exclusivas_hombre' => [],
                'exclusivas_mujer' => [],
                'compartidas' => [],
            ],
        ];
    }

    public static function groupedByDay(): array
    {
        $days = self::days();

        foreach ($days as $day => $meta) {
            $days[$day]['activities'] = [];
        }

        foreach (self::activities() as $activity) {
            $days[$activity['day']]['activities'][] = $activity;
        }

        return array_values($days);
    }

    public static function findActivity(string $key): ?array
    {
        foreach (array_merge(self::activities(), self::levelSixRituals()) as $activity) {
            if ($activity['key'] === $key) {
                return $activity;
            }
        }

        return null;
    }

    public static function activities(): array
    {
        return [
            self::activity('d1-hotel-l2-map', 1, 2, 'hotel', 'conversacion', 'Mapa de gratitud', 'Cada uno comparte tres momentos de la relacion que todavia le calientan el corazon y explica por que.', 'privado', 'Mapas del amor'),
            self::activity('d1-caminata-l2-question', 1, 2, 'caminata', 'conversacion', 'Pregunta caminando', 'Mientras caminan, respondan: que parte de mi te hizo sentir elegido este ultimo año?', 'publico', 'Preguntas de conexion'),
            self::activity('d1-restaurante-l2-toast', 1, 2, 'restaurante', 'ritual', 'Brindis secreto', 'Cada uno hace un brindis bajo: una gratitud, una promesa y una expectativa del viaje.', 'publico', 'Ritual de pareja'),
            self::activity('d1-transporte-l2-note', 1, 2, 'transporte', 'mision', 'Nota de llegada', 'Escriban una frase corta para abrir el viaje y leansela antes de llegar al siguiente lugar.', 'publico', 'Inicio emocional'),
            self::activity('d1-hotel-l3-look', 1, 3, 'hotel', 'timer', 'Mirada sin escape', 'Durante dos minutos mirense sin celular. Despues digan una cosa emocional y una fisica que desean del otro este viaje.', 'privado', 'Presencia corporal'),
            self::activity('d1-playa-l3-signal', 1, 3, 'playa', 'mision', 'Señal de deseo', 'Inventen una señal discreta para decir "me gustas ahora" sin palabras y usenla tres veces durante el dia.', 'publico', 'Coqueteo discreto'),
            self::activity('d1-restaurante-l3-compliments', 1, 3, 'restaurante', 'cartas', 'Tres cumplidos', 'Por turnos, cada uno da un cumplido emocional, uno fisico y uno sobre algo que admira.', 'publico', 'Apreciacion erotica'),
            self::activity('d1-noche-l3-kiss', 1, 3, 'noche', 'timer', 'Beso lento', 'Elijan un beso de 90 segundos con pausa, respiracion y una frase honesta al final.', 'privado', 'Atencion gradual'),
            self::activity('d1-hotel-l4-desire-list', 1, 4, 'hotel', 'fantasia', 'Lista de deseo permitido', 'Cada uno escribe tres deseos intimos para explorar estos dias. El otro puede aceptar, ajustar o pasar cualquiera.', 'privado', 'Conversacion de deseo'),
            self::activity('d1-noche-l4-menu', 1, 4, 'noche', 'cartas', 'Si, tal vez, no', 'Hagan tres columnas para caricias, palabras y ritmos. Cada punto necesita consentimiento de ambos.', 'privado', 'Limites explicitos'),
            self::activity('d1-descanso-l4-sensate', 1, 4, 'descanso', 'timer', 'Atencion corporal', 'Durante cinco minutos una persona guia contacto no apresurado y la otra solo comunica mas, menos o pausa.', 'privado', 'Sensate focus'),
            self::activity('d1-hotel-l5-private-code', 1, 5, 'hotel', 'ritual', 'Palabras clave', 'Inventen una palabra para pedir intimidad sexual privada y otra para pausar de inmediato. Repitan los limites antes de iniciar.', 'privado', 'Consentimiento activo'),

            self::activity('d2-playa-l2-present', 2, 2, 'playa', 'timer', 'Cinco sentidos', 'Describan cinco cosas que ven, oyen o sienten y conecten cada una con un recuerdo de ustedes.', 'publico', 'Presencia'),
            self::activity('d2-descanso-l2-care', 2, 2, 'descanso', 'conversacion', 'Como cuidarte hoy', 'Respondan: que necesitas de mi hoy para sentirte amado y tranquilo?', 'privado', 'Cuidado emocional'),
            self::activity('d2-restaurante-l2-choice', 2, 2, 'restaurante', 'cartas', 'Elijo por ti', 'Cada uno elige algo pequeño para el otro: bebida, postre, foto o plan siguiente, explicando por que.', 'publico', 'Atencion cotidiana'),
            self::activity('d2-caminata-l2-future', 2, 2, 'caminata', 'conversacion', 'Un año mas', 'Cada uno dice una cosa que quiere repetir y una que quiere mejorar antes del proximo aniversario.', 'publico', 'Vision compartida'),
            self::activity('d2-playa-l3-flirt', 2, 3, 'playa', 'mision', 'Coqueteo de dia', 'Durante una hora, cada uno debe iniciar tres gestos discretos de coqueteo sin explicar demasiado.', 'publico', 'Juego erotico discreto'),
            self::activity('d2-transporte-l3-whisper', 2, 3, 'transporte', 'cartas', 'Frase al oido', 'Cada uno dice al oido una frase romantica y una picante, manteniendola apropiada para el lugar.', 'publico', 'Lenguaje de deseo'),
            self::activity('d2-hotel-l3-hands', 2, 3, 'hotel', 'timer', 'Manos atentas', 'Durante tres minutos exploren solo manos, brazos y cuello, preguntando que se siente bien.', 'privado', 'Atencion corporal'),
            self::activity('d2-noche-l3-memory-kiss', 2, 3, 'noche', 'ritual', 'Beso con historia', 'Recuerden un beso favorito y repitan su energia, no la escena exacta.', 'privado', 'Memoria erotica'),
            self::activity('d2-hotel-l4-fantasy', 2, 4, 'hotel', 'fantasia', 'Fantasia editable', 'Cada uno comparte una fantasia sexual y el otro responde con si, tal vez, no y condiciones.', 'privado', 'Esther Perel'),
            self::activity('d2-noche-l4-control', 2, 4, 'noche', 'dados', 'Guia consentida', 'Un dado define quien guia y otro define ritmo: lento, jugueton, intenso, pausa, hablar o cambiar.', 'privado', 'Juego de control seguro'),
            self::activity('d2-descanso-l4-aftercare', 2, 4, 'descanso', 'ritual', 'Cuidado despues', 'Antes de cualquier momento intenso, acuerden como quieren cuidarse despues: agua, abrazo, palabras o silencio.', 'privado', 'Aftercare'),
            self::activity('d2-hotel-l5-script', 2, 5, 'hotel', 'fantasia', 'Guion privado', 'Construyan una escena sexual privada en tres actos: inicio, intensidad y cierre. Cada acto necesita aprobacion clara.', 'privado', 'Ritual inspirado en Kamasutra'),

            self::activity('d3-caminata-l2-letter', 3, 2, 'caminata', 'mision', 'Carta de cinco lineas', 'Escriban una carta corta: que prometo cuidar mas en nosotros?', 'publico', 'Compromiso'),
            self::activity('d3-restaurante-l2-awards', 3, 2, 'restaurante', 'cartas', 'Premios del viaje', 'Den premios inventados: mejor mirada, momento mas tierno, frase mas bonita y sorpresa favorita.', 'publico', 'Celebracion'),
            self::activity('d3-playa-l2-silence', 3, 2, 'playa', 'timer', 'Silencio acompañado', 'Tres minutos sin hablar, tomados de la mano. Luego cada uno dice que sintio.', 'publico', 'Regulacion emocional'),
            self::activity('d3-descanso-l2-repair', 3, 2, 'descanso', 'conversacion', 'Chequeo honesto', 'Respondan: hay algo que necesite reparar, agradecer o pedir antes de seguir subiendo intensidad?', 'privado', 'Reparacion'),
            self::activity('d3-playa-l3-photo', 3, 3, 'playa', 'mision', 'Foto con intencion', 'Tomen una foto que represente deseo, ternura o complicidad. Expliquen por que.', 'publico', 'Juego simbolico'),
            self::activity('d3-restaurante-l3-secret', 3, 3, 'restaurante', 'cartas', 'Secreto bonito', 'Cada uno confiesa algo que le atrae del otro y casi nunca dice.', 'publico', 'Vulnerabilidad'),
            self::activity('d3-hotel-l3-massage', 3, 3, 'hotel', 'timer', 'Masaje sin prisa', 'Cinco minutos por turno de masaje no sexualizado, terminando con una pregunta: que quieres mas?', 'privado', 'Sensate focus'),
            self::activity('d3-noche-l3-roles-soft', 3, 3, 'noche', 'ruleta', 'Roles suaves', 'Elijan rol de quien invita, quien sorprende o quien cuida. Mantengan el juego ligero y consensuado.', 'privado', 'Juego de roles suave'),
            self::activity('d3-hotel-l4-pleasure-menu', 3, 4, 'hotel', 'cartas', 'Menu de placer', 'Cada uno escribe tres caricias, tres palabras y tres limites. Usenlo para guiar un momento privado.', 'privado', 'Menu erotico'),
            self::activity('d3-noche-l4-yes-more-stop', 3, 4, 'noche', 'timer', 'Mas, menos, pausa', 'Practiquen pedir mas, menos o pausa antes de subir intensidad. El objetivo es confianza, no rendimiento.', 'privado', 'Consentimiento activo'),
            self::activity('d3-descanso-l4-desire-talk', 3, 4, 'descanso', 'conversacion', 'Deseo sin presion', 'Hablen de lo que mas les enciende emocionalmente, fisicamente y mentalmente, sin obligarse a hacerlo hoy.', 'privado', 'Conversacion de deseo'),
            self::activity('d3-hotel-l5-scene', 3, 5, 'hotel', 'fantasia', 'Escena privada', 'Diseñen juntos una escena sexual: lugar, ritmo, quien inicia, que se permite, que queda fuera y como cerrar.', 'privado', 'Kamasutra como ritual'),

            self::activity('d4-descanso-l2-gratitude', 4, 2, 'descanso', 'ritual', 'Gracias por este viaje', 'Cada uno dice cinco gracias concretos del viaje, sin repetir palabras genericas.', 'privado', 'Cierre emocional'),
            self::activity('d4-transporte-l2-memory', 4, 2, 'transporte', 'cartas', 'Capsula de memoria', 'Escriban tres recuerdos del viaje que quieren guardar y uno que quieren repetir pronto.', 'publico', 'Memoria compartida'),
            self::activity('d4-restaurante-l2-promise', 4, 2, 'restaurante', 'conversacion', 'Promesa realista', 'Cada uno hace una promesa pequeña y medible para la relacion.', 'publico', 'Compromiso'),
            self::activity('d4-caminata-l2-goodbye', 4, 2, 'caminata', 'timer', 'Cierre caminando', 'Caminen cinco minutos y digan que se llevan del otro, del viaje y de ustedes.', 'publico', 'Integracion'),
            self::activity('d4-playa-l3-final-signal', 4, 3, 'playa', 'mision', 'Ultima señal', 'Usen la señal de deseo que inventaron y cierrenla con una frase de cariño.', 'publico', 'Complicidad'),
            self::activity('d4-restaurante-l3-awards-hot', 4, 3, 'restaurante', 'cartas', 'Premios picantes', 'Den premios discretos: mejor beso, mejor mirada, momento mas provocador, momento mas tierno.', 'publico', 'Celebracion sensual'),
            self::activity('d4-hotel-l3-replay', 4, 3, 'hotel', 'ruleta', 'Replay favorito', 'Elijan una actividad nivel 2 o 3 favorita y repitanla con una mejora.', 'privado', 'Repeticion consciente'),
            self::activity('d4-noche-l3-slow', 4, 3, 'noche', 'timer', 'Lento a proposito', 'Todo lo que hagan durante diez minutos debe ser lento. Despues digan que cambio.', 'privado', 'Atencion erotica'),
            self::activity('d4-hotel-l4-pact', 4, 4, 'hotel', 'ritual', 'Pacto de deseo', 'Definan una costumbre sensual que quieran llevarse del viaje a la vida diaria.', 'privado', 'Deseo sostenido'),
            self::activity('d4-noche-l4-choice', 4, 4, 'noche', 'dados', 'Elegimos los dos', 'Un dado propone intensidad y el otro tipo de accion. Solo avanza si ambos dicen si claro.', 'privado', 'Decision conjunta'),
            self::activity('d4-descanso-l4-after', 4, 4, 'descanso', 'conversacion', 'Lo que aprendi de ti', 'Cada uno dice algo que aprendio del deseo, limites o ternura del otro durante el viaje.', 'privado', 'Integracion erotica'),
            self::activity('d4-hotel-l5-finale', 4, 5, 'hotel', 'ritual', 'Final elegido por ambos', 'Planeen un cierre sexual privado donde ambos digan que desean, que no desean y como quieren terminar la noche.', 'privado', 'Cierre consentido'),
        ];
    }

    private static function activity(string $key, int $day, int $level, string $location, string $type, string $title, string $prompt, string $privacy, string $inspiration): array
    {
        return [
            'key' => $key,
            'day' => $day,
            'level' => $level,
            'location' => $location,
            'type' => $type,
            'title' => $title,
            'prompt' => $prompt,
            'privacy' => $privacy,
            'inspiration' => $inspiration,
        ];
    }
}
