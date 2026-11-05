<?php

namespace App\Modules\Platform\Support;

/**
 * Curated discovery labels for the catalog.
 *
 * Case manifests remain the source for commercial labels such as "Gratis".
 * This map adds a small, consistent set of story and setting labels so every
 * installed case can be explored in the same way. Player recommendations are
 * facts, not discovery labels, and are deliberately removed here.
 */
class CaseTagTaxonomy
{
    /** @var array<string, array<int, string>> */
    private const THEMES = [
        'archivo-nueve' => ['Archivo perdido', 'Misterio tecnológico', 'Investigación digital'],
        'bajo-la-cabeza-del-dragon' => ['Misterio comunitario', 'Festival', 'Caso corto'],
        'bajo-la-linea-de-flotacion' => ['Misterio marítimo', 'Investigación científica', 'Espacio cerrado'],
        'camara-6-no-existe' => ['Vigilancia', 'Misterio urbano', 'Espacio cerrado'],
        'confeti-plateado' => ['Fiesta', 'Misterio social', 'Identidades ocultas'],
        'desaparecida-en-directo' => ['Medios de comunicación', 'Investigación digital', 'Desaparición'],
        'el-ano-que-no-cerro' => ['Misterio corporativo', 'Documentos', 'Secretos del pasado'],
        'el-bingo-del-jueves' => ['Misterio comunitario', 'Fraude cotidiano', 'Caso corto'],
        'el-brindis-22-14' => ['Boda', 'Misterio social', 'Cronología'],
        'el-candidato-perfecto' => ['Política', 'Misterio corporativo', 'Investigación documental'],
        'el-color-que-falta' => ['Arte', 'Misterio cultural', 'Objeto desaparecido'],
        'el-espejo-de-las-tres-caras' => ['Misterio psicológico', 'Espacio cerrado', 'Secretos familiares'],
        'el-huesped-que-nunca-llego' => ['Hotel', 'Desaparición', 'Misterio clásico'],
        'el-libro-de-los-sustos' => ['Librería', 'Misterio ligero', 'Secretos del pasado'],
        'el-puerto-de-los-calendarios' => ['Misterio marítimo', 'Cronología', 'Archivo perdido'],
        'el-reparto-invisible' => ['Teatro', 'Identidades ocultas', 'Misterio social'],
        'el-ultimo-lote' => ['Investigación científica', 'Misterio corporativo', 'Fraude'],
        'entre-sueno-y-vigilia' => ['Misterio psicológico', 'Sueños', 'Espacio cerrado'],
        'habitacion-314' => ['Hotel', 'Espacio cerrado', 'Misterio clásico'],
        'kilometro-186' => ['Tren nocturno', 'Desaparición', 'Misterio clásico'],
        'la-anada-perdida' => ['Bodega', 'Secretos familiares', 'Investigación documental'],
        'la-boda-de-los-ausentes' => ['Boda', 'Desaparición', 'Misterio social'],
        'la-boya-naranja' => ['Deporte', 'Misterio al aire libre', 'Caso corto'],
        'la-caja-de-donaciones' => ['Misterio comunitario', 'Fraude cotidiano', 'Caso corto'],
        'la-casa-de-cristal' => ['Misterio doméstico', 'Espacio cerrado', 'Secretos familiares'],
        'la-cera-de-los-invitados' => ['Misterio gótico', 'Ritual'],
        'la-corona-de-sal' => ['Misterio costero', 'Artesanía', 'Investigación documental'],
        'la-feria-de-las-profecias' => ['Feria nocturna', 'Misterio ligero'],
        'la-final-sin-tablero' => ['Juego de mesa', 'Fraude cotidiano', 'Caso corto'],
        'la-palabra-de-seguridad' => ['Misterio tecnológico', 'Espacio cerrado'],
        'la-primera-firma' => ['Misterio corporativo', 'Documentos', 'Fraude'],
        'la-ultima-funcion' => ['Teatro', 'Misterio cultural', 'Secretos del pasado'],
        'la-ultima-postal' => ['Viaje', 'Archivo perdido', 'Desaparición'],
        'la-voz-de-la-montana' => ['Misterio al aire libre', 'Montaña', 'Investigación científica'],
        'los-veinticuatro-nombres' => ['Archivo perdido', 'Misterio histórico', 'Investigación documental'],
        'modo-demostracion' => ['Misterio tecnológico', 'Misterio corporativo', 'Caso corto'],
        'nadie-salio-del-edificio' => ['Espacio cerrado', 'Misterio urbano', 'Cronología'],
        'proyecto-boreal' => ['Investigación científica', 'Misterio tecnológico', 'Espacio cerrado'],
        'punto-de-control' => ['Deporte', 'Misterio al aire libre', 'Cronología'],
        'steve-jacobs' => ['Misterio tecnológico', 'Misterio corporativo', 'Investigación documental'],
        'tres-minutos-de-silencio' => ['Misterio urbano', 'Cronología', 'Espacio cerrado'],
    ];

    /**
     * @param array<int, mixed> $declared
     * @return array<int, string>
     */
    public static function for(string $slug, array $declared = []): array
    {
        $tags = array_merge($declared, self::THEMES[$slug] ?? []);

        return array_values(array_unique(array_filter($tags, fn (mixed $tag): bool => is_string($tag)
            && trim($tag) !== ''
            && ! preg_match('/^ideal\s*:/iu', trim($tag)))));
    }
}
