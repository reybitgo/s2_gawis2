<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GenealogyService
{
    /**
     * Get the full genealogy tree for a user, overlaid with earnings data.
     *
     * @param User $user The user for whom to fetch the genealogy.
     * @param string $earningsType The type of earnings to fetch ('unilevel' or 'mlm').
     * @param int $maxLevel The maximum depth of the tree.
     * @return array The nested genealogy tree.
     */
    public function getGenealogyTree(User $user, string $earningsType): array
    {
        // Step 1: Fetch the entire downline hierarchy using a recursive CTE
        $downlineUsers = $this->fetchDownlineHierarchy($user->id);

        if ($downlineUsers->isEmpty()) {
            return [
                'tree' => [],
                'stats' => [
                    'total_downlines' => 0,
                    'active_downlines' => 0,
                ]
            ];
        }

        // Step 2: Fetch the earnings data for all downlines in a single query
        $downlineIds = $downlineUsers->pluck('id')->toArray();
        $earningsData = $this->fetchEarnings($user->id, $downlineIds, $earningsType);

        // Step 3: Merge earnings data into the user objects
        $downlineUsers->each(function ($downline) use ($earningsData) {
            $downline->earnings = $earningsData[$downline->id] ?? 0;
        });

        // Step 4: Calculate stats
        $stats = [
            'total_downlines' => $downlineUsers->count(),
            'active_downlines' => $downlineUsers->where('status', 'active')->count(),
        ];

        // Step 5: Build the nested tree structure
        $tree = $this->buildTree($downlineUsers);

        return ['tree' => $tree, 'stats' => $stats];
    }

    private function fetchDownlineHierarchy(int $userId): \Illuminate\Support\Collection
    {
        $query = <<<'SQL'
            WITH RECURSIVE downline_cte AS (
                -- Anchor member: direct referrals of the starting user
                SELECT 
                    id, 
                    sponsor_id, 
                    1 AS level
                FROM users
                WHERE sponsor_id = ?

                UNION ALL

                -- Recursive member: referrals of the members found in the previous step
                SELECT 
                    u.id, 
                    u.sponsor_id, 
                    d.level + 1
                FROM users u
                INNER JOIN downline_cte d ON u.sponsor_id = d.id
            )
            SELECT u.id, u.fullname, u.username, u.created_at as join_date, u.network_status as status, cte.level, u.sponsor_id
            FROM users u
            JOIN downline_cte cte ON u.id = cte.id
            ORDER BY cte.level, u.fullname;
        SQL;

        return collect(DB::select($query, [$userId]));
    }

    private function fetchEarnings(int $recipientId, array $downlineIds, string $earningsType): array
    {
        if (empty($downlineIds)) {
            return [];
        }

        $earnings = DB::table('transactions')
            ->select(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.buyer_id")) as buyer_id'), DB::raw('SUM(amount) as total_earnings'))
            ->where('user_id', $recipientId)
            ->where('source_type', $earningsType)
            ->whereIn(DB::raw('JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.buyer_id"))'), $downlineIds)
            ->groupBy('buyer_id')
            ->pluck('total_earnings', 'buyer_id')
            ->all();

        // Cast to float
        return array_map('floatval', $earnings);
    }

    private function buildTree(\Illuminate\Support\Collection $nodes, $parentId = null): array
    {
        $tree = [];

        // If parentId is null, we are at the root, so we find the direct children of the authenticated user.
        // The CTE query already starts from the direct children, so their sponsor_id will be the initial user's ID.
        if ($parentId === null) {
            $rootNodes = $nodes->where('sponsor_id', $nodes->first()->sponsor_id ?? null);
        } else {
            $rootNodes = $nodes->where('sponsor_id', $parentId);
        }

        foreach ($rootNodes as $node) {
            $children = $this->buildTree($nodes, $node->id);
            if (!empty($children)) {
                $node->children = $children;
            }
            $tree[] = $node;
        }

        return $tree;
    }
}
