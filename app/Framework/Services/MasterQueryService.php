<?php

namespace App\Framework\Services;

use Illuminate\Http\Request;

class MasterQueryService
{
    public function search(
        string $model,
        Request $request,
        array $searchColumns = [],
        array $filters = [],
        array $orderBy = []
    ) {
        $query = $model::query();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search') && count($searchColumns)) {

            $search = $request->search;

            $query->where(function ($q) use ($searchColumns, $search) {

                foreach ($searchColumns as $column) {

                    $q->orWhere(
                        $column,
                        'like',
                        "%{$search}%"
                    );

                }

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        foreach ($filters as $requestKey => $column) {

            if (
                $request->has($requestKey)
                &&
                $request->$requestKey !== ''
            ) {

                $query->where(
                    $column,
                    $request->$requestKey
                );

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        foreach ($orderBy as $column => $direction) {

            $query->orderBy($column, $direction);

        }

        return $query
            ->paginate(config('rds.pagination.per_page'))
            ->withQueryString();
    }
}