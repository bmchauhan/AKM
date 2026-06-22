<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $receipt_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0F141E;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 32px;
            background: #ffffff;
        }
        .header {
            border-bottom: 3px solid #AB1E23;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #080D21;
            margin: 0;
        }
        .brand-sub {
            font-size: 11px;
            color: #AB1E23;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            color: #080D21;
            margin: 20px 0 8px;
        }
        .intro {
            color: #0F141E;
            margin-bottom: 20px;
        }
        .meta-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .meta-grid td {
            padding: 8px 10px;
            vertical-align: top;
            border: 1px solid #E6EBF4;
            background: #F8F9FC;
        }
        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #080D21;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #080D21;
            margin: 0 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E6C280;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th {
            background: #080D21;
            color: #E6EBF4;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 10px 8px;
            text-align: left;
        }
        table.items td {
            border-bottom: 1px solid #E6EBF4;
            padding: 10px 8px;
            vertical-align: top;
        }
        table.items tr:nth-child(even) td {
            background: #F8F9FC;
        }
        .text-right { text-align: right; }
        .total-box {
            width: 280px;
            margin-left: auto;
            border: 2px solid #AB1E23;
            padding: 14px 16px;
            background: #ECEAE1;
        }
        .total-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #080D21;
            font-weight: bold;
        }
        .total-value {
            font-size: 20px;
            font-weight: bold;
            color: #AB1E23;
            margin-top: 4px;
        }
        .footer {
            margin-top: 36px;
            padding-top: 12px;
            border-top: 1px solid #E6EBF4;
            font-size: 10px;
            color: #666;
        }
        .notes {
            margin-top: 16px;
            padding: 12px;
            background: #E6EBF4;
            border-left: 4px solid #E6C280;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">{{ __('messages.brand_name') }}</p>
        <p class="brand-sub">{{ __('messages.finance_receipt_society_subtitle') }}</p>
    </div>

    <p class="receipt-title">{{ $receipt_title }}</p>
    <p class="intro">{{ __('messages.finance_receipt_intro', ['number' => $receipt_number]) }}</p>

    <table class="meta-grid">
        <tr>
            <td width="50%">
                <span class="meta-label">{{ $payee_label }}</span>
                {{ $payee_name }}
                @if ($payee_house)
                    <br><span style="font-size:11px;color:#666;">{{ __('messages.users_house') }}: {{ $payee_house }}</span>
                @endif
            </td>
            <td width="50%">
                <span class="meta-label">{{ __('messages.finance_collection_type') }}</span>
                {{ $receipt_kind_label }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">{{ __('messages.finance_paid_on') }}</span>
                {{ $paid_on }}
            </td>
            <td>
                <span class="meta-label">{{ __('messages.finance_payment_mode') }}</span>
                {{ $payment_mode ?? '—' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="meta-label">{{ __('messages.finance_reference') }}</span>
                {{ $reference ?? '—' }}
            </td>
        </tr>
    </table>

    <p class="section-title">{{ $line_heading }}</p>

    <table class="items">
        <thead>
            <tr>
                <th>{{ $show_charge_column ? __('messages.finance_receipt_billing_month') : __('messages.finance_receipt_description') }}</th>
                @if ($show_charge_column)
                    <th class="text-right">{{ __('messages.finance_receipt_monthly_charge') }}</th>
                @endif
                <th class="text-right">{{ __('messages.finance_receipt_amount_applied') }}</th>
                @if ($show_status_column)
                    <th>{{ __('messages.finance_maintenance_status') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($lines as $line)
                <tr>
                    <td>{{ $show_charge_column ? $line['period'] : $line['description'] }}</td>
                    @if ($show_charge_column)
                        <td class="text-right">{{ $line['charge'] ?? '—' }}</td>
                    @endif
                    <td class="text-right">{{ $line['applied'] }}</td>
                    @if ($show_status_column)
                        <td>{{ $line['status'] ?? '—' }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-label">{{ __('messages.finance_my_payments_total') }}</div>
        <div class="total-value">{{ $total_amount }}</div>
    </div>

    @if ($notes)
        <div class="notes">
            <strong>{{ __('messages.finance_notes') }}:</strong> {{ $notes }}
        </div>
    @endif

    <div class="footer">
        {{ __('messages.finance_receipt_footer', ['datetime' => $generated_at]) }}
    </div>
</body>
</html>
