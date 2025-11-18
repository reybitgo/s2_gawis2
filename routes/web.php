<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminMlmSettingsController;
use App\Http\Controllers\Admin\AdminUnilevelSettingsController;
use App\Http\Controllers\Member\WalletController;
use App\Http\Controllers\Member\UserActivityController;
use App\Http\Controllers\Member\GenealogyController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DatabaseResetController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', [FrontendController::class, 'index']);

Route::get('/about', function () {
    $admin = User::role('admin')->first();
    return view('frontend.about', compact('admin'));
})->name('frontend.about');

Route::get('/contact', function () {
    return view('frontend.contact');
})->name('frontend.contact');

Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/opportunity', function () {
    return view('frontend.opportunity');
})->name('frontend.opportunity');

Route::get('/our-products', [FrontendController::class, 'products'])->name('frontend.our-products');



Route::get('/test-login', function () {
    return view('test-login');
});

Route::middleware(['auth', 'enforce.2fa'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Member Registration Route (for logged-in users to register others)
    Route::get('/register-member', [\App\Http\Controllers\MemberRegistrationController::class, 'show'])->name('member.register.show');
    Route::post('/register-member', [\App\Http\Controllers\MemberRegistrationController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('member.register.process');

    // Package Routes (Public)
    Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/{package}', [PackageController::class, 'show'])->name('packages.show');

    // Product Routes (Public - Unilevel System)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('index');
        Route::get('/{product:slug}', [\App\Http\Controllers\ProductController::class, 'show'])->name('show');
        Route::post('/{product:id}/add-to-cart', [\App\Http\Controllers\ProductController::class, 'addToCart'])
            ->middleware('throttle:30,1')
            ->name('add-to-cart');
    });

    // Cart Routes (with rate limiting for cart mutations)
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add/{packageId}', [CartController::class, 'add'])
            ->middleware('throttle:30,1')
            ->name('add');
        Route::patch('/update/{itemId}', [CartController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('update');
        Route::delete('/remove/{itemId}', [CartController::class, 'remove'])
            ->middleware('throttle:30,1')
            ->name('remove');
        Route::delete('/clear', [CartController::class, 'clear'])
            ->middleware('throttle:10,1')
            ->name('clear');
        Route::get('/count', [CartController::class, 'getCount'])->name('count');
        Route::get('/summary', [CartController::class, 'getSummary'])->name('summary');
    });

    // Checkout Routes (with rate limiting for security)
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])
            ->middleware('throttle:10,1')
            ->name('process');
        Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::get('/order/{order}', [CheckoutController::class, 'orderDetails'])->name('order-details');
        Route::post('/order/{order}/cancel', [CheckoutController::class, 'cancelOrder'])
            ->middleware('throttle:10,1')
            ->name('cancel-order');
        Route::get('/summary', [CheckoutController::class, 'getSummary'])->name('summary');
    });

    // Order History Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderHistoryController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderHistoryController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderHistoryController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reorder', [OrderHistoryController::class, 'reorder'])->name('reorder');
        Route::get('/{order}/invoice', [OrderHistoryController::class, 'invoice'])->name('invoice');
        Route::get('/ajax/list', [OrderHistoryController::class, 'ajax'])->name('ajax');
    });

    // Return Request Routes (Customer Side)
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::post('/orders/{order}', [\App\Http\Controllers\ReturnRequestController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('store');
        Route::post('/{returnRequest}/tracking', [\App\Http\Controllers\ReturnRequestController::class, 'updateTracking'])
            ->middleware('throttle:10,1')
            ->name('update-tracking');
    });

    // Referral Routes (MLM System)
    Route::prefix('referral')->name('referral.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
    });

    // User Activity Logs Routes (Member's own activity tracking)
    Route::prefix('my-activities')->name('activities.')->group(function () {
        Route::get('/', [UserActivityController::class, 'index'])->name('index');
        Route::post('/export', [UserActivityController::class, 'export'])->name('export');
    });

    // Genealogy Routes
    Route::get('/member/unilevel/genealogy', [GenealogyController::class, 'showUnilevel'])->name('member.unilevel.genealogy');
    Route::get('/member/mlm/genealogy', [GenealogyController::class, 'showMlm'])->name('member.mlm.genealogy');
});

// Admin Routes
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/wallet-management', [AdminController::class, 'walletManagement'])
        ->middleware('ewallet.security:wallet_management')
        ->name('wallet.management');
    Route::get('/transaction-approval', [AdminController::class, 'transactionApproval'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transaction.approval');
    Route::get('/system-settings', [AdminController::class, 'systemSettings'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    Route::post('/users/{user}/activate', [AdminController::class, 'activateUser'])->name('users.activate');

    // Transaction Approval Routes
    Route::post('/transactions/{id}/approve', [AdminController::class, 'approveTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [AdminController::class, 'rejectTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.reject');
    Route::post('/transactions/{id}/block', [AdminController::class, 'blockTransaction'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.block');
    Route::post('/transactions/bulk-approval', [AdminController::class, 'bulkApproval'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.bulk');
    Route::post('/transactions/export-report', [AdminController::class, 'exportTransactionReport'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.export');
    Route::get('/transaction-stats', [AdminController::class, 'getTransactionStats'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transaction.stats');
    Route::get('/transactions/{id}/details', [AdminController::class, 'getTransactionDetails'])
        ->middleware('ewallet.security:transaction_approval')
        ->name('transactions.details');

    // Logs Routes
    Route::get('/logs', [AdminController::class, 'viewLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs');
    Route::post('/logs/export', [AdminController::class, 'exportLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs.export');
    Route::post('/logs/clear', [AdminController::class, 'clearOldLogs'])
        ->middleware('ewallet.security:system_settings')
        ->name('logs.clear');

    // Reports Routes
    Route::get('/reports', [AdminController::class, 'reports'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports');
    Route::post('/reports/generate', [AdminController::class, 'generateReport'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports.generate');
    Route::get('/reports/download/{reportId}', [AdminController::class, 'downloadReport'])
        ->middleware('ewallet.security:system_settings')
        ->name('reports.download');

    // System Settings Update Route
    Route::post('/system-settings', [AdminController::class, 'updateSystemSettings'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings.update');
    Route::post('/system-settings/test-notification', [AdminController::class, 'testNotification'])
        ->middleware('ewallet.security:system_settings')
        ->name('system.settings.test-notification');

    // Admin Package Management Routes
    Route::resource('packages', AdminPackageController::class);
    Route::post('/packages/{package}/toggle-status', [AdminPackageController::class, 'toggleStatus'])->name('packages.toggle-status');

    // MLM Settings Routes
    Route::get('/packages/{package}/mlm-settings', [AdminMlmSettingsController::class, 'edit'])->name('packages.mlm.edit');
    Route::put('/packages/{package}/mlm-settings', [AdminMlmSettingsController::class, 'update'])->name('packages.mlm.update');

    // Admin Product Management Routes (Unilevel System)
    Route::resource('products', AdminProductController::class);
    Route::post('/products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');

    // Unilevel Settings Routes
    Route::get('/products/{product}/unilevel-settings', [AdminUnilevelSettingsController::class, 'edit'])->name('products.unilevel-settings.edit');
    Route::put('/products/{product}/unilevel-settings', [AdminUnilevelSettingsController::class, 'update'])->name('products.unilevel-settings.update');
    Route::get('/products/{product}/unilevel-settings/preview', [AdminUnilevelSettingsController::class, 'preview'])->name('products.unilevel-settings.preview');
    Route::post('/products/unilevel-settings/apply-defaults', [AdminUnilevelSettingsController::class, 'applyDefaults'])->name('products.unilevel-settings.apply-defaults');

    // Admin Settings Routes
    Route::get('/application-settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/application-settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Admin Order Management Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
        Route::post('/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
        Route::post('/{order}/add-notes', [AdminOrderController::class, 'addNotes'])->name('add-notes');
        Route::post('/{order}/update-tracking', [AdminOrderController::class, 'updateTracking'])->name('update-tracking');
        Route::post('/{order}/update-pickup', [AdminOrderController::class, 'updatePickup'])->name('update-pickup');
        Route::post('/bulk-update-status', [AdminOrderController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
        Route::get('/analytics', [AdminOrderController::class, 'analytics'])->name('analytics');
        Route::get('/updates', [AdminOrderController::class, 'getUpdates'])->name('updates');
        Route::post('/status-history/{statusHistory}/update-notes', [AdminOrderController::class, 'updateTimelineNotes'])->name('status-history.update-notes');
    });

    // Admin Return Request Management Routes
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminReturnController::class, 'index'])->name('index');
        Route::get('/{returnRequest}', [\App\Http\Controllers\Admin\AdminReturnController::class, 'show'])->name('show');
        Route::post('/{returnRequest}/approve', [\App\Http\Controllers\Admin\AdminReturnController::class, 'approve'])
            ->middleware('throttle:10,1')
            ->name('approve');
        Route::post('/{returnRequest}/reject', [\App\Http\Controllers\Admin\AdminReturnController::class, 'reject'])
            ->middleware('throttle:10,1')
            ->name('reject');
        Route::post('/{returnRequest}/confirm-received', [\App\Http\Controllers\Admin\AdminReturnController::class, 'confirmReceived'])
            ->middleware('throttle:10,1')
            ->name('confirm-received');
        Route::get('/pending/count', [\App\Http\Controllers\Admin\AdminReturnController::class, 'pendingCount'])->name('pending-count');
    });

    // Monthly Quota Management Routes (Phase 4: Unilevel Quota System)
    Route::prefix('monthly-quota')->name('monthly-quota.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MonthlyQuotaController::class, 'index'])->name('index');
        Route::get('/packages', [\App\Http\Controllers\Admin\MonthlyQuotaController::class, 'packages'])->name('packages');
        Route::post('/packages/{package}/update-quota', [\App\Http\Controllers\Admin\MonthlyQuotaController::class, 'updatePackageQuota'])
            ->middleware('throttle:30,1')
            ->name('packages.update-quota');
        Route::get('/reports', [\App\Http\Controllers\Admin\MonthlyQuotaController::class, 'reports'])->name('reports');
        Route::get('/reports/user/{user}', [\App\Http\Controllers\Admin\MonthlyQuotaController::class, 'userReport'])->name('reports.user');
    });
});

// Database Reset Routes (Admin Only)
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa', 'role:admin'])->group(function () {
    Route::get('/reset-status', [DatabaseResetController::class, 'status'])->name('database.reset.status');
});

Route::middleware(['auth', 'conditional.verified', 'enforce.2fa', 'role:admin'])->group(function () {
    Route::get('/reset', [DatabaseResetController::class, 'reset'])->name('database.reset');
});

// Member/User Wallet Routes
Route::middleware(['auth', 'conditional.verified', 'enforce.2fa'])->prefix('wallet')->name('wallet.')->group(function () {
    Route::get('/deposit', [WalletController::class, 'deposit'])
        ->middleware('ewallet.security:deposit_funds')
        ->name('deposit');
    Route::post('/deposit', [WalletController::class, 'processDeposit'])
        ->middleware('ewallet.security:deposit_funds')
        ->name('deposit.process');

    Route::get('/transfer', [WalletController::class, 'transfer'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('transfer');
    Route::post('/transfer', [WalletController::class, 'processTransfer'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('transfer.process');

    Route::get('/withdraw', [WalletController::class, 'withdraw'])
        ->middleware('ewallet.security:withdraw_funds')
        ->name('withdraw');
    Route::post('/withdraw', [WalletController::class, 'processWithdraw'])
        ->middleware('ewallet.security:withdraw_funds')
        ->name('withdraw.process');

    Route::get('/convert', [WalletController::class, 'convert'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('convert');
    Route::post('/convert', [WalletController::class, 'processConvert'])
        ->middleware('ewallet.security:transfer_funds')
        ->name('convert.process');

    Route::get('/transactions', [WalletController::class, 'transactions'])
        ->middleware('ewallet.security:view_transactions')
        ->name('transactions');
});

Route::middleware(['guest'])->group(function () {
    Route::redirect('/home', '/dashboard');
});

// Symlink creation route (for shared hosting without SSH access) - REMOVE AFTER USE!
Route::get('/symlink', [App\Http\Controllers\SymlinkController::class, 'createStorageLink']);

// Debug route for session configuration - Remove in production
Route::middleware(['auth'])->get('/debug/session-config', function () {
    return response()->json([
        'session_lifetime' => config('session.lifetime'),
        'session_expire_on_close' => config('session.expire_on_close'),
        'session_driver' => config('session.driver'),
        'database_session_timeout_enabled' => \App\Models\SystemSetting::get('session_timeout', false),
        'database_session_timeout_minutes' => \App\Models\SystemSetting::get('session_timeout_minutes', 15),
    ]);
});

// Temporary routes for testing error pages - Remove in production
Route::prefix('test')->name('test.')->group(function () {
    Route::get('/404', function () {
        abort(404);
    })->name('404');

    Route::get('/500', function () {
        abort(500);
    })->name('500');

    Route::get('/419', function () {
        abort(419);
    })->name('419');

    Route::get('/403', function () {
        abort(403);
    })->name('403');

    Route::get('/429', function () {
        abort(429);
    })->name('429');

    Route::get('/errors', function () {
        return view('test-errors');
    })->name('errors');
});
