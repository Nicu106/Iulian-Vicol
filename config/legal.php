<?php

/*
 * Who IV MOTORCLASS is, legally — what /terminos and /privacidad print
 * (App\Http\Controllers\LegalController). Spanish law (LSSI art. 10) asks the
 * site to show the holder's name, NIF and address.
 *
 * In .env, never in git: for a sole trader (autónomo) the NIF is the DNI.
 *
 *   LEGAL_HOLDER="Nombre Apellido Apellido"     or "IV MOTORCLASS S.L."
 *   LEGAL_NIF="12345678Z"
 *   LEGAL_ADDRESS="Calle …, 29000 Málaga"
 *   LEGAL_REGISTRY="Registro Mercantil de Málaga, tomo …, folio …, hoja MA-…"   (an S.L. only)
 *
 * A value left out is simply not printed.
 */
return [
    'holder'   => env('LEGAL_HOLDER'),
    'nif'      => env('LEGAL_NIF'),
    'address'  => env('LEGAL_ADDRESS'),
    'registry' => env('LEGAL_REGISTRY'),

    // the date the texts were last changed, printed under each title
    'updated'  => '2026-10-29',
];
