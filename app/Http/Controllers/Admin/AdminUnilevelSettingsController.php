<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UnilevelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUnilevelSettingsController extends Controller
{
    /**
     * Show the form for editing unilevel settings for a product
     */
    public function edit(Product $product)
    {
        // Load existing unilevel settings or create defaults
        $settings = $product->unilevelSettings()->orderBy('level')->get();

        // If no settings exist, create default structure
        if ($settings->isEmpty()) {
            $defaultStructure = UnilevelSetting::getDefaultStructure($product->price);

            foreach ($defaultStructure as $levelData) {
                UnilevelSetting::create([
                    'product_id' => $product->id,
                    'level' => $levelData['level'],
                    'bonus_amount' => $levelData['bonus_amount'],
                    'is_active' => true,
                ]);
            }

            // Reload settings
            $settings = $product->unilevelSettings()->orderBy('level')->get();
        }

        return view('admin.products.unilevel-settings', compact('product', 'settings'));
    }

    /**
     * Update unilevel settings for a product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'bonus_amounts' => 'required|array|size:5',
            'bonus_amounts.*' => 'required|numeric|min:0',
            'is_active' => 'nullable|array',
            'is_active.*' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Update each level's bonus amount and active status
            for ($level = 1; $level <= 5; $level++) {
                $setting = UnilevelSetting::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'level' => $level,
                    ]
                );

                $setting->bonus_amount = $validated['bonus_amounts'][$level];
                $setting->is_active = isset($validated['is_active'][$level]) && $validated['is_active'][$level];
                $setting->save();
            }

            // Update the cached total unilevel bonus in the product
            $product->updateTotalUnilevelBonus();

            DB::commit();

            return redirect()->route('admin.products.unilevel-settings.edit', $product)
                ->with('success', 'Unilevel bonus settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update unilevel settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Apply default unilevel structure to multiple products
     */
    public function applyDefaults(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        DB::beginTransaction();
        try {
            $productsUpdated = 0;

            foreach ($validated['product_ids'] as $productId) {
                $product = Product::findOrFail($productId);
                $defaultStructure = UnilevelSetting::getDefaultStructure($product->price);

                foreach ($defaultStructure as $levelData) {
                    UnilevelSetting::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'level' => $levelData['level'],
                        ],
                        [
                            'bonus_amount' => $levelData['bonus_amount'],
                            'is_active' => true,
                        ]
                    );
                }

                // Update cached total
                $product->updateTotalUnilevelBonus();
                $productsUpdated++;
            }

            DB::commit();

            return redirect()->back()
                ->with('success', "Default unilevel structure applied to {$productsUpdated} product(s).");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to apply defaults: ' . $e->getMessage());
        }
    }

    /**
     * Preview total bonus calculation
     */
    public function preview(Request $request, Product $product)
    {
        $bonusAmounts = $request->input('bonus_amounts', []);
        $totalBonus = array_sum($bonusAmounts);

        $percentage = $product->price > 0 ? ($totalBonus / $product->price) * 100 : 0;

        return response()->json([
            'total_bonus' => number_format($totalBonus, 2),
            'product_price' => number_format($product->price, 2),
            'percentage' => number_format($percentage, 2),
            'formatted_total' => currency($totalBonus),
        ]);
    }
}
