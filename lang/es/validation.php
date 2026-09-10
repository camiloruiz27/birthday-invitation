<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | El archivo completo, no solo las reglas que la app usa hoy: una
    | traducción parcial no falla de forma visible, se cae al inglés por el
    | fallback_locale y nadie se entera hasta que un usuario ve "The :attribute
    | field is required." — que es exactamente el problema que este archivo
    | vino a cerrar.
    |
    */

    'accepted' => 'Debes aceptar :attribute.',
    'accepted_if' => 'Debes aceptar :attribute cuando :other es :value.',
    'active_url' => ':Attribute no es una URL válida.',
    'after' => ':Attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => ':Attribute debe ser una fecha igual o posterior a :date.',
    'alpha' => ':Attribute solo puede contener letras.',
    'alpha_dash' => ':Attribute solo puede contener letras, números, guiones y guiones bajos.',
    'alpha_num' => ':Attribute solo puede contener letras y números.',
    'array' => ':Attribute debe ser una lista.',
    'ascii' => ':Attribute solo puede contener caracteres alfanuméricos y símbolos de un byte.',
    'before' => ':Attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => ':Attribute debe ser una fecha igual o anterior a :date.',
    'between' => [
        'array' => ':Attribute debe tener entre :min y :max elementos.',
        'file' => ':Attribute debe pesar entre :min y :max kilobytes.',
        'numeric' => ':Attribute debe estar entre :min y :max.',
        'string' => ':Attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => ':Attribute solo puede ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => ':Attribute no es una fecha válida.',
    'date_equals' => ':Attribute debe ser una fecha igual a :date.',
    'date_format' => ':Attribute no corresponde al formato :format.',
    'decimal' => ':Attribute debe tener :decimal decimales.',
    'declined' => 'Debes rechazar :attribute.',
    'declined_if' => 'Debes rechazar :attribute cuando :other es :value.',
    'different' => ':Attribute y :other deben ser distintos.',
    'digits' => ':Attribute debe tener :digits dígitos.',
    'digits_between' => ':Attribute debe tener entre :min y :max dígitos.',
    'dimensions' => ':Attribute no tiene unas dimensiones de imagen válidas.',
    'distinct' => ':Attribute tiene un valor repetido.',
    'doesnt_end_with' => ':Attribute no puede terminar con ninguno de estos valores: :values.',
    'doesnt_start_with' => ':Attribute no puede empezar con ninguno de estos valores: :values.',
    'email' => ':Attribute debe ser una dirección de correo válida.',
    'ends_with' => ':Attribute debe terminar con alguno de estos valores: :values.',
    'enum' => ':Attribute no es una opción válida.',
    'exists' => ':Attribute no es una opción válida.',
    'file' => ':Attribute debe ser un archivo.',
    'filled' => ':Attribute no puede quedar vacío.',
    'gt' => [
        'array' => ':Attribute debe tener más de :value elementos.',
        'file' => ':Attribute debe pesar más de :value kilobytes.',
        'numeric' => ':Attribute debe ser mayor que :value.',
        'string' => ':Attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => ':Attribute debe tener :value elementos o más.',
        'file' => ':Attribute debe pesar :value kilobytes o más.',
        'numeric' => ':Attribute debe ser mayor o igual que :value.',
        'string' => ':Attribute debe tener :value caracteres o más.',
    ],
    'image' => ':Attribute debe ser una imagen.',
    'in' => ':Attribute no es una opción válida.',
    'in_array' => ':Attribute no está en :other.',
    'integer' => ':Attribute debe ser un número entero.',
    'ip' => ':Attribute debe ser una dirección IP válida.',
    'ipv4' => ':Attribute debe ser una dirección IPv4 válida.',
    'ipv6' => ':Attribute debe ser una dirección IPv6 válida.',
    'json' => ':Attribute debe ser una cadena JSON válida.',
    'lowercase' => ':Attribute debe ir en minúsculas.',
    'lt' => [
        'array' => ':Attribute debe tener menos de :value elementos.',
        'file' => ':Attribute debe pesar menos de :value kilobytes.',
        'numeric' => ':Attribute debe ser menor que :value.',
        'string' => ':Attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => ':Attribute no puede tener más de :value elementos.',
        'file' => ':Attribute debe pesar :value kilobytes o menos.',
        'numeric' => ':Attribute debe ser menor o igual que :value.',
        'string' => ':Attribute debe tener :value caracteres o menos.',
    ],
    'mac_address' => ':Attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => ':Attribute no puede tener más de :max elementos.',
        'file' => ':Attribute no puede pesar más de :max kilobytes.',
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
    ],
    'max_digits' => ':Attribute no puede tener más de :max dígitos.',
    'mimes' => ':Attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => ':Attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => ':Attribute debe tener al menos :min elementos.',
        'file' => ':Attribute debe pesar al menos :min kilobytes.',
        'numeric' => ':Attribute debe ser al menos :min.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
    ],
    'min_digits' => ':Attribute debe tener al menos :min dígitos.',
    'missing' => ':Attribute no debe estar presente.',
    'missing_if' => ':Attribute no debe estar presente cuando :other es :value.',
    'missing_unless' => ':Attribute no debe estar presente salvo que :other sea :value.',
    'missing_with' => ':Attribute no debe estar presente si :values está presente.',
    'missing_with_all' => ':Attribute no debe estar presente si :values están presentes.',
    'multiple_of' => ':Attribute debe ser múltiplo de :value.',
    'not_in' => ':Attribute no es una opción válida.',
    'not_regex' => 'El formato de :attribute no es válido.',
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => ':Attribute debe contener al menos una letra.',
        'mixed' => ':Attribute debe contener al menos una mayúscula y una minúscula.',
        'numbers' => ':Attribute debe contener al menos un número.',
        'symbols' => ':Attribute debe contener al menos un símbolo.',
        'uncompromised' => 'Esta contraseña apareció en una filtración de datos. Elige otra.',
    ],
    'present' => ':Attribute debe estar presente.',
    'prohibited' => ':Attribute no está permitido.',
    'prohibited_if' => ':Attribute no está permitido cuando :other es :value.',
    'prohibited_unless' => ':Attribute no está permitido salvo que :other esté en :values.',
    'prohibits' => ':Attribute impide que :other esté presente.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => ':Attribute es obligatorio.',
    'required_array_keys' => ':Attribute debe incluir entradas para: :values.',
    'required_if' => ':Attribute es obligatorio cuando :other es :value.',
    'required_if_accepted' => ':Attribute es obligatorio cuando se acepta :other.',
    'required_unless' => ':Attribute es obligatorio salvo que :other esté en :values.',
    'required_with' => ':Attribute es obligatorio cuando :values está presente.',
    'required_with_all' => ':Attribute es obligatorio cuando :values están presentes.',
    'required_without' => ':Attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => ':Attribute es obligatorio cuando ninguno de :values está presente.',
    'same' => ':Attribute y :other deben coincidir.',
    'size' => [
        'array' => ':Attribute debe tener :size elementos.',
        'file' => ':Attribute debe pesar :size kilobytes.',
        'numeric' => ':Attribute debe ser :size.',
        'string' => ':Attribute debe tener :size caracteres.',
    ],
    'starts_with' => ':Attribute debe empezar con alguno de estos valores: :values.',
    'string' => ':Attribute debe ser texto.',
    'timezone' => ':Attribute debe ser una zona horaria válida.',
    'unique' => ':Attribute ya está en uso.',
    'uploaded' => 'No se pudo subir :attribute.',
    'uppercase' => ':Attribute debe ir en mayúsculas.',
    'url' => ':Attribute debe ser una URL válida.',
    'ulid' => ':Attribute debe ser un ULID válido.',
    'uuid' => ':Attribute debe ser un UUID válido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Los nombres de campo como los llama el jugador, no como se llaman en la
    | base de datos: "El correo es obligatorio", nunca "El email es
    | obligatorio". Las claves son las que aparecen en los validate() de la
    | app; la lista de índice (players.*) cubre el formulario de nueva partida.
    |
    */

    'attributes' => [
        'case_slug' => 'el caso',
        'code' => 'el código',
        'current_password' => 'la contraseña actual',
        'email' => 'el correo',
        'ending_type' => 'el tipo de final',
        'interrogation_enabled' => 'el interrogatorio',
        'mechanics' => 'las mecánicas',
        'mode' => 'el modo',
        'motive' => 'el móvil',
        'name' => 'el nombre',
        'package' => 'el paquete',
        'password' => 'la contraseña',
        'password_confirmation' => 'la confirmación de la contraseña',
        'payment_methods' => 'los medios de pago',
        'players' => 'los jugadores',
        'players.*.email' => 'el correo del jugador',
        'players.*.name' => 'el nombre del jugador',
        'promo_code' => 'el código promocional',
        'question' => 'la pregunta',
        'remember' => 'recordarme',
        'suspect_slug' => 'la persona acusada',
        'token' => 'el enlace',
        'weapon' => 'el arma',
    ],

];
