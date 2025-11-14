<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    public function index()
    {
        $packages = Package::with('mlmSettings')->active()->available()->ordered()->get();

        $productColumns = \Illuminate\Support\Facades\Schema::getColumnListing('products');
        $groupByColumns = array_map(function($column) {
            return 'products.' . $column;
        }, $productColumns);

        $topProducts = Product::select('products.*', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->where('products.deleted_at', null)
            ->groupBy($groupByColumns)
            ->orderByDesc('total_sold')
            ->with('unilevelSettings')
            ->get();

        $soldProductIds = $topProducts->pluck('id');
        $remainingCount = 4 - $topProducts->count();

        if ($remainingCount > 0) {
            $otherProducts = Product::whereNotIn('id', $soldProductIds)
                ->orderBy('created_at', 'desc')
                ->limit($remainingCount)
                ->with('unilevelSettings')
                ->get();
            $topProducts = $topProducts->concat($otherProducts);
        } else {
            $topProducts = $topProducts->take(4);
        }

        return view('frontend.index', compact('packages', 'topProducts'));
    }

    public function products(Request $request)
    {
        $query = Product::active()->available()->with('unilevelSettings');

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'sort_order');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('sort_order')->orderBy('name');
        }

        $perPage = $this->getPerPage($request, 12);
        $products = $query->paginate($perPage)->appends($request->query());

        // Get all unique categories for filter
        $categories = Product::active()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort();

        return view('frontend.products', compact('products', 'categories', 'perPage'));
    }
}