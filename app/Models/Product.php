<?php

namespace App\Models;

use App\Models\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public static function booted()
    {
        static::addGlobalScope(new LiveScope());
    }

    public function formattedPrice()
    {
        return money($this->price);
    }

    public function variations()
    {
        return $this->hasMany(Variation::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb200x200')
            ->fit(Manipulations::FIT_CROP, 200, 200);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useFallbackUrl(url('/storage/no-product.png'));
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public static function applyFilters($products, $query)
    {
        $products = DB::query()
            ->selectRaw(
                <<<SQL
                    p.*
                SQL
            )->fromRaw(
                <<<SQL
                    products p
                    join variations v on v.product_id = p.id
                SQL
            )->whereRaw(
                <<<SQL
                    $query
                SQL
            )->whereIn('p.id', $products->pluck('id')->toArray())
            ->get();

        return self::query()->whereIn('id', $products->pluck('id')->toArray())->get();
    }

    public static function search($search)
    {
        return self::query()->whereRaw("LOWER(title) like '%" . strtolower($search) . "%'" );
    }
}
