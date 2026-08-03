@extends('pdf.layout')

@section('content')
<p style="font-size:18px; font-weight:700;">INVOICE</p>

<table style="width:100%; border: none;">
    <tr>
        <td style="border: none; width: 50%;">
            <strong>Invoice No:</strong> {{ $invoice_number }}<br>
            <strong>Date Issued:</strong> {{ $issue_date->format('d F Y') }}<br>
            <strong>Due Date:</strong> {{ $due_date->format('d F Y') }}
        </td>
        <td style="border: none;">
            <strong>Bill To:</strong><br>
            {{ $client_name }}<br>
            {{ $client_contact_person }}<br>
            {{ $client_address }}<br>
            {{ $client_email }} / {{ $client_phone }}
        </td>
    </tr>
</table>

<table class="finance-table">
    <thead>
        <tr>
            <th>Description of Service / Item</th>
            <th>Qty</th>
            <th>Unit Price (M)</th>
            <th>Total (M)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item['description'] }}</td>
            <td style="text-align:center;">{{ $item['qty'] }}</td>
            <td style="text-align:right;">{{ number_format($item['unit_price'], 2) }}</td>
            <td style="text-align:right;">{{ number_format($item['total'], 2) }}</td>
        </tr>
        @endforeach
        <tr class="totals">
            <td colspan="3" style="text-align:right;"><strong>SUB-TOTAL</strong></td>
            <td style="text-align:right;">M {{ number_format($sub_total, 2) }}</td>
        </tr>
        @if(isset($vat) && $vat > 0)
        <tr>
            <td colspan="3" style="text-align:right;">VAT ({{ $vat_rate }}%)</td>
            <td style="text-align:right;">M {{ number_format($vat, 2) }}</td>
        </tr>
        @endif
        <tr class="totals">
            <td colspan="3" style="text-align:right; font-size:14px;"><strong>TOTAL DUE</strong></td>
            <td style="text-align:right; font-size:14px;"><strong>M {{ number_format($total_due, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<p><strong>Payment Details</strong></p>
<table class="info-table">
    <tr>
        <td class="label">Bank Name</td>
        <td class="value">{{ $bank_name }}</td>
    </tr>
    <tr>
        <td class="label">Account Name</td>
        <td class="value">Honey Bee Culinary Institute</td>
    </tr>
    <tr>
        <td class="label">Account Number</td>
        <td class="value">{{ $account_number }}</td>
    </tr>
    <tr>
        <td class="label">Branch / Code</td>
        <td class="value">{{ $branch_code }}</td>
    </tr>
    <tr>
        <td class="label">Reference</td>
        <td class="value">{{ $payment_reference }}</td>
    </tr>
</table>
<p>Payment is due on or before <strong>{{ $due_date->format('d F Y') }}</strong>. Please use the invoice number as your payment reference. For payment queries, contact the Finance Office at finance@hbci.ac.ls.</p>

<div class="signature-block">
    <div class="signature-line"></div>
    <p class="signature-name">{{ $authorised_signatory }}</p>
    <p class="signature-title">Finance Office --- Honey Bee Culinary Institute</p>
</div>
@endsection