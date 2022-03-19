<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderConfirmationIndexController extends Controller
{
    public function __invoke(string $uuid)
    {
        $order = Order::where('uuid', $uuid)->firstOrFail();

        return view('orders.confirmation', [
            'order' => $order
        ]);
    }
}
