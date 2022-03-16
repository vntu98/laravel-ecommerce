<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model
{
    use HasFactory;
    use HasRecursiveRelationships;

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public static function descendantAndSelfCategories(Category $category)
    {
        $listCategories = DB::select('
            with recursive cte as (
                select id, title, slug, 0 as depth
                from categories
                where id = ?
                union all
                select c2.id, c2.title, c2.slug, depth + 1
                from categories c2
                join cte c on c.id = c2.parent_id
            ) select * from cte
        ', [$category->id]);

        return collect($listCategories);
    }

    public static function getProducts(Category $category)
    {
        $descendantAndSelfCategoriesIds = self::descendantAndSelfCategories($category)->pluck('id')->implode(',');
        $listProductIds = DB::select("select product_id from category_product where category_id in ($descendantAndSelfCategoriesIds)");

        return Product::query()->whereIn('id', collect($listProductIds)->pluck('product_id')->toArray())->get();
    }
}
