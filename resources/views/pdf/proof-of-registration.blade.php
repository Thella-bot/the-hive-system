<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Proof of Registration — HBCI</title>
<style>
@page {
    size: A4;
    margin: 0.6in 0.65in 0.7in 0.65in;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    line-height: 1.5;
}

/* ─── Rules ─── */
.rule-dark  { width:100%; height:8px; background-color:#92400e; }
.rule-amber { width:100%; height:3px; background-color:#d97706; }
.divider    { width:100%; border-top:1.5px solid #d97706; margin:10px 0; }

/* ─── Header ─── */
.hdr-tbl   { width:100%; border-collapse:collapse; padding:12px 0 10px; }
.inst-name { font-size:19pt; font-weight:bold; color:#92400e; }
.inst-sub  { font-size:8.5pt; color:#6b7280; margin-top:3px; }
.hdr-ref   { font-size:7pt; color:#6b7280; text-align:right; }
.ref-code  { font-size:9pt; font-weight:bold; color:#92400e; margin-top:2px; }

/* ─── Document type banner ─── */
.doc-banner     { border:1px solid #fde68a; border-left:5px solid #d97706;
                  background-color:#fffbeb; padding:10px 16px; margin:14px 0; }
.doc-type-label { font-size:14pt; font-weight:bold; color:#92400e;
                  letter-spacing:1px; text-transform:uppercase; }
.doc-type-sub   { font-size:8pt; color:#6b7280; margin-top:4px; }

/* ─── Certification text ─── */
.cert-text { font-size:9.5pt; color:#374151; line-height:1.75; margin:12px 0; }

/* ─── Section headers ─── */
.sect-lbl { font-size:7pt; font-weight:bold; letter-spacing:1.5px;
            text-transform:uppercase; color:#92400e;
            border-bottom:1px solid #fde68a; padding-bottom:4px; margin:16px 0 9px; }

/* ─── Info table ─── */
.info-tbl     { width:100%; border-collapse:collapse; }
.info-tbl tr  { border-bottom:1px solid #f3f4f6; }
.info-tbl td  { padding:6px 4px; font-size:9.5pt; vertical-align:top; }
.itd-lbl      { font-weight:bold; color:#374151; width:185px; }
.itd-val      { color:#111827; }

/* ─── Status badge ─── */
.badge-registered { background-color:#d1fae5; color:#065f46;
                    padding:2px 11px; font-weight:bold; font-size:9pt; }

/* ─── Module table ─── */
.mod-tbl      { width:100%; border-collapse:collapse; margin-top:6px; }
.mod-tbl th   { background-color:#92400e; color:#ffffff;
                padding:6px 10px; font-size:8.5pt; text-align:left; }
.mod-tbl th.r { text-align:right; }
.mod-tbl td   { padding:6px 10px; font-size:9pt; border-bottom:1px solid #f3f4f6; }
.mod-tbl .alt { background-color:#fffbeb; }
.mod-tbl .sum { font-weight:bold; border-top:1px solid #d97706;
                background-color:#fef3c7; }
.tr           { text-align:right; }

/* ─── Validity box ─── */
.validity-box { border:1px solid #e5e7eb; background-color:#f9fafb;
                padding:10px 14px; margin:16px 0; font-size:8.5pt;
                color:#374151; line-height:1.6; }

/* ─── Important notice ─── */
.notice-box   { border-left:4px solid #d97706; background-color:#fffbeb;
                padding:8px 14px; margin:14px 0; font-size:8.5pt; color:#374151; }

/* ─── Signature block ─── */
.sig-tbl      { width:100%; border-collapse:collapse; margin-top:30px; }
.sig-tbl td   { padding:4px 8px; vertical-align:bottom; width:50%; }
.sig-line     { border-top:0.75px solid #374151; padding-top:5px; margin-top:34px; }
.sig-name     { font-weight:bold; font-size:8.5pt; }
.sig-role     { font-size:7.5pt; color:#6b7280; margin-top:2px; }

/* ─── Footer strip ─── */
.footer-strip { width:100%; background-color:#1f2937; color:#9ca3af;
                text-align:center; padding:7px 10px; font-size:7.5pt;
                margin-top:18px; border-collapse:collapse; }
.page-footer  { position:fixed; bottom:-0.5in; left:0; right:0;
                text-align:center; font-size:7pt; color:#9ca3af;
                border-top:0.5px solid #e5e7eb; padding-top:5px; }
</style>
</head>
<body>
@php
    $refNo         = 'HBCI-REG-' . date('Y') . '-' . str_pad($application->id, 5, '0', STR_PAD_LEFT);
    $issuedAt      = now()->format('d F Y');
    $issuedTime    = now()->format('H:i');
    $academicYear  = date('Y') . '/' . date('y', mktime(0,0,0,1,1,date('Y')+1));
    $regDate       = $application->registered_at?->format('d F Y') ?? $issuedAt;
    $programmeName = $application->programme?->name ?? 'N/A';
    $variantLabel  = $application->variant?->label ?? null;
    $variantDur    = $application->variant?->duration ?? $application->programme?->duration ?? null;
    $studentNo     = $student->student_number ?? ('STU-' . str_pad($student->id, 5, '0', STR_PAD_LEFT));
    $phone         = optional($student->profile)->phone ?? null;
    $totalCredits  = $modules ? $modules->sum('credits') : 0;
    $moduleCount   = $modules ? $modules->count() : 0;

    $logoPath = public_path('images/hbci-logo.png');
    $logoSrc  = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
@endphp

{{-- ════ RULES ════ --}}
<table style="width:100%;border-collapse:collapse;"><tr><td class="rule-dark"></td></tr></table>
<table style="width:100%;border-collapse:collapse;"><tr><td class="rule-amber"></td></tr></table>

{{-- ════ HEADER ════ --}}
<table class="hdr-tbl">
  <tr>
    <td style="width:78px;padding-right:14px;vertical-align:middle;">
      <img src="{{ $logoSrc }}" alt="HBCI Logo" style="width:68px;height:auto;">
    </td>
    <td style="vertical-align:middle;">
      <div class="inst-name">Honey Bee Culinary Institute</div>
      <div class="inst-sub">Maseru, Kingdom of Lesotho &nbsp;&middot;&nbsp; Registrar's Office</div>
    </td>
    <td style="width:160px;vertical-align:middle;" class="hdr-ref">
      <div>Reference Number</div>
      <div class="ref-code">{{ $refNo }}</div>
      <div style="margin-top:8px;">Date of Issue</div>
      <div style="font-size:8.5pt;color:#1a1a1a;margin-top:2px;">{{ $issuedAt }}</div>
    </td>
  </tr>
</table>

<table style="width:100%;border-collapse:collapse;margin-top:6px;"><tr><td class="divider"></td></tr></table>

{{-- ════ DOCUMENT TYPE BANNER ════ --}}
<div class="doc-banner">
  <div class="doc-type-label">Proof of Registration</div>
  <div class="doc-type-sub">
    Issued by the Registrar's Office &nbsp;&middot;&nbsp;
    Academic Year {{ $academicYear }} &nbsp;&middot;&nbsp;
    {{ $moduleCount }} module{{ $moduleCount == 1 ? '' : 's' }} enrolled
    &nbsp;&middot;&nbsp; {{ $totalCredits }} credit hours
  </div>
</div>

{{-- ════ CERTIFICATION PARAGRAPH ════ --}}
<p class="cert-text">
  This document certifies that the student named herein has been duly registered as a student
  at <strong>Honey Bee Culinary Institute</strong> for the programme and academic period
  specified below. Registration was processed and confirmed by the Registrar's Office in
  accordance with the institute's admission and registration requirements.
  This serves as official proof of enrolment for the current academic year.
</p>

{{-- ════ STUDENT DETAILS ════ --}}
<div class="sect-lbl">Student Details</div>
<table class="info-tbl">
  <tr>
    <td class="itd-lbl">Full Name</td>
    <td class="itd-val">{{ strtoupper($student->name) }}</td>
  </tr>
  <tr>
    <td class="itd-lbl">Student Number</td>
    <td class="itd-val">{{ $studentNo }}</td>
  </tr>
  <tr>
    <td class="itd-lbl">Email Address</td>
    <td class="itd-val">{{ $student->email }}</td>
  </tr>
  @if($phone)
  <tr>
    <td class="itd-lbl">Contact Number</td>
    <td class="itd-val">{{ $phone }}</td>
  </tr>
  @endif
</table>

{{-- ════ REGISTRATION DETAILS ════ --}}
<div class="sect-lbl">Registration Details</div>
<table class="info-tbl">
  <tr>
    <td class="itd-lbl">Programme of Study</td>
    <td class="itd-val">{{ $programmeName }}</td>
  </tr>
  @if($variantLabel)
  <tr>
    <td class="itd-lbl">Mode of Study</td>
    <td class="itd-val">{{ $variantLabel }}</td>
  </tr>
  @endif
  @if($variantDur)
  <tr>
    <td class="itd-lbl">Programme Duration</td>
    <td class="itd-val">{{ $variantDur }}</td>
  </tr>
  @endif
  <tr>
    <td class="itd-lbl">Academic Year</td>
    <td class="itd-val">{{ $academicYear }}</td>
  </tr>
  <tr>
    <td class="itd-lbl">Registration Status</td>
    <td class="itd-val"><span class="badge-registered">&#10003; Registered</span></td>
  </tr>
  <tr>
    <td class="itd-lbl">Registration Confirmed</td>
    <td class="itd-val">{{ $regDate }}</td>
  </tr>
</table>

{{-- ════ ENROLLED MODULES ════ --}}
<div class="sect-lbl">Enrolled Modules &mdash; {{ $academicYear }}</div>
@if($modules && $modules->count() > 0)
<table class="mod-tbl">
  <thead>
    <tr>
      <th style="width:18%;">Code</th>
      <th style="width:60%;">Module Name</th>
      <th class="r" style="width:22%;">Credit Hours</th>
    </tr>
  </thead>
  <tbody>
    @foreach($modules as $idx => $module)
    <tr class="{{ $idx % 2 == 1 ? 'alt' : '' }}">
      <td>{{ $module->code }}</td>
      <td>{{ $module->name }}</td>
      <td class="tr">{{ $module->credits }}</td>
    </tr>
    @endforeach
    <tr class="sum">
      <td colspan="2" style="text-align:right;padding-right:12px;">
        Total Credit Hours Registered
      </td>
      <td class="tr">{{ $totalCredits }}</td>
    </tr>
  </tbody>
</table>
@else
  <p style="color:#6b7280;font-style:italic;font-size:9pt;margin-top:6px;">
    No modules enrolled at the time of issue.
  </p>
@endif

{{-- ════ VALIDITY ════ --}}
<div class="validity-box">
  <strong>Document Validity:</strong> This proof of registration is valid for the
  {{ $academicYear }} academic year from the date of issue. It is produced for official
  purposes and may be presented to financial institutions, government offices, or other
  institutions as evidence of enrolment at Honey Bee Culinary Institute.
</div>

{{-- ════ IMPORTANT NOTICE ════ --}}
<div class="notice-box">
  <strong>Important:</strong> This document is only valid when accompanied by an official
  institute stamp. Any alterations render this document invalid. To verify the authenticity
  of this document, contact the Registrar's Office at
  <strong>registrar@hbci.ac.ls</strong> quoting reference <strong>{{ $refNo }}</strong>.
</div>

{{-- ════ SIGNATURES ════ --}}
<table class="sig-tbl">
  <tr>
    <td style="width:50%;">
      <div class="sig-line">
        <div class="sig-name">Registrar</div>
        <div class="sig-role">Honey Bee Culinary Institute</div>
        <div class="sig-role">Maseru, Kingdom of Lesotho</div>
      </div>
    </td>
    <td style="width:50%;text-align:right;">
      <div class="sig-line" style="border-top:0.75px solid #374151;">
        <div class="sig-name">Official Stamp</div>
        <div class="sig-role">Registrar's Office &mdash; HBCI</div>
        <div class="sig-role">Date: __________________</div>
      </div>
    </td>
  </tr>
</table>

{{-- ════ FOOTER STRIP ════ --}}
<table class="footer-strip" style="margin-top:18px;">
  <tr>
    <td>
      Reference: {{ $refNo }} &nbsp;&middot;&nbsp;
      Issued: {{ $issuedAt }} at {{ $issuedTime }} &nbsp;&middot;&nbsp;
      Honey Bee Culinary Institute &nbsp;&middot;&nbsp; Maseru, Lesotho &nbsp;&middot;&nbsp;
      Verify: registrar@hbci.ac.ls
    </td>
  </tr>
</table>

<div class="page-footer">
  Honey Bee Culinary Institute &middot; Maseru, Kingdom of Lesotho &middot;
  This document is only valid with an official stamp &middot; Unauthorised alteration is a criminal offence
</div>
</body>
</html>
