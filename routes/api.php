<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\PropertySearchController;
use App\Http\Controllers\Api\AccountProfileController;
use App\Http\Controllers\Api\AccountPaymentMethodController;
use App\Http\Controllers\Api\TravelerController;
use App\Http\Controllers\Api\TripRequestController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\RoomSelectionController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\RewardsController;
use App\Http\Controllers\Api\DealsController;
use App\Http\Controllers\Api\TravelDestinationController;
use App\Http\Controllers\Api\AccommodationController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HotelbedsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TailoredHotelController;
use App\Http\Controllers\Api\PromoEngineSettingsController;
use App\Http\Controllers\Api\PromoEngineMetricsController;
use App\Http\Controllers\Api\PromoEngineTrackingController;
use App\Http\Controllers\Api\PromoEngineOffersController;

use App\Http\Controllers\Api\HotelbedsDebugController;



Route::post('auth/login', [AuthController::class, 'login']);

// add register route matching spec
Route::post('auth/register', [AuthController::class, 'register']);

// (Google OAuth callback will usually be in web.php, not api.php)
Route::post('/auth/google/token', [AuthController::class, 'loginWithGoogleToken']);
Route::post('/auth/facebook/token', [AuthController::class, 'loginWithFacebookToken']);

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');

Route::get('/tailored-hotels', [TailoredHotelController::class, 'index']);


// ------- PUBLIC SEARCH ENDPOINTS -------

// 2.1 Generic search
Route::post('search', [SearchController::class, 'search']);

Route::get('/countries', [SearchController::class, 'countries']);
Route::get('/destinations', [SearchController::class, 'destinations']);

// -------- ROOM SELECTION (partially overlaps your RoomController) --------
Route::get('hotels/{hotel}/rooms', [RoomSelectionController::class, 'roomTypes']);
Route::post('hotels/{hotel}/rooms/price', [RoomSelectionController::class, 'priceQuote']);
Route::post('hotels/{hotel}/rooms/check-availability', [RoomSelectionController::class, 'checkAvailability']);
Route::post('hotels/{hotel}/rooms/selection', [RoomSelectionController::class, 'createSelection']);
Route::get('hotels/{hotel}/room-categories', [RoomSelectionController::class, 'categories']);
Route::get('hotels/amenities', [RoomSelectionController::class, 'amenities']); // optional
Route::post('hotels/{hotel}/media', [HotelController::class, 'uploadMedia']);


// 2.2 properties search (filterable list)
Route::get('properties/search', [PropertySearchController::class, 'index']);

Route::get('hotelbeds/hotels', [HotelController::class, 'index']);
// ------- AUTHENTICATED API -------

Route::get('/hotelbeds/content/{hotelCode}', [HotelController::class, 'getContent']);


// -------- RESERVATIONS --------
// 1) Checkout (Stripe intent) – MUST be before apiResource
Route::post('reservations/checkout', [ReservationController::class, 'checkout']);

// 2) Preview endpoint
Route::post('reservations/preview', [ReservationController::class, 'preview']);

// 3) RESTful CRUD routes
Route::apiResource('reservations', ReservationController::class)->except(['index']);

// Hotelbeds CheckRate alias (protected)
Route::post('reservations/checkrate', [ReservationController::class, 'hotelbedsCheckRate']);

// Voucher endpoint (protected)
Route::get('reservations/{reservation}/voucher', [ReservationController::class, 'voucher']);
Route::get('favorites', [FavoriteController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {

    Route::get('auth/me', [AuthController::class, 'me']);

    // -------- ACCOUNT (profile / traveler / trip lookup) --------
    Route::prefix('account')->group(function () {
        Route::get('profile', [AccountProfileController::class, 'show']);
        Route::put('profile', [AccountProfileController::class, 'update']);
        Route::put('change-password', [AccountProfileController::class, 'changePassword']);

        Route::get('payment-methods', [AccountPaymentMethodController::class, 'index']);
        Route::post('payment-methods', [AccountPaymentMethodController::class, 'store']);
        Route::delete('payment-methods/{id}', [AccountPaymentMethodController::class, 'destroy']);
        Route::put('payment-methods/{id}/default', [AccountPaymentMethodController::class, 'setDefault']);

        Route::get('travelers', [TravelerController::class, 'index']);
        Route::post('travelers', [TravelerController::class, 'store']);
        Route::put('travelers/{id}', [TravelerController::class, 'update']);
        Route::delete('travelers/{id}', [TravelerController::class, 'destroy']);
    });

    // 4.4 Can't find your trip
    Route::post('trips/request', [TripRequestController::class, 'store']);

    // -------- MESSAGES / SUPPORT --------
    Route::get('messages/conversations', [MessageController::class, 'conversations']);
    Route::get('messages/conversations/{id}', [MessageController::class, 'show']);
    Route::post('messages/conversations/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::post('messages/conversations', [MessageController::class, 'startConversation']);

    // 3.4 contact support
    Route::post('support/contact', [SupportController::class, 'contact']);

    // -------- FAVORITES --------
    
    Route::post('favorites', [FavoriteController::class, 'store']);
    Route::delete('favorites/{favorite}', [FavoriteController::class, 'destroy']);
    Route::get('favorites/check', [FavoriteController::class, 'check']); // optional



    // -------- PAYMENT METHODS (global alias, same as account) --------
    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods', [PaymentMethodController::class, 'store']);
    Route::patch('payment-methods/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
    Route::get('properties/{property}/payment-options', [PaymentMethodController::class, 'paymentOptions']);

    // -------- REWARDS / DEALS / DESTINATIONS / ACCOMMODATIONS --------
    Route::get('rewards', [RewardsController::class, 'index']);
    Route::get('hotels-deals', [DealsController::class, 'index']); // avoid clashing with your existing /hotels
    Route::get('travel-destinations', [TravelDestinationController::class, 'index']);
    Route::get('accommodations', [AccommodationController::class, 'index']);
    Route::get('reservations',[ReservationController::class, 'index']);
});


Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('inventory/hotels', [InventoryController::class, 'index']);
    Route::get('inventory/supplier-search', [InventoryController::class, 'supplierSearch']);
    Route::post('inventory/import-single', [InventoryController::class, 'importSingle']);
    Route::post('inventory/pin', [InventoryController::class, 'pin']);
    Route::delete('inventory/pin/{pinned}', [InventoryController::class, 'unpin']);
    Route::get('inventory/pinned', [InventoryController::class, 'pinnedList']);
    Route::get('inventory/content-health', [InventoryController::class, 'contentHealth']);

    Route::prefix('promo-engine')->group(function () {
        Route::get('settings', [PromoEngineSettingsController::class, 'show']);
        Route::put('settings', [PromoEngineSettingsController::class, 'update']);
        Route::get('metrics', [PromoEngineMetricsController::class, 'index']);
    });
});

Route::prefix('promo-engine')->group(function () {
    Route::get('ongoing-deals', [PromoEngineOffersController::class, 'index']);
    Route::post('impression', [PromoEngineTrackingController::class, 'impression']);
    Route::post('click', [PromoEngineTrackingController::class, 'click']);
});
