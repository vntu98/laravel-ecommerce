<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderIndexController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request)
    {
        $orders = $request->user()->orders()
            ->with('variations.product', 'variations.media', 'variations.ancestorsAndSelf', 'shippingType')
            ->latest()->get();

        return view('orders.index', [
            'orders' => $orders
        ]);
    }
}
