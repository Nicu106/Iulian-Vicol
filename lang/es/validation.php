<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Tienes que aceptar :attribute.',
    'accepted_if' => 'The :attribute field must be accepted when :other is :value.',
    'active_url' => 'The :attribute field must be a valid URL.',
    'after' => 'La fecha de :attribute tiene que ser posterior a :date.',
    'after_or_equal' => 'La fecha de :attribute tiene que ser :date o posterior.',
    'alpha' => 'The :attribute field must only contain letters.',
    'alpha_dash' => 'The :attribute field must only contain letters, numbers, dashes, and underscores.',
    'alpha_num' => 'The :attribute field must only contain letters and numbers.',
    'any_of' => 'The :attribute field is invalid.',
    'array' => 'El campo :attribute tiene que ser una lista.',
    'ascii' => 'The :attribute field must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'La fecha de :attribute tiene que ser anterior a :date.',
    'before_or_equal' => 'The :attribute field must be a date before or equal to :date.',
    'between' => [
        'array' => 'El campo :attribute tiene que tener entre :min y :max elementos.',
        'file' => 'El archivo de :attribute tiene que pesar entre :min y :max kilobytes.',
        'numeric' => 'El campo :attribute tiene que estar entre :min y :max.',
        'string' => 'El campo :attribute tiene que tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute tiene que ser sí o no.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'El campo :attribute no coincide.',
    'contains' => 'The :attribute field is missing a required value.',
    'current_password' => 'The password is incorrect.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_equals' => 'The :attribute field must be a date equal to :date.',
    'date_format' => 'The :attribute field must match the format :format.',
    'decimal' => 'The :attribute field must have :decimal decimal places.',
    'declined' => 'The :attribute field must be declined.',
    'declined_if' => 'The :attribute field must be declined when :other is :value.',
    'different' => 'The :attribute field and :other must be different.',
    'digits' => 'The :attribute field must be :digits digits.',
    'digits_between' => 'The :attribute field must be between :min and :max digits.',
    'dimensions' => 'The :attribute field has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_contain' => 'The :attribute field must not contain any of the following: :values.',
    'doesnt_end_with' => 'The :attribute field must not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute field must not start with one of the following: :values.',
    'email' => 'El campo :attribute no parece un correo válido.',
    'ends_with' => 'The :attribute field must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'The :attribute field must have one of the following extensions: :values.',
    'file' => 'El campo :attribute tiene que ser un archivo.',
    'filled' => 'El campo :attribute no puede quedarse vacío.',
    'gt' => [
        'array' => 'The :attribute field must have more than :value items.',
        'file' => 'The :attribute field must be greater than :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than :value.',
        'string' => 'The :attribute field must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute field must have :value items or more.',
        'file' => 'The :attribute field must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be greater than or equal to :value.',
        'string' => 'The :attribute field must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'El campo :attribute tiene que ser una imagen.',
    'in' => 'El valor de :attribute no vale.',
    'in_array' => 'The :attribute field must exist in :other.',
    'in_array_keys' => 'The :attribute field must contain at least one of the following keys: :values.',
    'integer' => 'El campo :attribute tiene que ser un número entero.',
    'ip' => 'The :attribute field must be a valid IP address.',
    'ipv4' => 'The :attribute field must be a valid IPv4 address.',
    'ipv6' => 'The :attribute field must be a valid IPv6 address.',
    'json' => 'The :attribute field must be a valid JSON string.',
    'list' => 'The :attribute field must be a list.',
    'lowercase' => 'The :attribute field must be lowercase.',
    'lt' => [
        'array' => 'The :attribute field must have less than :value items.',
        'file' => 'The :attribute field must be less than :value kilobytes.',
        'numeric' => 'The :attribute field must be less than :value.',
        'string' => 'The :attribute field must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute field must not have more than :value items.',
        'file' => 'The :attribute field must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute field must be less than or equal to :value.',
        'string' => 'The :attribute field must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute field must be a valid MAC address.',
    'max' => [
        'array' => 'El campo :attribute no puede tener más de :max elementos.',
        'file' => 'El archivo de :attribute no puede pesar más de :max kilobytes.',
        'numeric' => 'El campo :attribute no puede ser mayor que :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'max_digits' => 'The :attribute field must not have more than :max digits.',
    'mimes' => 'El campo :attribute tiene que ser un archivo de tipo :values.',
    'mimetypes' => 'El campo :attribute tiene que ser un archivo de tipo :values.',
    'min' => [
        'array' => 'El campo :attribute tiene que tener al menos :min elementos.',
        'file' => 'El archivo de :attribute tiene que pesar al menos :min kilobytes.',
        'numeric' => 'El campo :attribute tiene que ser al menos :min.',
        'string' => 'El campo :attribute tiene que tener al menos :min caracteres.',
    ],
    'min_digits' => 'The :attribute field must have at least :min digits.',
    'missing' => 'The :attribute field must be missing.',
    'missing_if' => 'The :attribute field must be missing when :other is :value.',
    'missing_unless' => 'The :attribute field must be missing unless :other is :value.',
    'missing_with' => 'The :attribute field must be missing when :values is present.',
    'missing_with_all' => 'The :attribute field must be missing when :values are present.',
    'multiple_of' => 'The :attribute field must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute field format is invalid.',
    'numeric' => 'El campo :attribute tiene que ser un número.',
    'password' => [
        'letters' => 'The :attribute field must contain at least one letter.',
        'mixed' => 'The :attribute field must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute field must contain at least one number.',
        'symbols' => 'The :attribute field must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_if_accepted' => 'The :attribute field is prohibited when :other is accepted.',
    'prohibited_if_declined' => 'The :attribute field is prohibited when :other is declined.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute field format is invalid.',
    'required' => 'Falta rellenar :attribute.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'Falta rellenar :attribute.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_if_declined' => 'The :attribute field is required when :other is declined.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute field must match :other.',
    'size' => [
        'array' => 'The :attribute field must contain :size items.',
        'file' => 'The :attribute field must be :size kilobytes.',
        'numeric' => 'The :attribute field must be :size.',
        'string' => 'The :attribute field must be :size characters.',
    ],
    'starts_with' => 'The :attribute field must start with one of the following: :values.',
    'string' => 'El campo :attribute tiene que ser texto.',
    'timezone' => 'The :attribute field must be a valid timezone.',
    'unique' => 'Ya existe otro con ese :attribute.',
    'uploaded' => 'No se ha podido subir :attribute. Puede que pese demasiado.',
    'uppercase' => 'The :attribute field must be uppercase.',
    'url' => 'El campo :attribute tiene que ser un enlace válido.',
    'ulid' => 'The :attribute field must be a valid ULID.',
    'uuid' => 'The :attribute field must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'brand' => 'marca',
        'model' => 'modelo',
        'year' => 'año',
        'price' => 'precio',
        'original_price' => 'precio anterior',
        'offer_price' => 'precio de oferta',
        'offer_expires_at' => 'fecha de fin de la oferta',
        'offer_type' => 'tipo de oferta',
        'offer_description' => 'descripción de la oferta',
        'status' => 'estado',
        'mileage' => 'kilómetros',
        'fuel' => 'combustible',
        'fuel_type' => 'combustible',
        'transmission' => 'cambio',
        'engine' => 'motor',
        'engine_capacity' => 'cilindrada',
        'power' => 'potencia',
        'drivetrain' => 'tracción',
        'color' => 'color',
        'body_type' => 'carrocería',
        'condition' => 'etiqueta medioambiental',
        'description' => 'descripción',
        'features' => 'equipamiento',
        'video_url' => 'enlace del vídeo',
        'cover_image' => 'portada',
        'gallery_images' => 'fotos',
        'gallery_images.*' => 'una de las fotos',
        'images' => 'fotos',
        'images.*' => 'una de las fotos',
        'location' => 'ubicación',
        'priority' => 'orden',
        'internal_notes' => 'notas',
        'title' => 'título',
        'seller_name' => 'nombre',
        'seller_phone' => 'teléfono',
        'seller_email' => 'correo',
        'author_name' => 'nombre',
        'author_location' => 'ubicación',
        'quote' => 'opinión',
        'image' => 'foto',
        'order_index' => 'orden',
        'is_active' => 'si se enseña',
        'name' => 'nombre',
        'email' => 'correo',
        'password' => 'contraseña',
        'phone' => 'teléfono',
        'message' => 'mensaje',
        'subject' => 'asunto',
    ],

];
