<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('credits.receipt.title') }} {{ $purchase->purchase_uid }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1A1A1A; }
        h1 { font-size: 20px; color: #1A1A1A; margin: 0 0 5px; }
        .brand { color: #C8956C; font-weight: 700; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td { padding: 6px 0; vertical-align: top; }
        .label { color: #9A958D; text-transform: uppercase; font-size: 9px; letter-spacing: 1px; }
        .value { color: #1A1A1A; font-weight: 600; }
        .total-row td { padding-top: 14px; border-top: 1px solid #D4CFC7; font-size: 14px; }
        .amount { color: #C8956C; font-size: 18px; font-weight: 700; text-align: right; }
        .footer { margin-top: 40px; font-size: 10px; color: #9A958D; border-top: 1px solid #D4CFC7; padding-top: 12px; line-height: 1.4; }
        .header { border-bottom: 2px solid #C8956C; padding-bottom: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    @php
        $statusKey = 'credits.status.' . $purchase->status;
        $statusLabel = \Illuminate\Support\Facades\Lang::has($statusKey) ? __($statusKey) : ucfirst((string) $purchase->status);
    @endphp

    <div class="header">
        <div class="brand">{{ __('provider.brand') }}</div>
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
            <td class="value">{{ $purchase->paid_at?->isoFormat('LL, LT') ?? __('Not provided') }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('credits.receipt.reference') }}</td>
            <td style="font-family: monospace; font-size: 11px;">{{ $purchase->gateway_payment_id ?? $purchase->gateway_checkout_id ?? __('Not provided') }}</td>
        </tr>
        <tr>
            <td class="label">{{ __('credits.receipt.status') }}</td>
            <td class="value">{{ $statusLabel }}</td>
        </tr>
    </table>

    <table style="margin-top: 30px;">
        <tr>
            <td class="value">{{ $purchase->unlockPack?->name ? __($purchase->unlockPack->name) : __('Not provided') }}</td>
            <td style="text-align: right;" class="value">{{ trans_choice('credits.count', (int) $purchase->unlocks_count, ['count' => (int) $purchase->unlocks_count]) }}</td>
        </tr>
        <tr class="total-row">
            <td class="value">{{ __('credits.receipt.amount') }}</td>
            <td class="amount">{{ \App\Support\Money::format($purchase->amount, $purchase->currency) }}</td>
        </tr>
    </table>

    <p class="footer">
        {{ __('credits.receipt.demo_notice') }}<br>
        {{ __('credits.receipt.generated_on', ['date' => now()->isoFormat('LL')]) }}
    </p>
</body>
</html>
