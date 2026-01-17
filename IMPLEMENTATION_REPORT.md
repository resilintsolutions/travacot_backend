# Travacot Implementation Report

This document summarizes the features implemented in this repo and lists all new APIs/endpoints.

## Features Delivered

### 1) Marketplace Reservation Flow
- Marketplace hotel search, results, hotel detail, checkout, booking confirmation.
- Rate recheck before payment, retry-on-failure, stop on change unless user accepts.
- Marketplace resales (list, buy, cancel), with identity verification gating for resale.
- Identity verification via Stripe Identity, with middleware enforcement.

### 2) Promo Engine (Ongoing Deals)
- Global on/off switch.
- Eligibility gate and margin safety rules.
- Promo modes (light/normal/aggressive) with downgrade logic.
- Promo tracking (impression/click) + attribution logic.
- Admin settings and metrics UI.

### 3) Customer Support (Admin)
- Support case list and detail view with messaging.
- Decision console for payout outcomes and response tracking.
- Dummy data seeded for full UI testing.

### 4) Supporting Enhancements
- Profile fields on users + registration updates.
- Reservation model enhancements for resales and promo tracking.
- SQLite compatibility fixes for admin analytics.

## New Endpoints (CSV)

```
Category,Method,Path,Controller@Method,Purpose
Marketplace,GET,/marketplace/search,MarketplaceSearchController@show,Show search form
Marketplace,POST,/marketplace/search,MarketplaceSearchController@search,Run marketplace search
Marketplace,GET,/marketplace/hotels/{hotelCode},MarketplaceHotelController@show,Hotel details + rooms
Marketplace,POST,/marketplace/checkout/start,MarketplaceCheckoutController@start,Start checkout session
Marketplace,GET,/marketplace/checkout,MarketplaceCheckoutController@show,Show checkout page
Marketplace,POST,/marketplace/checkout/recheck,MarketplaceCheckoutController@recheck,Rate recheck before payment
Marketplace,POST,/marketplace/checkout/accept,MarketplaceCheckoutController@acceptRate,Accept updated rate
Marketplace,POST,/marketplace/checkout/create,MarketplaceCheckoutController@createReservation,Create reservation + payment intent
Marketplace,GET,/marketplace/checkout/processing/{reservation},MarketplaceCheckoutController@processing,Processing screen
Marketplace,GET,/marketplace/confirmation/{reservation},MarketplaceCheckoutController@confirmation,Confirmation page
Marketplace,GET,/marketplace/reservations/{reservation}/status,MarketplaceCheckoutController@status,Check reservation status
Marketplace,GET,/marketplace/resales,MarketplaceResaleController@index,List resale offers
Marketplace,GET,/marketplace/resales/{resale},MarketplaceResaleController@show,Resale details
Marketplace,POST,/marketplace/resales/{resale}/buy,MarketplaceResaleController@buy,Buy resale offer
Marketplace,POST,/marketplace/resales/{reservation}/list,MarketplaceResaleController@create,List booking for resale
Marketplace,GET,/marketplace/verification,MarketplaceVerificationController@show,Identity verification page
Marketplace,POST,/marketplace/verification/fee-intent,MarketplaceVerificationController@feeIntent,Create verification fee intent
Marketplace,POST,/marketplace/verification/start,MarketplaceVerificationController@start,Start Stripe Identity flow
PromoEngine,GET,/api/promo-engine/ongoing-deals,PromoEngineOffersController@index,Ongoing deals results
PromoEngine,POST,/api/promo-engine/impression,PromoEngineTrackingController@impression,Track promo impression
PromoEngine,POST,/api/promo-engine/click,PromoEngineTrackingController@click,Track promo click
PromoEngine,GET,/api/promo-engine/settings,PromoEngineSettingsController@show,Get engine settings (admin)
PromoEngine,PUT,/api/promo-engine/settings,PromoEngineSettingsController@update,Update engine settings (admin)
PromoEngine,GET,/api/promo-engine/metrics,PromoEngineMetricsController@index,Promo analytics metrics (admin)
PromoEngine,GET,/admin/promo-engine,PromoEngineController@index,Admin promo engine UI
PromoEngine,PUT,/admin/promo-engine,PromoEngineController@update,Save promo engine settings
Support,GET,/admin/support,SupportCaseController@index,Customer support list
Support,GET,/admin/support/{supportCase},SupportCaseController@show,Support case detail
Support,POST,/admin/support/{supportCase}/messages,SupportCaseController@sendMessage,Send admin message
Support,POST,/admin/support/{supportCase}/decision,SupportCaseController@updateDecision,Update decision + status
```

## Demo Data
- Seeder: `Database\Seeders\DemoDataSeeder`
- Runs with `php artisan migrate:fresh --seed --force`
- Creates: users, hotels, rooms, reservations, payment method, search logs, support case + chat, promo tracking events, resale listing.
