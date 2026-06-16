<?php

namespace App\Enums;

/**
 * User Roles for The Hive Culinary Institute
 *
 * 21 roles covering all functional domains with domain-based access control.
 */
enum UserRole: string
{
    // === Administrative ===
    case SUPER_ADMIN = 'super-admin';
    case IT_SUPPORT = 'it-support';

    // === Academic Leadership ===
    case ACADEMIC_DIRECTOR = 'academic-director';
    case PROGRAM_COORDINATOR = 'program-coordinator';

    // === Faculty (Teaching) ===
    case CHEF_INSTRUCTOR = 'chef-instructor';
    case PASTRY_INSTRUCTOR = 'pastry-instructor';
    case SOUS_CHEF = 'sous-chef';

    // === Student ===
    case STUDENT = 'student';

    // === Student Lifecycle ===
    case ADMISSIONS_OFFICER = 'admissions-officer';
    case REGISTRAR = 'registrar';
    case EXAMINATION_CELL = 'examination-cell';

    // === Finance & Procurement ===
    case FINANCE = 'finance';
    case PROCUREMENT_MANAGER = 'procurement-manager';
    case STOREKEEPER = 'storekeeper';
    case HR_MANAGER = 'hr-manager';

    // === Support Services ===
    case LIBRARIAN = 'librarian';
    case CAREER_SERVICES = 'career-services';
    case EVENTS_PR_MANAGER = 'events-pr-manager';
    case CAFETERIA_MANAGER = 'cafeteria-manager';

    // === External ===
    case PARENT_GUARDIAN = 'parent-guardian';
    case ALUMNI = 'alumni';

    /**
     * Get all roles as array of strings
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get display name for the role
     */
    public function displayName(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::IT_SUPPORT => 'IT Support / System Admin',
            self::ACADEMIC_DIRECTOR => 'Academic Director',
            self::PROGRAM_COORDINATOR => 'Program Coordinator',
            self::CHEF_INSTRUCTOR => 'Chef Instructor',
            self::PASTRY_INSTRUCTOR => 'Pastry / Bakery Instructor',
            self::SOUS_CHEF => 'Sous Chef (Teaching Assistant)',
            self::STUDENT => 'Student',
            self::ADMISSIONS_OFFICER => 'Admissions Officer',
            self::REGISTRAR => 'Registrar',
            self::EXAMINATION_CELL => 'Examination Cell',
            self::FINANCE => 'Finance / Accounts',
            self::PROCUREMENT_MANAGER => 'Procurement Manager',
            self::STOREKEEPER => 'Storekeeper',
            self::HR_MANAGER => 'HR Manager',
            self::LIBRARIAN => 'Librarian',
            self::CAREER_SERVICES => 'Career Services',
            self::EVENTS_PR_MANAGER => 'Events & PR Manager',
            self::CAFETERIA_MANAGER => 'Cafeteria / Bistro Manager',
            self::PARENT_GUARDIAN => 'Parent / Guardian',
            self::ALUMNI => 'Alumni',
        };
    }

    /**
     * Get the primary domain this role belongs to
     */
    public function domain(): string
    {
        return match ($this) {
            self::SUPER_ADMIN, self::IT_SUPPORT => 'System Administration',
            self::ACADEMIC_DIRECTOR, self::PROGRAM_COORDINATOR => 'Academic Leadership',
            self::CHEF_INSTRUCTOR, self::PASTRY_INSTRUCTOR, self::SOUS_CHEF => 'Faculty',
            self::STUDENT => 'Student Services',
            self::ADMISSIONS_OFFICER => 'Admissions',
            self::REGISTRAR, self::EXAMINATION_CELL => 'Academic Operations',
            self::FINANCE => 'Finance',
            self::PROCUREMENT_MANAGER, self::STOREKEEPER => 'Inventory & Procurement',
            self::HR_MANAGER => 'Human Resources',
            self::LIBRARIAN => 'Library',
            self::CAREER_SERVICES => 'Career Services',
            self::EVENTS_PR_MANAGER => 'Events & PR',
            self::CAFETERIA_MANAGER => 'Food Service',
            self::PARENT_GUARDIAN, self::ALUMNI => 'External',
        };
    }

    /**
     * Check if role is considered staff (not student)
     */
    public function isStaff(): bool
    {
        return $this !== self::STUDENT
            && $this !== self::PARENT_GUARDIAN
            && $this !== self::ALUMNI;
    }

    /**
     * Check if role is a faculty/instructor role
     */
    public function isFaculty(): bool
    {
        return in_array($this, [
            self::CHEF_INSTRUCTOR,
            self::PASTRY_INSTRUCTOR,
            self::SOUS_CHEF,
            self::ACADEMIC_DIRECTOR,
        ]);
    }

    /**
     * Check if role is administrative (can manage users, settings)
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::IT_SUPPORT,
        ]);
    }

    /**
     * Check if role can access financial data
     */
    public function canAccessFinance(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::FINANCE,
            self::HR_MANAGER,
        ]);
    }

    /**
     * Check if role can manage student records
     */
    public function canManageStudents(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ACADEMIC_DIRECTOR,
            self::PROGRAM_COORDINATOR,
            self::ADMISSIONS_OFFICER,
            self::REGISTRAR,
            self::EXAMINATION_CELL,
            self::CHEF_INSTRUCTOR,
            self::PASTRY_INSTRUCTOR,
        ]);
    }

    /**
     * Check if role can access kitchen/inventory
     */
    public function canAccessKitchen(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ACADEMIC_DIRECTOR,
            self::CHEF_INSTRUCTOR,
            self::PASTRY_INSTRUCTOR,
            self::SOUS_CHEF,
            self::PROCUREMENT_MANAGER,
            self::STOREKEEPER,
        ]);
    }
}