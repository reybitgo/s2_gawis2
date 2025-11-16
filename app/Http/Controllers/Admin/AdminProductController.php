<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    public function index(Request $request)
    {
        $perPage = $this->getPerPage($request, 10);

        $query = Product::withTrashed();

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        // Get unique categories for filter
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Product Management'],
        ];

        return view('admin.products.index', compact('products', 'perPage', 'categories', 'breadcrumbs'));
    }

    public function create()
    {
        // Get existing categories for dropdown
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'price' => 'required|numeric|min:0.01',
            'points_awarded' => 'required|numeric|min:0|max:9999.99',
            'quantity_available' => 'nullable|integer|min:0',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:255',
            'weight_grams' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Auto-generate SKU if not provided (handled by model boot method)

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        // Handle checkbox values (unchecked checkboxes are not submitted)
        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully. Configure unilevel bonus settings next!');
    }

    public function show(Product $product)
    {
        $product->load('unilevelSettings');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        // Get existing categories for dropdown
        $categories = Product::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'price' => 'required|numeric|min:0.01',
            'points_awarded' => 'required|numeric|min:0|max:9999.99',
            'quantity_available' => 'nullable|integer|min:0',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:255',
            'weight_grams' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        // Handle checkbox values
        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (!$product->canBeDeleted()) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Cannot delete product that has been purchased by customers.');
        }

        // Delete product image if exists
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        // Force delete (since we use soft deletes)
        $product->forceDelete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.products.index')
            ->with('success', "Product {$status} successfully.");
    }
}
