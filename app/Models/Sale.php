<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    const PAYMENT_METHODS = [
        'especes'      => 'Espèces',
        'mvola'        => 'MVola',
        'orange_money' => 'Orange Money',
        'airtel_money' => 'Airtel Money',
        'carte'        => 'Carte bancaire',
        'virement'     => 'Virement',
    ];

    protected $fillable = [
        'reference', 'client_id', 'user_id',
        'status', 'payment_method', 'total_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
