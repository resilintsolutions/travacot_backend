<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\HotelbedsController;
use App\Http\Controllers\Admin\InventoryHotelsController;
use App\Http\Controllers\Admin\InventoryPinnedController;
use App\Http\Controllers\Admin\InventoryContentHealthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Admin\KpiController;
use App\Http\Controllers\Admin\ApiStatusController;
use App\Http\Controllers\Admin\PpmController;
use App\Http\Controllers\Admin\MspController;
use App\Http\Controllers\Admin\MarginRulesController;
use App\Http\Controllers\Admin\TodayPerformanceController;
use App\Http\Controllers\Admin\AdminReservationController;
use App\Http\Controllers\Admin\HotelExclusionController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\PromoEngineController;
use App\Http\Controllers\Admin\SupportCaseController;

Route::get('/', function () {
    // return view('welcome');
    return redirect('login');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])
    ->name('google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('contacts', ContactController::class);
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('roles', AdminRoleController::class);
    Route::resource('permissions', AdminPermissionController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::apiResource('hotels', HotelController::class);
    Route::post('hotels/{hotel}/media', [InventoryHotelsController::class, 'uploadMedia'])
        ->name('hotels.media.store');
    Route::delete('hotels/{hotel}/media/{media}', [InventoryHotelsController::class, 'deleteMedia'])
        ->name('hotels.media.destroy');
    Route::post('hotels/{hotel}/media/{media}/feature', [InventoryHotelsController::class, 'setFeaturedMedia'])
        ->name('hotels.media.feature');

    Route::get('hotelbeds/hotels', [HotelbedsController::class, 'index']);
    Route::post('hotelbeds/import', [HotelbedsController::class, 'import']);

    Route::get('/inventory/hotels-list', [InventoryHotelsController::class, 'index'])
        ->name('inventory.hotels_list');
    Route::get('/inventory/pinned', [InventoryPinnedController::class, 'index'])
        ->name('inventory.pinned.index');
    Route::get('/inventory/content-health', [InventoryContentHealthController::class, 'index'])
        ->name('inventory.content_health.index');
    Route::get('hotels/{hotel}', [HotelController::class, 'show'])->name('hotels.show');
    Route::get('/kpis', [KpiController::class, 'index'])->name('kpis');
    Route::get('/admin/api-status-cards', [ApiStatusController::class, 'index'])
        ->name('api-status.index');

    Route::post('/admin/api-status-cards/{supplier}/toggle', [ApiStatusController::class, 'toggleStatus'])
        ->name('api-status.toggle');

    Route::get('/ppm', [PpmController::class, 'index'])
        ->name('ppm.index');

    // MSP
    Route::get('pricing/msp', [MspController::class, 'index'])->name('msp.index');
    Route::get('pricing/msp/create', [MspController::class, 'create'])->name('msp.create');
    Route::post('pricing/msp', [MspController::class, 'store'])->name('msp.store');

    // JSON endpoint used by the modal editor (must come before parameter routes)
    Route::get('pricing/msp/{msp}/json', [MspController::class, 'showJson'])->name('msp.json');

    Route::get('pricing/msp/{msp}/edit', [MspController::class, 'edit'])->name('msp.edit');
    Route::put('pricing/msp/{msp}', [MspController::class, 'update'])->name('msp.update');
    Route::delete('pricing/msp/{msp}', [MspController::class, 'destroy'])->name('msp.destroy');

    // Margin rules
    Route::prefix('pricing/margin-rules')->name('margin-rules.')->group(function () {

        Route::get('/', [MarginRulesController::class, 'index'])->name('index');

        // Global
        Route::post('/update-global', [MarginRulesController::class, 'updateGlobal'])->name('global.update');

        // Country
        Route::post('/country', [MarginRulesController::class, 'storeCountry'])->name('country.store');
        Route::put('/country/{rule}', [MarginRulesController::class, 'updateCountry'])->name('country.update');

        // City
        Route::post('/city', [MarginRulesController::class, 'storeCity'])->name('city.store');
        Route::put('/city/{rule}', [MarginRulesController::class, 'updateCity'])->name('city.update');

        // Rule Parameters A,B,C
        Route::post('/parameters/update', [MarginRulesController::class, 'updateParameters'])->name('parameters.update');

        // JSON for modal
        Route::get('/{rule}/json', [MarginRulesController::class, 'showJson'])->name('json');

        // Delete
        Route::delete('/{rule}', [MarginRulesController::class, 'destroy'])->name('destroy');
    });


    // Today Performance
    Route::get('/today-performance', [TodayPerformanceController::class, 'index'])
        ->name('today-performance.index');

    Route::prefix('reservations')->name('reservations.')->group(function () {

        Route::get('/', [AdminReservationController::class, 'index'])->name('index');
        Route::get('/failed', [AdminReservationController::class, 'failed'])->name('failed');
        Route::get('/{reservation}', [AdminReservationController::class, 'show'])->name('show');
        // ACTION BUTTON ROUTES
        Route::post('/{reservation}/retry', [AdminReservationController::class, 'retryBooking'])->name('retry');
        Route::post('/{reservation}/check-status', [AdminReservationController::class, 'checkStatus'])->name('checkStatus');
        Route::post('/{reservation}/cancel', [AdminReservationController::class, 'cancelBooking'])->name('cancel');
        Route::post('/{reservation}/rebook', [AdminReservationController::class, 'rebook'])->name('rebook');
    });

    Route::prefix('exclusions')->group(function () {
        Route::get('/exclusions', [HotelExclusionController::class, 'index']);
        Route::get('/rules', [HotelExclusionController::class, 'rules']);
        Route::put('/rules', [HotelExclusionController::class, 'updateRules']);

        Route::get('/hotels', [HotelExclusionController::class, 'hotels']);

        Route::post('/hotels/{hotel}/hide', [HotelExclusionController::class, 'hide']);
        Route::post('/hotels/{hotel}/show', [HotelExclusionController::class, 'show']);
        Route::post('/hotels/{hotel}/automatic', [HotelExclusionController::class, 'automatic']);
        Route::post('/hotels/bulk/hide', [HotelExclusionController::class, 'bulkHide']);
        Route::post('/hotels/bulk/show', [HotelExclusionController::class, 'bulkShow']);
        Route::post('/hotels/bulk/automatic', [HotelExclusionController::class, 'bulkAutomatic']);
        Route::get('/stats', [HotelExclusionController::class, 'stats']);
        Route::post('/recalculate', [HotelExclusionController::class, 'recalculate']);
    });

    Route::prefix('system-health')->name('system-health.')->group(function () {

    Route::get('/api-health', [SystemHealthController::class, 'index'])
        ->name('api-health.index');

    Route::get('/api-health/realtime', [SystemHealthController::class, 'realtime'])
        ->name('api-health.realtime');

    });

    Route::prefix('promo-engine')->name('promo-engine.')->group(function () {
        Route::get('/', [PromoEngineController::class, 'index'])->name('index');
        Route::put('/', [PromoEngineController::class, 'update'])->name('update');
    });

    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportCaseController::class, 'index'])->name('index');
        Route::get('/{supportCase}', [SupportCaseController::class, 'show'])->name('show');
        Route::post('/{supportCase}/messages', [SupportCaseController::class, 'sendMessage'])->name('messages.send');
        Route::post('/{supportCase}/decision', [SupportCaseController::class, 'updateDecision'])->name('decision');
    });
});

Route::get('test-media', function (\App\Services\MediaService $ms) {
    $media = $ms->importForHotel(1, 'https://images.pexels.com/photos/34666713/pexels-photo-34666713.jpeg');
    dd($media);
});


require __DIR__ . '/auth.php';

Route::prefix('marketplace')->name('marketplace.')->group(function () {
    Route::get('/search', [\App\Http\Controllers\Marketplace\MarketplaceSearchController::class, 'show'])
        ->name('search.show');
    Route::post('/search', [\App\Http\Controllers\Marketplace\MarketplaceSearchController::class, 'search'])
        ->name('search.perform');
    Route::get('/hotels/{hotelCode}', [\App\Http\Controllers\Marketplace\MarketplaceHotelController::class, 'show'])
        ->name('hotels.show');

    Route::get('/resales', [\App\Http\Controllers\Marketplace\MarketplaceResaleController::class, 'index'])
        ->name('resales.index');
    Route::get('/resales/{resale}', [\App\Http\Controllers\Marketplace\MarketplaceResaleController::class, 'show'])
        ->name('resales.show');

    Route::middleware('auth')->group(function () {
        Route::post('/checkout/start', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'start'])
            ->name('checkout.start');
        Route::get('/checkout', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'show'])
            ->name('checkout.show');
        Route::post('/checkout/recheck', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'recheck'])
            ->name('checkout.recheck');
        Route::post('/checkout/accept', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'acceptRate'])
            ->name('checkout.accept');
        Route::post('/checkout/create', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'createReservation'])
            ->name('checkout.create');
        Route::get('/checkout/processing/{reservation}', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'processing'])
            ->name('checkout.processing');
        Route::get('/confirmation/{reservation}', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'confirmation'])
            ->name('checkout.confirmation');
        Route::get('/reservations/{reservation}/status', [\App\Http\Controllers\Marketplace\MarketplaceCheckoutController::class, 'status'])
            ->name('reservations.status');

        Route::post('/resales/{resale}/buy', [\App\Http\Controllers\Marketplace\MarketplaceResaleController::class, 'buy'])
            ->name('resales.buy');

        Route::get('/verification', [\App\Http\Controllers\Marketplace\MarketplaceVerificationController::class, 'show'])
            ->name('verification.show');
        Route::post('/verification/fee-intent', [\App\Http\Controllers\Marketplace\MarketplaceVerificationController::class, 'feeIntent'])
            ->name('verification.fee');
        Route::post('/verification/start', [\App\Http\Controllers\Marketplace\MarketplaceVerificationController::class, 'start'])
            ->name('verification.start');
    });

    Route::middleware(['auth', 'identity_verified'])->group(function () {
        Route::post('/resales/{reservation}/list', [\App\Http\Controllers\Marketplace\MarketplaceResaleController::class, 'create'])
            ->name('resales.create');
    });
});
