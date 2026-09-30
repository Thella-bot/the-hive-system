<?php
declare(strict_types=1);

namespace App\Support\Concerns;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Server-side table sorting.
 *
 * Sort field names arrive from the browser, so they are only ever applied after
 * being looked up in a caller-supplied allow-list. A request for an unknown
 * field silently falls back to the default order rather than reaching the
 * database.
 */
trait AppliesSorting
{
    /**
     * Apply a validated sort to a query.
     *
     * @param  array<string, string>  $sortable  field => column (a column may
     *                                           carry an explicit direction
     *                                           suffix, e.g. "profiles.name desc")
     * @param  string  $defaultField
     * @param  string  $defaultDirection
     */
    protected function applySorting(
        Builder $query,
        array $sortable,
        string $defaultField = '',
        string $defaultDirection = 'asc',
    ): Builder {
        $field = $this->requestedSortField();
        $direction = $this->requestedSortDirection();

        if ($field === null || ! array_key_exists($field, $sortable)) {
            $field = $defaultField;
            $direction = $defaultDirection;
        }

        $column = $sortable[$field] ?? null;

        if ($column === null) {
            return $query;
        }

        // Support a "column desc" entry so related-table sorts can be expressed
        // without a second map.
        if (str_contains($column, ' ')) {
            [$column, $columnDirection] = preg_split('/\s+/', $column, 2);
            $direction = strtolower($columnDirection) === 'desc' ? 'desc' : 'asc';
        }

        return $query->orderBy($column, $direction === 'desc' ? 'desc' : 'asc');
    }

    /**
     * Read `sort` and `direction` from the current request, if present.
     *
     * @return array{0: string|null, 1: string}
     */
    protected function requestedSorting(Request $request): array
    {
        return [
            $this->requestedSortField($request),
            $this->requestedSortDirection($request),
        ];
    }

    protected function requestedSortField(?Request $request = null): ?string
    {
        $request ??= request();

        $field = $request->query('sort');

        return is_string($field) && $field !== '' ? $field : null;
    }

    protected function requestedSortDirection(?Request $request = null): string
    {
        $request ??= request();

        return strtolower((string) $request->query('direction')) === 'desc' ? 'desc' : 'asc';
    }
}
