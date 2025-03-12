<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\OrganizerController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDateController;
use App\Http\Controllers\TicketPriceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TicketController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/venues', [VenueController::class, 'getAllVenues'])->middleware('auth:sanctum');
Route::post('/venues', [VenueController::class, 'addVenue'])->middleware('auth:sanctum');
Route::post('/venues/{id}', [VenueController::class, 'editVenue'])->middleware('auth:sanctum');
Route::delete('/venues/{id}', [VenueController::class, 'deleteVenue'])->middleware('auth:sanctum');

Route::get('/organizers', [OrganizerController::class, 'getAllOrganizers'])->middleware('auth:sanctum');
Route::post('/organizers', [OrganizerController::class, 'addOrganizer'])->middleware('auth:sanctum');
Route::post('/organizers/{id}', [OrganizerController::class, 'editOrganizer'])->middleware('auth:sanctum');
Route::delete('/organizers/{id}', [OrganizerController::class, 'deleteOrganizer'])->middleware('auth:sanctum');

Route::post('/events', [EventController::class, 'createEvent'])->middleware('auth:sanctum');
Route::get('/events', [EventController::class, 'getEvents']);

Route::post('/event_dates', [EventDateController::class, 'createEventDate'])->middleware('auth:sanctum');

Route::post('/ticket_prices', [TicketPriceController::class, 'createTicketPrice'])->middleware('auth:sanctum'); 

Route::post('/create-checkout-session', [CheckoutController::class, 'createCheckoutSession']);
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

Route::get('/generate-ticket/{ticketId}', [TicketController::class, 'generateTicket']);
Route::post('/ticket/create', [TicketController::class, 'createTicket']);
Route::get('/ticket/{ticketId}/download', [TicketController::class, 'generateTicket']);
Route::post('/api/create-ticket', [TicketController::class, 'createTicketAfterPayment']);
Route::post('/generate-ticket', [TicketController::class, 'createTicketAfterPayment']);