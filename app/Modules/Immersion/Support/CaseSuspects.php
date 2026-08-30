<?php

namespace App\Modules\Immersion\Support;

/**
 * Registro fijo de personas interrogables (Mecanica 7). No es contenido
 * narrativo nuevo: el testimonio completo sigue viviendo unicamente en
 * data/suspects/*.md (unica fuente de verdad para el interrogatorio);
 * aqui solo se repiten los campos cortos de la "Ficha" de cada persona
 * (conexion, motivo, coartada) tal como ya aparecen en esos archivos, mas
 * la foto recortada del PDF original del caso, para mostrar un tablero de
 * sospechosos mas visual en vez de solo texto.
 */
class CaseSuspects
{
    public static function all(): array
    {
        return [
            'elizabeth-foster' => [
                'name' => 'Elizabeth Foster',
                'role' => 'sospechosa',
                'file' => 'suspects/elizabeth-foster.md',
                'photo' => 'elizabeth-foster.jpg',
                'connection' => 'Esposa',
                'motive' => 'Sospecha engaño',
                'alibi' => 'Estaba en su coche',
            ],
            'sofia-reyes' => [
                'name' => 'Sofía Reyes',
                'role' => 'sospechosa',
                'file' => 'suspects/sofia-reyes.md',
                'photo' => 'sofia-reyes.jpg',
                'connection' => 'Relación amorosa',
                'motive' => 'Despecho',
                'alibi' => 'Ticket Uber',
            ],
            'lucas-jacobs' => [
                'name' => 'Lucas Jacobs',
                'role' => 'sospechoso',
                'file' => 'suspects/lucas-jacobs.md',
                'photo' => 'lucas-jacobs.jpg',
                'connection' => 'Hijo',
                'motive' => 'Falta de apoyo en su carrera',
                'alibi' => 'Hoja de registro',
            ],
            'rachel-miller' => [
                'name' => 'Rachel Miller',
                'role' => 'sospechosa',
                'file' => 'suspects/rachel-miller.md',
                'photo' => 'rachel-miller.jpg',
                'connection' => 'Amiga de la esposa',
                'motive' => 'Enojo',
                'alibi' => null,
            ],
            'emily-johnson' => [
                'name' => 'Emily Johnson',
                'role' => 'sospechosa',
                'file' => 'suspects/emily-johnson.md',
                'photo' => 'emily-johnson.jpg',
                'connection' => 'Empleada del Hotel Altamira',
                'motive' => null,
                'alibi' => null,
            ],
            'kevin-huang' => [
                'name' => 'Kevin Huang',
                'role' => 'sospechoso',
                'file' => 'suspects/kevin-huang.md',
                'photo' => 'kevin-huang.jpg',
                'connection' => 'Contador',
                'motive' => 'El padre fue rechazado para un procedimiento cardíaco urgente y murió',
                'alibi' => 'Ticket Uber',
            ],
            'daniel-blake' => [
                'name' => 'Daniel Blake',
                'role' => 'sospechoso',
                'file' => 'suspects/daniel-blake.md',
                'photo' => 'daniel-blake.jpg',
                'connection' => 'Amigo/Socio',
                'motive' => 'Desacuerdos laborales',
                'alibi' => null,
            ],
            'sarah-collins' => [
                'name' => 'Sarah Collins',
                'role' => 'sospechosa',
                'file' => 'suspects/sarah-collins.md',
                'photo' => 'sarah-collins.jpg',
                'connection' => 'Gerente de Hotel Altamira',
                'motive' => null,
                'alibi' => null,
            ],
            'jeremy-burt-testigo' => [
                'name' => 'Jeremy Burt',
                'role' => 'testigo (no es sospechoso oficial)',
                'file' => 'suspects/jeremy-burt-testigo.md',
                'photo' => 'jeremy-burt.jpg',
                'connection' => 'Recepcionista del turno nocturno, Hotel Altamira',
                'motive' => null,
                'alibi' => null,
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        return self::all()[$slug] ?? null;
    }

    public static function exists(string $slug): bool
    {
        return array_key_exists($slug, self::all());
    }

    public static function victim(): array
    {
        return [
            'name' => 'Steve Jacobs',
            'photo' => 'steve-jacobs.jpg',
        ];
    }
}
