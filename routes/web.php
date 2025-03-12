<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/create-checkout-session', [CheckoutController::class, 'createCheckoutSession']);
Route::get('/checkout/success', function () {
    return view('checkout.success'); 
})->name('checkout.success');
Route::get('/checkout/cancel', function () {
    return view('checkout.cancel'); 
})->name('checkout.cancel');


Route::get('/generate-ticket/{ticketId}', [TicketController::class, 'generateTicket']);
Route::post('/api/create-ticket', [TicketController::class, 'createTicketAfterPayment']);