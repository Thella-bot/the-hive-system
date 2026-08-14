<?php

return [
    'name' => env('INSTITUTION_NAME', 'Honey Bee Culinary Institute'),
    'abbreviation' => env('INSTITUTION_ABBREVIATION', 'HBCI'),
    'registrar_office' => env('REGISTRAR_OFFICE', 'Registrar'),
    'address' => env('INSTITUTION_ADDRESS', 'Maseru, Lesotho'),
    'phone' => env('INSTITUTION_PHONE', '+266 2231 2345'),
    'email' => env('INSTITUTION_EMAIL', 'info@hbci.ac.ls'),
    'finance_office' => env('FINANCE_OFFICE', 'Finance'),
    'hr_office' => env('HR_OFFICE', 'Human Resources'),
    'academic_office' => env('ACADEMIC_OFFICE', 'Academic Office'),
    'student_affairs_office' => env('STUDENT_AFFAIRS_OFFICE', 'Student Affairs'),
    'default_salary' => env('DEFAULT_SALARY', 15000.00),
    'default_probation' => env('DEFAULT_PROBATION', '3 Months'),
    'default_pay_day' => env('DEFAULT_PAY_DAY', '25th'),
    'default_notice_period' => env('DEFAULT_NOTICE_PERIOD', '1 Month'),
    'default_registration_fee' => env('DEFAULT_REGISTRATION_FEE', 500.00),
];
