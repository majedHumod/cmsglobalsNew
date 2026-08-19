<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exercise content locales
    |--------------------------------------------------------------------------
    |
    | Canonical exercise rows keep English in `name` / `description` columns.
    | Per-locale content lives in the `translations` JSON column and can be
    | extended by overlay files under database/data/exercise-translations/{locale}.json
    |
    */

    'supported_locales' => ['ar', 'en', 'de', 'es'],

    /*
    | Default content language for exercise names/descriptions.
    | Kept independent from APP_LOCALE so an English system locale
    | does not hide Arabic exercise overlays in an Arabic-first product.
    */
    'default_locale' => env('EXERCISE_LOCALE', 'ar'),

    'fallback_locale' => env('EXERCISE_FALLBACK_LOCALE', 'en'),

    /*
    | Optional runtime override (set by SetRequestLocale / lang switcher).
    */
    'runtime_locale' => null,

    /*
    | Locales authored in overlay files (not shipped by RepDB).
    | These are preserved when re-importing RepDB with --force.
    */
    'overlay_locales' => ['ar'],

    'overlay_path' => database_path('data/exercise-translations'),

    'translatable_fields' => ['name', 'description', 'instructions', 'tips'],

];
