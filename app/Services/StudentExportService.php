<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cohort;
use App\Models\Department;
use App\Models\Programme;
use App\Models\User;
use App\Support\Concerns\AppliesSorting;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Builds the "typical information" extract of the student register.
 *
 * The listing on the students page and this export share one query so an
 * administrator exporting from a filtered view gets exactly the rows they can
 * see, not a different set.
 */
class StudentExportService
{
    use AppliesSorting;

    /**
     * Roles that appear in the register. Matches the students listing.
     */
    public const ROLES = ['student', 'parent-guardian', 'alumni'];

    /**
     * The columns offered in the export, in display order.
     *
     * @return array<string, array{label: string, group: string}>
     */
    public function columns(): array
    {
        return [
            'student_number' => ['label' => 'Student Number', 'group' => 'identity'],
            'name' => ['label' => 'Full Name', 'group' => 'identity'],
            'email' => ['label' => 'Email', 'group' => 'identity'],
            'phone' => ['label' => 'Phone', 'group' => 'contact'],
            'gender' => ['label' => 'Gender', 'group' => 'identity'],
            'date_of_birth' => ['label' => 'Date of Birth', 'group' => 'identity'],
            'national_id_number' => ['label' => 'National ID', 'group' => 'identity'],
            'address' => ['label' => 'Address', 'group' => 'contact'],
            'programme' => ['label' => 'Programme', 'group' => 'academic'],
            'programme_code' => ['label' => 'Programme Code', 'group' => 'academic'],
            'department' => ['label' => 'Department', 'group' => 'academic'],
            'cohort' => ['label' => 'Cohort', 'group' => 'academic'],
            'academic_year' => ['label' => 'Academic Year', 'group' => 'academic'],
            'status' => ['label' => 'Status', 'group' => 'academic'],
            'enrollment_date' => ['label' => 'Enrollment Date', 'group' => 'academic'],
            'expected_graduation_date' => ['label' => 'Expected Graduation', 'group' => 'academic'],
            'graduation_date' => ['label' => 'Graduation Date', 'group' => 'academic'],
            'modules_enrolled' => ['label' => 'Modules Enrolled', 'group' => 'academic'],
            'average_mark' => ['label' => 'Average Mark', 'group' => 'academic'],
            'emergency_contact_name' => ['label' => 'Emergency Contact', 'group' => 'contact'],
            'emergency_contact_phone' => ['label' => 'Emergency Contact Phone', 'group' => 'contact'],
            'emergency_contact_relationship' => ['label' => 'Emergency Contact Relationship', 'group' => 'contact'],
            'dietary_restrictions' => ['label' => 'Dietary Restrictions', 'group' => 'contact'],
            'last_login_at' => ['label' => 'Last Login', 'group' => 'account'],
            'created_at' => ['label' => 'Record Created', 'group' => 'account'],
        ];
    }

    /**
     * The columns a plain "export everything" download contains.
     *
     * @return array<int, string>
     */
    public function defaultColumns(): array
    {
        return array_keys($this->columns());
    }

    /**
     * The filterable statuses, so the UI does not hard-code them.
     *
     * @return array<int, string>
     */
    public function statuses(): array
    {
        return ['active', 'graduated', 'on_leave', 'suspended', 'withdrawn'];
    }

    /**
     * Columns the register listing can be sorted by.
     *
     * Keys are what the browser sends; values are real columns. Anything not
     * listed here is ignored, so a crafted `sort` parameter cannot reach the
     * database.
     *
     * @return array<string, string>
     */
    public function sortableColumns(): array
    {
        return [
            'name' => 'name',
            'student_number' => 'student_number',
            'email' => 'email',
            'programme' => 'programme_id',
            'created_at' => 'created_at',
            'last_login_at' => 'last_login_at',
            'enrollment_date' => 'profiles.enrollment_date',
            'cohort' => 'profiles.cohort_id',
            'status' => 'profiles.status',
        ];
    }

    /**
     * The student register, filtered. Shared with the listing screen.
     *
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters = []): Builder
    {
        $query = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', self::ROLES))
            ->with([
                'profile.cohort.academicYear',
                'programme.department',
            ])
            ->withCount('enrollments')
            ->withAvg(['grades as average_mark' => fn ($q) => $q->whereNotNull('marks')], 'marks')            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn ($p) => $p->where('student_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->whereHas(
                'profile',
                fn ($p) => $p->where('status', $status)
            ))
            ->when($filters['programme_id'] ?? null, fn ($query, $id) => $query->where('programme_id', $id))
            ->when($filters['cohort_id'] ?? null, fn ($query, $id) => $query->whereHas(
                'profile',
                fn ($p) => $p->where('cohort_id', $id)
            ))
            ->when($filters['department_id'] ?? null, fn ($query, $id) => $query->whereHas(
                'programme',
                fn ($p) => $p->where('department_id', $id)
            ))
            ->when($filters['unassigned'] ?? null, fn ($query) => $query->whereNull('programme_id'))
            ->when(! empty($filters['ids']), fn ($query) => $query->whereIn('users.id', $filters['ids']));

        // Sorting can only be resolved once the joins for related columns exist,
        // so add them here rather than in the base query.
        $sortable = $this->sortableColumns();
        $field = $this->requestedSortField();

        if ($field !== null && str_starts_with($sortable[$field] ?? '', 'profiles.')) {
            $query->leftJoin('profiles', function ($join) {
                $join->on('profiles.profileable_id', '=', 'users.id')
                    ->where('profiles.profileable_type', User::class);
            })->select('users.*');
        }

        $this->applySorting($query, $sortable, 'student_number', 'asc');

        return $query;
    }

    /**
     * Flatten the register into rows keyed by column name.
     *
     * @param  array<int, string>  $columns
     * @return Collection<int, array<string, string>>
     */
    public function rows(iterable $students, array $columns): Collection
    {
        return collect($students)->map(function (User $student) use ($columns) {
            $profile = $student->profile;
            $values = [
                'student_number' => $student->student_number ?? $profile?->student_number,
                'name' => $student->name,
                'email' => $student->email,
                'phone' => $profile?->phone,
                'gender' => $student->gender,
                'date_of_birth' => $profile?->date_of_birth?->format('Y-m-d'),
                'national_id_number' => $student->national_id_number,
                'address' => $profile?->address,
                'programme' => $student->programme?->name,
                'programme_code' => $student->programme?->code,
                'department' => $student->programme?->department?->name,
                'cohort' => $profile?->cohort?->name,
                'academic_year' => $profile?->cohort?->academicYear?->name,
                'status' => $profile?->status,
                'enrollment_date' => $profile?->enrollment_date?->format('Y-m-d'),
                'expected_graduation_date' => $profile?->expected_graduation_date?->format('Y-m-d'),
                'graduation_date' => $profile?->graduation_date?->format('Y-m-d'),
                'modules_enrolled' => $student->enrollments_count ?? 0,
                'average_mark' => isset($student->average_mark) ? round((float) $student->average_mark, 2) : null,
                'emergency_contact_name' => $profile?->emergency_contact_name,
                'emergency_contact_phone' => $profile?->emergency_contact_phone,
                'emergency_contact_relationship' => $profile?->emergency_contact_relationship,
                'dietary_restrictions' => $this->joinList($profile?->dietary_restrictions),
                'last_login_at' => $student->last_login_at?->format('Y-m-d H:i'),
                'created_at' => $student->created_at?->format('Y-m-d H:i'),
            ];

            $row = [];
            foreach ($columns as $column) {
                $row[$column] = (string) ($values[$column] ?? '');
            }

            return $row;
        });
    }

    /**
     * The filter options that make an export reproducible from the listing.
     *
     * @return array<string, Collection>
     */
    public function filterOptions(): array
    {
        return [
            'programmes' => Programme::with('department:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'department_id'])
                ->map(fn (Programme $p) => [
                    'value' => $p->id,
                    'label' => $p->name,
                    'department' => $p->department?->name,
                ]),
            'cohorts' => Cohort::orderByDesc('start_date')
                ->limit(200)
                ->get(['id', 'name', 'department_id'])
                ->map(fn (Cohort $c) => [
                    'value' => $c->id,
                    'label' => $c->name,
                ]),
            'departments' => Department::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Department $d) => [
                    'value' => $d->id,
                    'label' => $d->name,
                ]),
            'statuses' => collect($this->statuses()),
        ];
    }

    /**
     * @param  array<int, string>|null  $list
     */
    protected function joinList(?array $list): ?string
    {
        if (empty($list)) {
            return null;
        }

        return implode('; ', array_filter($list, 'is_scalar'));
    }
}
