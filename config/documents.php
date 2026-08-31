<?php

return [
    'signatories' => [
        'cover_letter' => [
            'name' => env('SIGNATORY_COVER_NAME', 'D. K. T. Akintola'),
            'title' => env('SIGNATORY_COVER_TITLE', 'Deputy Registrar, Academic Affairs Division'),
            'for' => env('SIGNATORY_COVER_FOR', 'REGISTRAR'),
        ],
        'transcript' => [
            'name' => env('SIGNATORY_TRANSCRIPT_NAME', 'S. A. Ogunlade'),
            'title' => env('SIGNATORY_TRANSCRIPT_TITLE', 'Deputy Registrar, Academic Affairs Division'),
            'for' => env('SIGNATORY_TRANSCRIPT_FOR', 'Registrar'),
        ],
        'proficiency' => [
            'name' => env('SIGNATORY_PROFICIENCY_NAME', 'ADETUTU ADEWOLE'),
            'title' => env('SIGNATORY_PROFICIENCY_TITLE', 'Administrative Officer, Academic Affairs'),
            'for' => env('SIGNATORY_PROFICIENCY_FOR', 'REGISTRAR'),
        ],
    ],
];
