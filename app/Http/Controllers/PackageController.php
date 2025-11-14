<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PackageController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    public function index(Request $request)
    {
        $query = Package::active()->available()->ordered();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->has('sort')) {
            switch ($request->get('sort')) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'points_high':
                    $query->orderBy('points_awarded', 'desc');
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
            }
        }

        $perPage = $this->getPerPage($request, 12);
        $packages = $query->paginate($perPage)->appends($request->query());

        // Get cart items to check which packages are already in cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();
        $cartPackageIds = array_keys($cartItems);

        return view('packages.index', compact('packages', 'cartPackageIds', 'perPage'));
    }

    public function show(Package $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        // Cache individual package for 15 minutes
        $package = Cache::remember("package_{$package->id}", 900, function() use ($package) {
            return Package::find($package->id);
        });

        // Get cart items to check if this package is already in cart
        $cartService = app(CartService::class);
        $cartItems = $cartService->getItems();
        $isInCart = array_key_exists($package->id, $cartItems);

        return view('packages.show', compact('package', 'isInCart'));
    }
}