<?php

namespace App\Modules\Immersion\Support;

/**
 * Imagenes del PDF original del caso (evidencia, recortes de prensa,
 * capturas de redes sociales/recibos) que se muestran junto al texto
 * verbatim de cada Sobre. Las imagenes en si viven en public/immersion/gallery
 * (recortadas/optimizadas del PDF); aqui solo se mapea que archivo de imagen
 * corresponde a que Sobre y con que titulo corto.
 */
class CaseGallery
{
    public static function forSourceFile(string $sourceFile): array
    {
        return match ($sourceFile) {
            'sobres/sobre-1.md' => [
                ['file' => 'sobre1-post-policia.jpg', 'caption' => 'Publicación del Departamento de Policía de San Francisco'],
                ['file' => 'sobre1-prensa-suplementos.jpg', 'caption' => 'San Francisco Daily — suplementos adulterados'],
                ['file' => 'sobre1-titulares-articulo.jpg', 'caption' => 'Titulares de prensa y artículo del video del elevador'],
                ['file' => 'sobre1-recortes-prensa.jpg', 'caption' => 'Recortes de prensa'],
                ['file' => 'sobre1-comentarios.jpg', 'caption' => 'Comentarios en redes sociales'],
                ['file' => 'sobre1-autopsia.jpg', 'caption' => 'Reporte de autopsia — Oficina de Medicina Forense'],
                ['file' => 'sobre1-laboratorio.jpg', 'caption' => 'Laboratorio de Ciencias Forenses de San Francisco'],
                ['file' => 'evidencia-1-suplementos.jpg', 'caption' => 'Evidencia 1 — Suplementos manipulados'],
                ['file' => 'evidencia-2-huellas.jpg', 'caption' => 'Evidencia 2 — Huellas dactilares'],
                ['file' => 'evidencia-3-torre-blanca.jpg', 'caption' => 'Evidencia 3 — Pieza de ajedrez (torre blanca)'],
                ['file' => 'evidencia-4-collar.jpg', 'caption' => 'Evidencia 4 — Collar de hombre'],
            ],
            'sobres/sobre-2.md' => [
                ['file' => 'sobre2-prensa-helixcare.jpg', 'caption' => 'San Francisco Daily — demanda contra HelixCare'],
            ],
            'sobres/sobre-3.md' => [
                ['file' => 'sobre3-tweets.jpg', 'caption' => 'Publicaciones en X (Twitter)'],
                ['file' => 'sobre3-youtube-torreblanca.jpg', 'caption' => 'Canal de YouTube "Torre Blanca"'],
                ['file' => 'sobre3-recibo-chessmith.jpg', 'caption' => 'Recibo de pedido de ajedrez — Chessmith'],
                ['file' => 'sobre3-recibos-varios.jpg', 'caption' => 'Recibo de bar, publicación de Facebook, Uber y taxi'],
                ['file' => 'sobre3-registro-rada.jpg', 'caption' => 'Registro de entrada y salida — Estudio de Ensayo de RADA'],
                ['file' => 'sobre3-correo-cctv.jpg', 'caption' => 'Correo — Material solicitado de CCTV'],
            ],
            default => [],
        };
    }

    /**
     * Titulos de sección ("## ...") que ya se muestran como imagen y por lo
     * tanto se omiten del texto renderizado, para no repetir lo mismo en
     * texto y en foto. El archivo .md original no se toca: esto solo afecta
     * la salida que ve el jugador (correo y bandeja).
     *
     * @return string[]
     */
    public static function excludedHeadings(string $sourceFile): array
    {
        return match ($sourceFile) {
            'sobres/sobre-1.md' => [
                'Reporte de Investigación',
                'Laboratorio de Ciencias Forenses',
                'Etiquetas de evidencia',
                'Recorte de prensa — San Francisco Daily (02 Febrero',
                'Titulares de prensa',
                'Artículo — Steve Jacobs, captado en video',
                'Publicación en redes sociales — Departamento de Policía',
                'Comentarios en redes sociales',
            ],
            'sobres/sobre-2.md' => [
                'Recorte de prensa — San Francisco Daily (09 Septiembre',
            ],
            'sobres/sobre-3.md' => [
                'Publicaciones en X (Twitter)',
                'Canal de YouTube',
                'Recibo de pedido de ajedrez',
                'Recibo de compra — Bar Prince Albert',
                'Publicación en Facebook',
                'Captura de app de transporte',
                'Recibo — San Francisco Taxi VIP',
                'Registro de Entrada y Salida',
                'Correo electrónico — Material solicitado de CCTV',
            ],
            default => [],
        };
    }
}
