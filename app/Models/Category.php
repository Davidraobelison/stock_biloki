<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productsCount(): int
    {
        return $this->products()->count();
    }

    public function totalStock(): int
    {
        return $this->products()->sum('stock_quantity');
    }
}
