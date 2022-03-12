<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke()
    {
        $categories = Category::tree()->get()->toTree();

        return view('home', [
            'categories' => $categories
        ]);
    }
}
