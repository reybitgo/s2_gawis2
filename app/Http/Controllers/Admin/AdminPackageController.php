<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminPackageController extends Controller
{
    use \App\Http\Traits\HasPaginationLimit;

    public function index(Request $request)
    {
        $perPage = $this->getPerPage($request, 10);

        $packages = Package::withTrashed()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)->appends($request->query());

        $breadcrumbs = [
            ['title' => 'Management'],
            ['title' => 'Package Management'],
        ];

        return view('admin.packages.index', compact('packages', 'perPage', 'breadcrumbs'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug',
            'price' => 'required|numeric|min:0.01',
            'points_awarded' => 'required|integer|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_mlm_package' => 'boolean',
            'is_rankable' => 'boolean',
            'sort_order' => 'integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255'
        ]);

        if (!$validated['slug']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('packages', 'public');
        }

        // Build meta_data from user-friendly inputs
        $metaData = [];

        if ($request->has('features') && is_array($request->features)) {
            $features = array_filter($request->features, function($feature) {
                return !empty(trim($feature));
            });
            if (!empty($features)) {
                $metaData['features'] = array_values($features);
            }
        }

        if ($request->filled('duration')) {
            $metaData['duration'] = $request->duration;
        }

        if ($request->filled('category')) {
            $metaData['category'] = $request->category;
        }

        if (!empty($metaData)) {
            $validated['meta_data'] = $metaData;
        }

        // Remove the temporary fields from validated data
        unset($validated['features'], $validated['duration'], $validated['category']);

        // Handle checkbox values (unchecked checkboxes are not submitted)
        $validated['is_active'] = $request->has('is_active');
        $validated['is_mlm_package'] = $request->has('is_mlm_package');
        $validated['is_rankable'] = $request->has('is_rankable');

        Package::create($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function show(Package $package)
    {
        return view('admin.packages.show', compact('package'));
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:packages,slug,' . $package->id,
            'price' => 'required|numeric|min:0.01',
            'points_awarded' => 'required|integer|min:0',
            'quantity_available' => 'nullable|integer|min:0',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_mlm_package' => 'boolean',
            'is_rankable' => 'boolean',
            'sort_order' => 'integer|min:0',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255'
        ]);

        if (!$validated['slug']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($package->image_path) {
                Storage::disk('public')->delete($package->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('packages', 'public');
        }

        // Build meta_data from user-friendly inputs
        $metaData = [];

        if ($request->has('features') && is_array($request->features)) {
            $features = array_filter($request->features, function($feature) {
                return !empty(trim($feature));
            });
            if (!empty($features)) {
                $metaData['features'] = array_values($features);
            }
        }

        if ($request->filled('duration')) {
            $metaData['duration'] = $request->duration;
        }

        if ($request->filled('category')) {
            $metaData['category'] = $request->category;
        }

        if (!empty($metaData)) {
            $validated['meta_data'] = $metaData;
        } else {
            $validated['meta_data'] = null;
        }

        // Remove the temporary fields from validated data
        unset($validated['features'], $validated['duration'], $validated['category']);

        // Handle checkbox values (unchecked checkboxes are not submitted)
        $validated['is_active'] = $request->has('is_active');

        // Prevent changing MLM status if package has been purchased
        if (!$package->canBeDeleted() && $package->is_mlm_package) {
            // Keep the original MLM status - cannot be changed
            $validated['is_mlm_package'] = $package->is_mlm_package;
        } else {
            $validated['is_mlm_package'] = $request->has('is_mlm_package');
        }

        // Prevent disabling rank status if users have this rank
        $hasUsersWithRank = $package->is_rankable && \App\Models\User::where('rank_package_id', $package->id)->exists();
        if ($hasUsersWithRank) {
            // Keep the original rankable status - cannot be disabled
            $validated['is_rankable'] = $package->is_rankable;
        } else {
            $validated['is_rankable'] = $request->has('is_rankable');
        }

        $package->update($validated);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        if (!$package->canBeDeleted()) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'Cannot delete package that has been purchased by users.');
        }

        if ($package->image_path) {
            Storage::disk('public')->delete($package->image_path);
        }

        $package->forceDelete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package deleted successfully.');
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.packages.index')
            ->with('success', "Package {$status} successfully.");
    }
}
