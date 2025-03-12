<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseSuccess;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function completeOrder(Request $request)
    {
        // Assume you have logic here to process the order
        $user = auth()->user(); // Or any logic to get the user
        $orderDetails = $request->orderDetails; // Assume you have the order details from the request

        // Send the email
        Mail::to($user->email)->send(new PurchaseSuccess($user, $orderDetails));

        // Optionally, return a response
        return response()->json(['message' => 'Order placed successfully!']);
    }
}

