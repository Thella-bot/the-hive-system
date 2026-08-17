<?php
declare(strict_types=1);

namespace App\Enums;

enum GradableType: string
{
    case QUIZ = 'quiz';
    case TEST = 'test';
    case ASSIGNMENT = 'assignment';
    case MIDTERM_EXAM = 'mid-term_exam';
    case FINAL_EXAM = 'final_exam';
}