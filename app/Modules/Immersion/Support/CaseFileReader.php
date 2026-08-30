<?php

namespace App\Modules\Immersion\Support;

use Illuminate\Support\Str;

/**
 * Lee el contenido narrativo del caso (sobres, testimonios, victima) desde
 * app/Modules/Immersion/data/. Ese contenido no se genera ni se modifica aqui:
 * este helper solo lo carga y lo renderiza tal cual, para que el correo y la
 * bandeja del jugador muestren exactamente el mismo texto verbatim.
 */
class CaseFileReader
{
    private static array $cache = [];

    public static function dataPath(string $relativePath = ''): string
    {
        $base = dirname(__DIR__).'/data';

        return $relativePath === '' ? $base : $base.'/'.ltrim($relativePath, '/');
    }

    public static function exists(string $relativePath): bool
    {
        return is_file(self::dataPath($relativePath));
    }

    public static function raw(string $relativePath): string
    {
        if (isset(self::$cache[$relativePath])) {
            return self::$cache[$relativePath];
        }

        $path = self::dataPath($relativePath);

        if (! is_file($path)) {
            return self::$cache[$relativePath] = '';
        }

        return self::$cache[$relativePath] = (string) file_get_contents($path);
    }

    public static function renderMarkdown(string $markdown): string
    {
        return Str::markdown($markdown, ['html_input' => 'strip']);
    }

    public static function renderFile(string $relativePath): string
    {
        return self::renderMarkdown(self::raw($relativePath));
    }

    /**
     * Igual que renderFile(), pero omite las secciones "## Titulo" cuyo
     * titulo contenga alguno de los textos en $excludeHeadingContains. No
     * modifica el archivo original: solo deja de mostrar, en esta salida
     * puntual, las secciones que ya se ven como imagen en la galeria (ver
     * CaseGallery), para no repetir el mismo contenido en texto y en foto.
     *
     * @param  string[]  $excludeHeadingContains
     */
    public static function renderFileExcludingSections(string $relativePath, array $excludeHeadingContains): string
    {
        if (empty($excludeHeadingContains)) {
            return self::renderFile($relativePath);
        }

        $blocks = preg_split('/^---$/m', self::raw($relativePath));

        $kept = array_filter($blocks, function (string $block) use ($excludeHeadingContains) {
            if (! preg_match('/^##\s+(.+)$/m', $block, $matches)) {
                return true;
            }

            $heading = trim($matches[1]);

            foreach ($excludeHeadingContains as $needle) {
                if (str_contains($heading, $needle)) {
                    return false;
                }
            }

            return true;
        });

        return self::renderMarkdown(trim(implode("\n\n---\n\n", $kept)));
    }
}
