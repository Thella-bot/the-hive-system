@extends('pdf.layout')

@section('content')
<p><strong>{{ $recipient_title }} {{ $recipient_name }}</strong><br>
    {{ $recipient_position }}<br>
    {{ $recipient_org }}<br>
    {{ $recipient_city }}
</p>

<p>Dear {{ $recipient_title }} {{ $recipient_last_name }},</p>

<p class="subject">RE: {{ strtoupper($subject) }}</p>

<p>{{ $opening_paragraph }}</p>

@if(isset($second_paragraph))
<p>{{ $second_paragraph }}</p>
@endif

@if(isset($third_paragraph))
<p>{{ $third_paragraph }}</p>
@endif

<p>{{ $closing_paragraph }}</p>

<div class="signature-block">
    <p>Yours sincerely,</p>
    <div class="signature-line"></div>
    <p class="signature-name">{{ $signatory_name }}</p>
    <p class="signature-title">{{ $signatory_title }}</p>
    <p class="institute-footer">Honey Bee Culinary Institute</p>
    @if(isset($enclosures))
    <p><em>Encl: {{ $enclosures }}</em></p>
    @endif
    @if(isset($cc_recipients))
    <p><em>cc: {{ $cc_recipients }}</em></p>
    @endif
</div>
@endsection