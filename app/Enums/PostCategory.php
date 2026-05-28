<?php

namespace App\Enums;

enum PostCategory: string
{
    case GENERAL = 'general';
    case ACADEMIC = 'academic';
    case EVENT = 'event';
    case HR = 'hr';
    case MODULE = 'module';
    case STUDENT = 'student';
    case STAFF = 'staff';
    case ADMINISTRATIVE = 'administrative';
    case FINANCIAL = 'financial';
    case HEALTH_SAFETY = 'health_safety';
}