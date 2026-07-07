<?php

use App\Http\Controllers\CampaignMenuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\APIGW\ApiGatewayController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\SsoController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-session-guest')->group(function (): void {
    Route::get('/', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
    Route::get('/sso/complete', [SsoController::class, 'complete'])->name('sso.complete');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('web')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/campaign/wa-business/campaign-template', [CampaignMenuController::class, 'listCampaignTemplates'])->name('campaign-template.index');
    Route::post('/campaign/wa-business/campaign-template', [CampaignMenuController::class, 'storeCampaignTemplate'])->name('campaign-template.store');
    Route::get('/campaign/wa-business/campaign-template/api-list', [ApiGatewayController::class, 'wabaTemplateList'])->name('campaign-template.api-list');
    Route::get('/campaign/wa-business/display-name/api-list', [ApiGatewayController::class, 'wabaDisplayNameList'])->name('campaign-template.display-name-list');
    Route::get('/campaign/wa-business/campaign-template/api-show', [CampaignMenuController::class, 'showCampaignTemplateFromApi'])->name('campaign-template.api-show');
    Route::get('/campaign/wa-business/campaign-template/{template}', [CampaignMenuController::class, 'showCampaignTemplate'])->name('campaign-template.show');

    // SMS campaign create (via myads_api proxy)
    Route::get('/campaign/price-view', [ApiGatewayController::class, 'priceView'])->name('campaign.price-view');
    Route::get('/campaign/terms-and-conditions', [CampaignMenuController::class, 'termsAndConditions'])->name('campaign.terms');
    Route::get('/campaign/{channel}/report', [ApiGatewayController::class, 'campaignReport'])->name('campaign.report');
    Route::get('/campaign/{channel}/targeted/profile-dropdown-list', [ApiGatewayController::class, 'profileDropdownList'])->name('campaign.targeted.profile-dropdown-list');
    Route::post('/campaign/payment/initiate', [ApiGatewayController::class, 'paymentInitiate'])->name('campaign.payment.initiate');
    Route::get('/campaign/payment/qris-download', [ApiGatewayController::class, 'paymentQrisDownload'])->name('campaign.payment.qris-download');
    Route::get('/campaign/payment/{transactionId}', [ApiGatewayController::class, 'paymentStatus'])->name('campaign.payment.status');
    Route::post('/campaign/wa-business/targeted/send', [ApiGatewayController::class, 'wabaTargetedAdd'])->name('campaign.waba-targeted.send');
    Route::post('/campaign/wa-business/location-based-area/send', [ApiGatewayController::class, 'wabaLbaAdd'])->name('campaign.waba-lba.send');
    Route::post('/campaign/{channel}/targeted/send', [ApiGatewayController::class, 'targetedAdd'])->name('campaign.targeted.send');
    Route::post('/campaign/{channel}/location-based-area/send', [ApiGatewayController::class, 'lbaAdd'])->name('campaign.lba.send');
    Route::post('/campaign/track-event', [ApiGatewayController::class, 'trackEvent'])->name('campaign.track-event');

    Route::get('/campaign/{channel}/{menu}', [CampaignMenuController::class, 'show'])->name('campaign.menu');
    Route::get('/campaign/{campaignId}/invoice/pdf', [App\Http\Controllers\InvoiceController::class, 'downloadPdfByCampaign'])->name('campaign.invoice.pdf');
    Route::get('/invoice/{invoiceId}', [App\Http\Controllers\InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{invoiceId}/pdf', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoice.pdf');
    Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->middleware('admin-session-auth')->name('admin.dashboard');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
