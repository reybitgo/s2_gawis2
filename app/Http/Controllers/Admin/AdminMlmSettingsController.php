<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\MlmSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMlmSettingsController extends Controller
{
    /**
     * Show the MLM settings edit form for a package
     */
    public function edit(Package $package)
    {
        // Only MLM packages can have MLM settings
        if (!$package->is_mlm_package) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'This package does not support MLM settings');
        }

        // Get MLM settings for this package, keyed by level
        $mlmSettings = $package->mlmSettings()
                               ->orderBy('level')
                               ->get()
                               ->keyBy('level');

        // Calculate totals for active levels only
        $totalCommission = $mlmSettings
            ->where('is_active', true)
            ->sum('commission_amount');
        $companyProfit = $package->price - $totalCommission;
        $profitMargin = $package->price > 0 ? ($companyProfit / $package->price) * 100 : 0;

        return view('admin.packages.mlm-settings', compact(
            'package',
            'mlmSettings',
            'totalCommission',
            'companyProfit',
            'profitMargin'
        ));
    }

    /**
     * Update the MLM settings for a package
     */
    public function update(Request $request, Package $package)
    {
        // Validate input
        $request->validate([
            'settings' => 'required|array',
            'settings.*.level' => 'required|integer|between:1,5',
            'settings.*.commission_amount' => 'required|numeric|min:0',
            'settings.*.is_active' => 'nullable|boolean'
        ]);

        // Calculate total commission for active levels only
        $totalCommission = collect($request->settings)
            ->filter(function ($setting) {
                return isset($setting['is_active']) && $setting['is_active'];
            })
            ->sum('commission_amount');

        $maxCommission = $package->price * 0.40;

        if ($totalCommission > $maxCommission) {
            return back()->withErrors([
                'total_commission' => sprintf(
                    'Total MLM commission (₱%s) exceeds 40%% of package price (₱%s)',
                    number_format($totalCommission, 2),
                    number_format($maxCommission, 2)
                )
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            foreach ($request->settings as $setting) {
                MlmSetting::updateOrCreate(
                    [
                        'package_id' => $package->id,
                        'level' => $setting['level']
                    ],
                    [
                        'commission_amount' => $setting['commission_amount'],
                        'is_active' => isset($setting['is_active']) && $setting['is_active']
                    ]
                );
            }

            // Update package metadata
            $metadata = $package->meta_data ?? [];
            $metadata['total_commission'] = $totalCommission;
            $metadata['company_profit'] = $package->price - $totalCommission;
            $metadata['profit_margin'] = number_format((($package->price - $totalCommission) / $package->price) * 100, 2) . '%';

            $package->update(['meta_data' => $metadata]);

            DB::commit();

            return back()->with('success', 'MLM settings updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()])->withInput();
        }
    }
}
