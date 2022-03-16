<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variation;
use Livewire\Component;

class ProductBrowser extends Component
{
    public $category;

    public $queryFilters = [];

    public $priceRange = [
        'max' => null
    ];

    public function mount()
    {
        $this->queryFilters = $this->category->products->pluck('variations')
            ->flatten()
            ->groupBy('type')
            ->keys()
            ->mapWithKeys(fn ($key) => [$key => []])
            ->toArray();
    }

    public function render()
    {
        $maxPrice = $this->category->products->max('price');

        $this->priceRange['max'] = $this->priceRange['max'] ?: $maxPrice;

        $filters = collect($this->queryFilters)->filter(fn ($filter) => !empty($filter))
            ->map(function ($value, $key) {
                return collect($value)->map(fn ($value) => "(v.type = '" . $key . "' and v.title = '" . $value . "')");
            })
            ->flatten()
            ->join(' OR ');

        if ($this->priceRange['max']) {
            $filters .= ($filters ? ' AND ' : '') . ' p.price <= ' . $this->priceRange['max'];
        }

        $products = Category::getProducts($this->category);
        
        $filterTypes = Variation::getTypes($products);

        if ($filters) {
            $products = Product::applyFilters($products, $filters);
        }

        return view('livewire.product-browser', [
            'products' => $products,
            'filters' => $filterTypes,
            'maxPrice' => $maxPrice
        ]);
    }
}
