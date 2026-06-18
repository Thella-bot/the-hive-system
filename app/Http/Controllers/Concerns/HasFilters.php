<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilters
{
    /**
     * Apply common filters to a query based on request parameters.
     */
    protected function applyFilters(Builder $query, Request $request, array $config = []): Builder
    {
        $defaults = [
            'status' => false,
            'search' => false,
            'searchColumns' => [],
            'date_from' => false,
            'date_to' => false,
            'category_id' => false,
            'budget_id' => false,
            'dateColumn' => 'created_at',
        ];

        $config = array_merge($defaults, $config);

        if ($config['status'] && $request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($config['search'] && $request->has('search') && $request->search) {
            $search = $request->search;
            $columns = $config['searchColumns'] ?? ['name'];

            $query->where(function ($q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($config['date_from'] && $request->has('date_from') && $request->date_from) {
            $query->whereDate($config['dateColumn'], '>=', $request->date_from);
        }

        if ($config['date_to'] && $request->has('date_to') && $request->date_to) {
            $query->whereDate($config['dateColumn'], '<=', $request->date_to);
        }

        if ($config['category_id'] && $request->has('category_id') && $request->category_id) {
            $column = $config['categoryColumn'] ?? 'expense_category_id';
            $query->where($column, $request->category_id);
        }

        if ($config['budget_id'] && $request->has('budget_id') && $request->budget_id) {
            $query->where('budget_id', $request->budget_id);
        }

        return $query;
    }

    /**
     * Get filter inputs from request for passing to Inertia.
     */
    protected function getFilterInputs(Request $request, array $keys): array
    {
        return $request->only($keys);
    }
}