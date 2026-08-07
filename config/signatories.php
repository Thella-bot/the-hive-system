<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Signatory Definitions
    |--------------------------------------------------------------------------
    |
    | Maps PDF document types to the signatory role and title used in the
    | signature block. Each entry should have:
    |   - role: the Spatie role name to look up the user
    |   - title: the designation shown under the signature line
    |
    | If no user is found for the role, the fallback name is "AUTHORISED
    | SIGNATORY".
    |
    */

    'defaults' => [
        'name' => 'AUTHORISED SIGNATORY',
    ],

    'documents' => [
        // Academic / Admissions
        'acceptance'          => ['role' => 'registrar',      'title' => 'Registrar'],
        'rejection'           => ['role' => 'registrar',      'title' => 'Registrar'],
        'proof_of_enrolment'  => ['role' => 'registrar',      'title' => 'Registrar'],

        // Finance
        'payment_receipt'     => ['role' => 'finance',        'title' => 'Finance Officer'],
        'invoice'             => ['role' => 'finance',        'title' => 'Finance Officer'],

        // HR / Staff
        'staff_appointment'   => ['role' => 'director',       'title' => 'Director'],
        'staff_warning'       => ['role' => 'hr-manager',     'title' => 'HR Manager'],
        'student_warning'     => ['role' => 'dean',           'title' => 'Dean of Students'],
        'student_suspension'  => ['role' => 'director',       'title' => 'Director'],
        'student_expulsion'   => ['role' => 'director',       'title' => 'Director'],

        // General / Other
        'reference'           => ['role' => 'lecturer',       'title' => 'Lecturer'],
        'work_placement'      => ['role' => 'coordinator',    'title' => 'Work Placement Coordinator'],
        'general_correspondence' => ['role' => 'admin',        'title' => 'Administrator'],
        'internal_memo'       => ['role' => 'admin',          'title' => 'Administrator'],
    ],
];
