<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display product catalog
     */
    public function index(Request $request)
    {
        $query = Product::active()->available();

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
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

        // Check which products are in cart
        $inCart = [];
        foreach ($products as $product) {
            $inCart[$product->id] = $this->cartService->hasProduct($product->id);
        }

        return view('products.index', compact('products', 'categories', 'inCart', 'perPage'));
    }

    /**
     * Display individual product details
     */
    public function show(Product $product)
    {
        // Check if product is available
        if (!$product->is_active) {
            abort(404, 'Product not found or unavailable');
        }

        // Load unilevel settings
        $product->load('unilevelSettings');

        // Check if product is in cart
        $inCart = $this->cartService->hasProduct($product->id);

        // Get related products (same category)
        $relatedProducts = Product::active()
            ->available()
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        return view('products.show', compact('product', 'inCart', 'relatedProducts'));
    }

    /**
     * Add product to cart via AJAX
     */
    public function addToCart(Request $request, Product $product)
    {
        if (!$product->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'This product is no longer available.'
            ], 400);
        }

        $quantity = $request->input('quantity', 1);

        if ($this->cartService->addProduct($product, $quantity)) {
            $cartSummary = $this->cartService->getSummary();

            return response()->json([
                'success' => true,
                'message' => "'{$product->name}' has been added to your cart.",
                'cart_count' => $cartSummary['item_count'],
                'in_cart' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to add product to cart. Please check availability.'
        ], 400);
    }
}
