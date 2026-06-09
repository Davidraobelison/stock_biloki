<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket – {{ $sale->reference }}</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .ticket {
            background: #fff;
            width: 320px;
            padding: 1.5rem 1.25rem;
            border-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        }

        .store-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .store-sub {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
        }

        .sep {
            border: none;
            border-top: 1px dashed #9ca3af;
            margin: 12px 0;
        }

        .meta { font-size: 11px; color: #374151; }
        .meta .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .meta .label { color: #9ca3af; }

        .items-header {
            display: flex;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 6px;
        }

        .item-row {
            display: flex;
            font-size: 12px;
            padding: 3px 0;
            align-items: flex-start;
        }

        .col-name  { flex: 1; }
        .col-qty   { width: 30px; text-align: center; }
        .col-price { width: 60px; text-align: right; }
        .col-sub   { width: 70px; text-align: right; }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            padding-top: 8px;
            border-top: 2px solid #111827;
            font-size: 15px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .status-cancelled {
            text-align: center;
            background: #fee2e2;
            color: #dc2626;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 4px 0;
            border-radius: 2px;
            margin-bottom: 8px;
        }

        /* Boutons — cachés à l'impression */
        .no-print {
            margin-top: 1.5rem;
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            font-size: 13px;
            cursor: pointer;
            font-family: sans-serif;
            text-decoration: none;
        }

        .btn-print  { background: #4f46e5; color: #fff; }
        .btn-back   { background: #e5e7eb; color: #374151; }

        @media print {
            body { background: #fff; padding: 0; }
            .ticket { box-shadow: none; border-radius: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div>
    <div class="ticket">

        {{-- En-tête boutique --}}
        <div style="display:flex;justify-content:center;margin-bottom:6px;">
            <div style="background:#4f46e5;border-radius:8px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
                <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                </svg>
            </div>
        </div>
        <div class="store-name">Biloki</div>
        <div class="store-sub">Ticket de caisse</div>

        <hr class="sep">

        {{-- Statut annulée --}}
        @if($sale->isCancelled())
            <div class="status-cancelled">*** VENTE ANNULÉE ***</div>
        @endif

        {{-- Métadonnées --}}
        <div class="meta">
            <div class="row">
                <span class="label">Réf.</span>
                <span>{{ $sale->reference }}</span>
            </div>
            <div class="row">
                <span class="label">Date</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($sale->client)
            <div class="row">
                <span class="label">Client</span>
                <span>{{ $sale->client->name }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">Paiement</span>
                <span>{{ \App\Models\Sale::PAYMENT_METHODS[$sale->payment_method] ?? $sale->payment_method }}</span>
            </div>
            @if($sale->notes)
            <div class="row">
                <span class="label">Note</span>
                <span>{{ $sale->notes }}</span>
            </div>
            @endif
        </div>

        <hr class="sep">

        {{-- En-tête colonnes --}}
        <div class="items-header">
            <span class="col-name">Article</span>
            <span class="col-qty">Qté</span>
            <span class="col-price">P.U.</span>
            <span class="col-sub">S-Total</span>
        </div>

        {{-- Lignes articles --}}
        @foreach($sale->items as $item)
        <div class="item-row">
            <span class="col-name">{{ $item->product?->name ?? '—' }}</span>
            <span class="col-qty">{{ $item->quantity }}</span>
            <span class="col-price">{{ number_format($item->unit_price, 0, ',', ' ') }}</span>
            <span class="col-sub">{{ number_format($item->subtotal, 0, ',', ' ') }}</span>
        </div>
        @endforeach

        {{-- Total --}}
        <div class="total-row">
            <span>TOTAL</span>
            <span>{{ number_format($sale->total_amount, 0, ',', ' ') }} Ar</span>
        </div>

        <hr class="sep">

        {{-- Pied --}}
        <div class="footer">
            Merci pour votre achat !<br>
            Caissier : {{ $sale->user?->name ?? '—' }}
        </div>
    </div>

    {{-- Boutons hors impression --}}
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">Imprimer</button>
        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-back">Retour</a>
    </div>
</div>

</body>
</html>
