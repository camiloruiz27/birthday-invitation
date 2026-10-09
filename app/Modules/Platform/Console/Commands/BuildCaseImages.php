<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Immersion\Cases\CaseRegistry;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Makes the small versions of the big art that the ad landing and link
 * previews use.
 *
 * Every cover is a 1.4-2 MB PNG and the brand art is 1.2-1.8 MB. On a phone
 * inside the TikTok or Instagram in-app browser that is most of what has to
 * download before anyone sees the page, and several share crawlers (WhatsApp,
 * some of Meta's) give up on a preview image that heavy. So each cover gets,
 * next to the original:
 *
 *   landing.jpg   longest side 1100 px, for the ad landing's hero
 *   og.jpg        1200x630, for the link preview
 *
 * og.jpg is the cover CONTAINED on a blurred, darkened copy of itself rather
 * than cropped to the 1.91:1 card: five of the covers are portrait, and
 * centre-cropping a portrait poster to a landscape card cuts the title off.
 *
 * Idempotent: an output newer than its source is left alone unless --force.
 * The results are committed, so a deploy needs no image tooling.
 */
class BuildCaseImages extends Command
{
    protected $signature = 'platform:build-case-images
                            {--brand : Also (re)build the brand derivatives: small logo, hero and the default share image}
                            {--force : Rebuild even when the output is already newer than its source}';

    protected $description = 'Build the web-sized cover and share images used by the ad landing.';

    /** Matches --color-surface in resources/css/app.css. */
    private const BACKGROUND = [8, 15, 20];

    public function handle(CaseRegistry $registry): int
    {
        if (! extension_loaded('gd') || ! function_exists('imagecreatefrompng')) {
            $this->error('La extensión GD de PHP no está disponible.');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $built = 0;

        // From the manifests, not the catalog table: this is a build step
        // that must work before (or without) a database.
        foreach ($registry->all() as $case) {
            $cover = $case->catalog()['cover_path'];

            if (! $cover) {
                continue;
            }

            $source = public_path(ltrim($cover, '/'));

            if (! is_file($source)) {
                $this->warn("{$case->slug}: no encuentro {$cover}, lo salto.");

                continue;
            }

            $dir = dirname($source);

            $built += $this->build($source, "{$dir}/landing.jpg", fn ($img) => $this->fit($img, 1100), 80, $force);
            $built += $this->build($source, "{$dir}/og.jpg", fn ($img) => $this->shareCard($img), 82, $force);

            $this->line("  {$case->slug}");
        }

        if ($this->option('brand')) {
            $built += $this->buildBrand($force);
        }

        $this->info("{$built} imagen(es) generada(s).");

        return self::SUCCESS;
    }

    private function buildBrand(bool $force): int
    {
        $brand = public_path('brand');
        $built = 0;

        // The logo is used at 32-36 px but shipped as 1254 px / 1.2 MB, and
        // it is the favicon too, so every page paid for it.
        foreach ([96, 192] as $size) {
            $built += $this->build("{$brand}/isotipo.png", "{$brand}/isotipo-{$size}.png", fn ($img) => $this->fit($img, $size), null, $force);
        }

        foreach ([1200, 700] as $width) {
            $built += $this->build("{$brand}/hero-01.png", "{$brand}/hero-01-{$width}.jpg", fn ($img) => $this->fit($img, $width), 78, $force);
        }

        $built += $this->build("{$brand}/social-network.png", "{$brand}/social-network.jpg", fn ($img) => $this->cropFill($img, 1200, 630), 82, $force);

        return $built;
    }

    /**
     * @param  callable(\GdImage): \GdImage  $transform
     * @param  int|null  $quality  JPEG quality, or null to write a PNG (keeps transparency)
     */
    private function build(string $source, string $target, callable $transform, ?int $quality, bool $force): int
    {
        if (! is_file($source)) {
            $this->warn('No encuentro '.basename($source).', lo salto.');

            return 0;
        }

        if (! $force && is_file($target) && filemtime($target) >= filemtime($source)) {
            return 0;
        }

        $result = $transform($this->load($source));

        if ($quality !== null) {
            // JPEG cannot hold transparency.
            $result = $this->flatten($result);
        }

        if ($quality === null) {
            imagesavealpha($result, true);
            imagepng($result, $target, 9);
        } else {
            imageinterlace($result, true);
            imagejpeg($result, $target, $quality);
        }

        return 1;
    }

    private function load(string $path): \GdImage
    {
        $image = @imagecreatefrompng($path);

        if ($image === false) {
            throw new RuntimeException("No pude leer {$path} como PNG.");
        }

        return $image;
    }

    /**
     * Scales so the LONGEST side is $max (never up), keeping the aspect
     * ratio and any transparency.
     */
    private function fit(\GdImage $source, int $max): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $max / max($width, $height));

        return $this->resample($source, 0, 0, $width, $height, (int) round($width * $scale), (int) round($height * $scale));
    }

    /**
     * Fills $width x $height with the image, cropping the overflow from the
     * centre. Used where the source already has the target shape.
     */
    private function cropFill(\GdImage $source, int $width, int $height): \GdImage
    {
        $sw = imagesx($source);
        $sh = imagesy($source);
        $scale = max($width / $sw, $height / $sh);
        $cw = (int) round($width / $scale);
        $ch = (int) round($height / $scale);

        return $this->flatten($this->resample(
            $source,
            (int) round(($sw - $cw) / 2),
            (int) round(($sh - $ch) / 2),
            $cw,
            $ch,
            $width,
            $height
        ));
    }

    /**
     * 1200x630: a blurred, darkened copy of the cover fills the card and the
     * whole cover sits on it, uncropped.
     */
    private function shareCard(\GdImage $source): \GdImage
    {
        $width = 1200;
        $height = 630;

        // Blur on a small copy and scale it up: a Gaussian pass on the full
        // 1200x630 canvas is slow, and shrinking alone without a real blur
        // leaves visible blocks.
        $small = $this->cropFill($source, 240, 126);

        for ($pass = 0; $pass < 8; $pass++) {
            imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $card = $this->resample($small, 0, 0, 240, 126, $width, $height);

        imagealphablending($card, true);
        imagefilledrectangle($card, 0, 0, $width, $height, imagecolorallocatealpha($card, 0, 0, 0, 60));

        $scale = min($width / imagesx($source), $height / imagesy($source));
        $w = (int) round(imagesx($source) * $scale);
        $h = (int) round(imagesy($source) * $scale);

        imagecopyresampled(
            $card,
            $this->flatten($source),
            (int) round(($width - $w) / 2),
            (int) round(($height - $h) / 2),
            0,
            0,
            $w,
            $h,
            imagesx($source),
            imagesy($source)
        );

        return $card;
    }

    private function resample(\GdImage $source, int $sx, int $sy, int $sw, int $sh, int $dw, int $dh): \GdImage
    {
        $target = imagecreatetruecolor($dw, $dh);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $source, 0, 0, $sx, $sy, $dw, $dh, $sw, $sh);

        return $target;
    }

    /**
     * JPEG has no transparency: lay the image on the brand background so a
     * transparent edge becomes the page colour instead of black.
     */
    private function flatten(\GdImage $source): \GdImage
    {
        $flat = imagecreatetruecolor(imagesx($source), imagesy($source));
        [$r, $g, $b] = self::BACKGROUND;

        imagefill($flat, 0, 0, imagecolorallocate($flat, $r, $g, $b));
        imagealphablending($flat, true);
        imagecopy($flat, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));

        return $flat;
    }
}
