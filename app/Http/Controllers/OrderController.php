<?php

namespace App\Http\Controllers;

use App\Mail\PurchaseSuccess;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function completeOrder(Request $request)
    {
        $user = auth()->user();
        $orderDetails = $request->orderDetails; 

        Mail::to($user->email)->send(new PurchaseSuccess($user, $orderDetails));

        return response()->json(['message' => 'Order placed successfully!']);
    }
}

