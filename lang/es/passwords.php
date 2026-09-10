<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | Lo que el corredor de contraseñas devuelve tras un intento de cambio.
    | `sent` no se usa en la práctica: PasswordResetLinkController responde
    | siempre con su propio mensaje, el mismo exista o no la cuenta, para que
    | el formulario no sirva para averiguar qué correos están registrados.
    |
    */

    'reset' => 'Tu contraseña quedó cambiada. Ya puedes ingresar con ella.',
    'sent' => 'Te enviamos un enlace para restablecer tu contraseña.',
    'throttled' => 'Espera un momento antes de volver a intentarlo.',
    'token' => 'Este enlace ya caducó o no es válido. Pide uno nuevo.',
    'user' => 'No encontramos ninguna cuenta con esa dirección.',

];
