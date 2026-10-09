<?php

// /muestras/* — the private proposals for the client. A 4-digit PIN keeps them
// to the people it is given to (App\Http\Middleware\MuestrasPin).
return [
    'pin' => (string) env('MUESTRAS_PIN', ''),
];
