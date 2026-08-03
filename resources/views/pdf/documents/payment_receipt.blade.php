@extends('pdf.layout')

@section('content')
<p style="font-size:18px; font-weight:700;">OFFICIAL RECEIPT</p>
<table class="info-table" style="border: none;">
    <tr>
        <td style="border: none; width: 50%;">
            <strong>Receipt No:</strong> {{ $receipt_number }}<br>
            <strong>Date:</strong> {{ $date->format('d F Y') }}
        </td>
        <td style="border: none;">
            <strong>Received From:</strong><br>
            {{ $payer_name }}<br>
            Student No: {{ $student_number ?? 'N/A' }}<br>
            Programme: {{ $programme_name ?? 'N/A' }}
        </td>
    </tr>
</table>

<table class="finance-table">
    <thead>
        <tr>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit Price (M)</th>
            <th>Amount (M)</th>
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
        @if(isset($discount) && $discount > 0)
        <tr>
            <td colspan="3" style="text-align:right;">DISCOUNT</td>
            <td style="text-align:right;">M {{ number_format($discount, 2) }}</td>
        </tr>
        @endif
        <tr class="totals">
            <td colspan="3" style="text-align:right; font-size:14px;"><strong>TOTAL PAID</strong></td>
            <td style="text-align:right; font-size:14px;"><strong>M {{ number_format($total_paid, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

<table style="width:100%; border: none; margin-top:10px;">
    <tr>
        <td style="border: none;"><strong>Payment Method:</strong> {{ $payment_method }}</td>
        <td style="border: none;"><strong>Academic Year:</strong> {{ $academic_year }}</td>
    </tr>
    <tr>
        <td style="border: none;"><strong>Bank Ref:</strong> {{ $bank_ref ?? 'N/A' }}</td>
        <td style="border: none;"><strong>Cohort:</strong> {{ $cohort }}</td>
    </tr>
</table>

<p><strong>Amount in Words: {{ $amount_words }} Maloti Only</strong></p>
<p><em>This is an official receipt of Honey Bee Culinary Institute. Please retain for your records.</em></p>

<div class="signature-block">
    <div class="signature-line"></div>
    <p class="signature-name">{{ $cashier_name }}</p>
    <p class="signature-title">Finance Office --- Honey Bee Culinary Institute</p>
</div>
@endsection