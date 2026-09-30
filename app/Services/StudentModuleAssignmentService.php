<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Module;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keeps a student's module list equal to their programme's module list.
 *
 * Programme membership lives in the `programme_module` pivot (modules themselves
 * carry no programme_id), and a module may belong to several programmes, so the
 * student's list is the union of the pivot rows for their programme.
 *
 * This is the single place the rule is applied: student creation, programme
 * transfers and the students:sync-modules command all come through here, so a
 * student's modules cannot drift away from their programme.
 */
class StudentModuleAssignmentService
{
    /**
     * Every module on a programme, in curriculum order.
     *
     * @return Collection<int, Module>
     */
    public function modulesFor(?Programme $programme): Collection
    {
        if (! $programme) {
            return collect();
        }

        return Module::query()
            ->join('programme_module as pm', 'pm.module_id', '=', 'modules.id')
            ->where('pm.programme_id', $programme->id)
            ->orderBy('pm.year_level')
            ->orderBy('pm.semester')
            ->orderBy('pm.order_column')
            ->orderBy('modules.code')
            ->select('modules.*')
            ->get();
    }

    /**
     * @return array<int, int>
     */
    public function moduleIdsFor(?Programme $programme): array
    {
        return $this->modulesFor($programme)->pluck('id')->all();
    }

    /**
     * Make the student's modules exactly the programme's modules.
     *
     * @return array{expected: array<int, int>, actual: array<int, int>, added: array<int, int>, removed: array<int, int>}
     */
    public function sync(User $student, ?Programme $programme): array
    {
        $expected = $this->moduleIdsFor($programme);
        $actual = $this->currentModuleIds($student);

        $added = array_values(array_diff($expected, $actual));
        $removed = array_values(array_diff($actual, $expected));

        if ($added !== [] || $removed !== []) {
            $student->modules()->sync($expected);
        }

        return [
            'expected' => $expected,
            'actual' => $actual,
            'added' => $added,
            'removed' => $removed,
        ];
    }

    /**
     * The modules a student currently holds.
     *
     * @return array<int, int>
     */
    public function currentModuleIds(User $student): array
    {
        return DB::table('module_user')
            ->where('user_id', $student->id)
            ->pluck('module_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Compare a student against their programme without writing anything.
     *
     * @return array{in_sync: bool, expected: int, actual: int, added: array<int, int>, removed: array<int, int>}
     */
    public function inspect(User $student, ?Programme $programme): array
    {
        $expected = $this->moduleIdsFor($programme);
        $actual = $this->currentModuleIds($student);

        $added = array_values(array_diff($expected, $actual));
        $removed = array_values(array_diff($actual, $expected));

        return [
            'in_sync' => $added === [] && $removed === [],
            'expected' => count($expected),
            'actual' => count($actual),
            'added' => $added,
            'removed' => $removed,
        ];
    }
}
