<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price',
        'stock_quantity', 'stock_alert', 'image', 'category_id',
    ];

    protected $appends = ['status'];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'stock_quantity' => 'integer',
            'stock_alert'    => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->stock_alert) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->stock_alert;
    }
}
