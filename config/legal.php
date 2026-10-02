<?php

/*
|--------------------------------------------------------------------------
| Legal identity and document versions
|--------------------------------------------------------------------------
|
| Everything the privacy policy, the terms and the cookie policy say about
| WHO is behind the platform lives here, so the texts themselves never have
| to be edited to fill in a name or an address.
|
| Why each value is not optional in practice:
|
|  - Ley 1581 de 2012 / Decreto 1074 de 2015 (art. 2.2.2.25.3.1): a data
|    policy must name the controller (razon social o nombre) and give a way to
|    reach it.
|  - Ley 1480 de 2011, art. 50: an online seller must show its razon social,
|    NIT, notification address, phone and email before the consumer buys.
|
| Anything left empty renders as a visible "[PENDIENTE: ...]" marker on the
| legal pages — a missing value is never silently hidden.
|
*/

return [

    // Legal name of the person or company that operates the platform.
    'entity_name' => env('LEGAL_ENTITY_NAME', ''),

    // NIT for a company, cedula for a natural person.
    'entity_id' => env('LEGAL_ID', ''),

    // Judicial notification address (Ley 1480, art. 50).
    'address' => env('LEGAL_ADDRESS', ''),

    'city' => env('LEGAL_CITY', ''),

    // The channel for PQRS, retracto requests and habeas data queries.
    'email' => env('LEGAL_EMAIL', ''),

    'phone' => env('LEGAL_PHONE', ''),

    // Bump a version whenever its text changes in a way a user must be told
    // about. The versions are stored on the user at registration, which is
    // the proof of what they accepted and when.
    'privacy_version' => env('LEGAL_PRIVACY_VERSION', '1.0'),
    'terms_version' => env('LEGAL_TERMS_VERSION', '1.0'),
    'cookies_version' => env('LEGAL_COOKIES_VERSION', '1.0'),

    // Date the current texts took effect (Y-m-d).
    'updated_at' => env('LEGAL_UPDATED_AT', '2026-10-01'),

];
