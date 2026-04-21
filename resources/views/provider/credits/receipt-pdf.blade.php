<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $purchase->purchase_uid }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; color: #1B5E20; margin: 0 0 5px; }
        .brand { color: #1B5E20; font-weight: 700; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 6px 0; vertical-align: top; }
        .label { color: #6b7280; text-transform: uppercase; font-size: 9px; letter-spacing: 1px; }
        .value { color: #111827; font-weight: 600; }
        .total-row td { padding-top: 14px; border-top: 1px solid #e5e7eb; font-size: 14px; }
        .amount { color: #1B5E20; font-size: 18px; font-weight: 700; text-align: right; }
        .footer { margin-top: 40px; font-size: 10px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; line-height: 1.4; }
        .header { border-bottom: 2px solid #1B5E20; padding-bottom: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">💍 Zawaj Pro — Algérie</div>
        <h1>{{ __('credits.receipt.title') }}</h1>
        <p style="font-family: monospace; color: #6b7280; font-size: 11px; margin: 4px 0;">{{ $purchase->purchase_uid }}</p>
    </div>

    <table>
        <tr>
            <td class="label" style="width: 30%;">{{ __('credits.receipt.issued_to') }}</td>
            <td class="value">
                {{ $purchase->user->name }}<br>
                <span style="color: #6b7280; font-weight: 400;">{{ $purchase->user->email }}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{{ __('credits.receipt.paid_on') }}</td>
            <td class="value">{{ $purchase->paid_at?->isoFormat('LL, HH:mm') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('credits.receipt.reference') }}</td>
            <td style="font-family: monospace; font-size: 11px;">{{ $purchase->gateway_payment_id ?? $purchase->gateway_checkout_id ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('credits.receipt.status') }}</td>
            <td class="value">{{ ucfirst($purchase->status) }}</td>
        </tr>
    </table>

    <table style="margin-top: 30px;">
        <tr>
            <td class="value">{{ $purchase->unlockPack?->name ?? '—' }}</td>
            <td style="text-align: right;" class="value">{{ $purchase->unlocks_count }} {{ __('credits.receipt.unlocks') }}</td>
        </tr>
        <tr class="total-row">
            <td class="value">{{ __('credits.receipt.amount') }}</td>
            <td class="amount">{{ number_format($purchase->amount_dzd, 0, ',', ' ') }} DZD</td>
        </tr>
    </table>

    <p class="footer">
        {{ __('credits.receipt.footer') }}<br>
        Document généré le {{ now()->isoFormat('LL') }} — Zawaj Pro.
    </p>
</body>
</html>
