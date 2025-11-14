<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

trait HasPaginationLimit
{
    /**
     * Get the pagination limit from request or use default
     *
     * @param Request $request
     * @param int $default
     * @return int
     */
    protected function getPerPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->get('per_page', $default);

        // Validate and constrain the per_page value
        $allowedValues = [10, 15, 25, 50, 100];

        if (!in_array($perPage, $allowedValues)) {
            $perPage = $default;
        }

        return $perPage;
    }
}
