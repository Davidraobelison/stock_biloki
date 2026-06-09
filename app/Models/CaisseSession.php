<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaisseSession extends Model
{
    protected $fillable = [
        'user_id', 'opening_balance', 'closing_balance',
        'status', 'notes', 'opened_at', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'opened_at'       => 'datetime',
            'closed_at'       => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(CaisseMouvement::class, 'session_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function expectedBalance(): float
    {
        $entrees = (float) $this->mouvements()->where('type', 'entree')->sum('amount');
        $sorties = (float) $this->mouvements()->where('type', 'sortie')->sum('amount');

        return (float) $this->opening_balance + $entrees - $sorties;
    }

    public static function current(): ?self
    {
        return self::where('status', 'open')->latest()->first();
    }
}
