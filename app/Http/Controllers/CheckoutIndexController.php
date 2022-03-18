<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RedirectIfCartEmpty;
use Illuminate\Http\Request;

class CheckoutIndexController extends Controller
{
    public function __construct()
    {
        $this->middleware(RedirectIfCartEmpty::class);
    }

    public function __invoke()
    {
        return view('checkout');
    }
}
