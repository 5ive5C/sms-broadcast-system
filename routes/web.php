<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ClientSwitchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PricingTierController;
use App\Http\Controllers\Admin\QueueMonitorController;
use App\Http\Controllers\Admin\ReconciliationController;
use App\Http\Controllers\Admin\TopUpApprovalController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\QuickSendController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? '/home' : route('login'));
});

Route::middleware('auth')->group(function () {
    Route::get('/home', function () {
        return redirect(auth()->user()->isInternalAdmin() ? '/admin/dashboard' : '/dashboard');
    })->name('home');

    Route::resource('users', UserController::class)->except('show');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');

    // Client portal — screens that assume a tenant (wallet, campaigns,
    // ...). An internal super-admin has every permission string, so this
    // is gated on client-ness, not permissions.
    Route::middleware('client-portal')->group(function () {
        Route::get('settings/api-keys', [ApiKeyController::class, 'index'])->name('api-keys.index');
        Route::post('settings/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
        Route::patch('settings/api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke'])->name('api-keys.revoke');

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('quick-send', [QuickSendController::class, 'create'])->name('quick-send.create');
        Route::post('quick-send', [QuickSendController::class, 'store'])->name('quick-send.store');

        Route::get('campaigns/templates', [MessageTemplateController::class, 'index'])->name('templates.index');
        Route::get('campaigns/templates/create', [MessageTemplateController::class, 'create'])->name('templates.create');
        Route::post('campaigns/templates', [MessageTemplateController::class, 'store'])->name('templates.store');
        Route::get('campaigns/templates/{template}/edit', [MessageTemplateController::class, 'edit'])->name('templates.edit');
        Route::put('campaigns/templates/{template}', [MessageTemplateController::class, 'update'])->name('templates.update');

        Route::get('campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/new', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{campaign}/recipients', [CampaignController::class, 'recipients'])->name('campaigns.recipients');
        Route::post('campaigns/{campaign}/recipients', [CampaignController::class, 'storeRecipients'])->name('campaigns.recipients.store');
        Route::get('campaigns/{campaign}/merge', [CampaignController::class, 'merge'])->name('campaigns.merge');
        Route::post('campaigns/{campaign}/merge', [CampaignController::class, 'storeMerge'])->name('campaigns.merge.store');
        Route::get('campaigns/{campaign}/review', [CampaignController::class, 'review'])->name('campaigns.review');
        Route::post('campaigns/{campaign}/launch', [CampaignController::class, 'launch'])->name('campaigns.launch');
        Route::post('campaigns/{campaign}/save-draft', [CampaignController::class, 'saveDraft'])->name('campaigns.save-draft');
        Route::get('campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');

        Route::get('messages/failed', [MessageController::class, 'failed'])->name('messages.failed');
        Route::post('messages/resend', [MessageController::class, 'resend'])->name('messages.resend');
        Route::get('messages/export', [MessageController::class, 'export'])->name('messages.export');
        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');

        Route::get('reports/delivery', [ReportController::class, 'delivery'])->name('reports.delivery');
        Route::get('reports/delivery/export', [ReportController::class, 'deliveryExport'])->name('reports.delivery.export');
        Route::get('reports/usage', [ReportController::class, 'usage'])->name('reports.usage');

        Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::get('wallet/top-up', [WalletController::class, 'topUp'])->name('wallet.top-up');
        Route::post('wallet/top-up', [WalletController::class, 'requestTopUp'])->name('wallet.top-up.store');
        Route::get('wallet/invoices', [WalletController::class, 'invoices'])->name('wallet.invoices');
    });

    // Internal admin portal — SMS Broadcast staff only.
    Route::prefix('admin')->middleware('internal-admin')->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('view-as/exit', [ClientSwitchController::class, 'exit'])->name('admin.view-as.exit');
        Route::get('view-as/{client}', [ClientSwitchController::class, 'viewAs'])->name('admin.view-as');

        Route::resource('clients', ClientController::class)->except('show');

        Route::get('pricing', [PricingTierController::class, 'index'])->name('pricing-tiers.index');
        Route::post('pricing', [PricingTierController::class, 'store'])->name('pricing-tiers.store');
        Route::put('pricing/{pricingTier}', [PricingTierController::class, 'update'])->name('pricing-tiers.update');

        Route::get('top-ups', [TopUpApprovalController::class, 'index'])->name('top-ups.index');
        Route::patch('top-ups/{topUpRequest}/approve', [TopUpApprovalController::class, 'approve'])->name('top-ups.approve');
        Route::patch('top-ups/{topUpRequest}/reject', [TopUpApprovalController::class, 'reject'])->name('top-ups.reject');

        Route::get('queue', [QueueMonitorController::class, 'index'])->name('queue.index');

        Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
        Route::post('reconciliation/rerun', [ReconciliationController::class, 'rerun'])->name('reconciliation.rerun');

        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
    });
});
