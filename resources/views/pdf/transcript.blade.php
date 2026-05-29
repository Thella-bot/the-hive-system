<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Official Academic Transcript — HBCI</title>
<style>
@page {
    size: A4;
    margin: 0.55in 0.6in 0.65in 0.6in;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9pt;
    color: #1a1a1a;
    line-height: 1.4;
}

/* ─── Structural elements ─── */
.top-rule      { width:100%; height:7px; background-color:#92400e; margin-bottom:0; }
.amber-rule    { width:100%; height:3px; background-color:#d97706; margin-bottom:12px; }
.divider       { width:100%; border-top:1.5px solid #d97706; margin:10px 0; }
.divider-thin  { width:100%; border-top:0.5px solid #e5e7eb; margin:6px 0; }

/* ─── Header ─── */
.header-wrap   { width:100%; border-collapse:collapse; margin-bottom:2px; padding-top:10px; }
.hdr-logo td  { vertical-align:middle; }
.institute-name { font-size:17pt; font-weight:bold; color:#92400e; letter-spacing:0.3px; }
.doc-title     { font-size:10.5pt; font-weight:bold; color:#d97706; letter-spacing:2px;
                 text-transform:uppercase; margin-top:4px; }
.doc-subtitle  { font-size:7.5pt; color:#6b7280; margin-top:3px; }
.hdr-ref-box   { font-size:7pt; color:#6b7280; text-align:right; }
.ref-num       { font-size:8.5pt; font-weight:bold; color:#92400e; }

/* ─── Student info box ─── */
.info-box      { border:1px solid #fde68a; background-color:#fffbeb;
                 padding:11px 14px; margin:10px 0 14px 0; }
.info-box-title { font-size:7pt; font-weight:bold; letter-spacing:1.5px;
                  text-transform:uppercase; color:#92400e; margin-bottom:8px; }
.info-grid     { width:100%; border-collapse:collapse; }
.info-grid td  { padding:2.5px 0; font-size:8.5pt; vertical-align:top; }
.lbl           { font-weight:bold; color:#374151; width:140px; }
.val           { color:#111827; }
.col-gap       { width:30px; }

/* ─── Module section ─── */
.mod-header    { width:100%; background-color:#92400e; color:#ffffff;
                 padding:5px 10px; font-size:9pt; font-weight:bold;
                 margin-top:14px; border-collapse:collapse; }
.mod-header-tbl { width:100%; border-collapse:collapse; }
.mod-header-tbl td { color:#ffffff; font-size:9pt; padding:5px 10px; vertical-align:middle; }
.mod-credit-badge { background-color:#d97706; padding:2px 9px;
                    font-size:7.5pt; font-weight:bold; color:#fff; }

/* ─── Grades table ─── */
.g-table       { width:100%; border-collapse:collapse; font-size:8.5pt; margin-top:0; }
.g-table th    { background-color:#f3f4f6; color:#374151; border:1px solid #d1d5db;
                 padding:5px 8px; font-weight:bold; font-size:7.5pt; }
.g-table td    { border:1px solid #e5e7eb; padding:5px 8px; color:#1a1a1a; }
.g-table .even { background-color:#fafafa; }
.tc            { text-align:center; }
.italic-muted  { color:#9ca3af; font-style:italic; }

/* ─── Module summary bar ─── */
.mod-sum-tbl   { width:100%; border-collapse:collapse; margin:0 0 12px 0; }
.mod-sum-tbl td { border:1px solid #fde68a; background-color:#fffbeb;
                  padding:6px 10px; text-align:center; }
.sum-lbl       { font-size:7pt; color:#6b7280; display:block; margin-bottom:2px; text-transform:uppercase; letter-spacing:.5px; }
.sum-val       { font-size:11pt; font-weight:bold; color:#92400e; }

/* ─── Letter grade colours ─── */
.ga  { color:#065f46; }   /* A+, A, A- */
.gb  { color:#1e40af; }   /* B+, B, B- */
.gc  { color:#92400e; }   /* C+, C, C- */
.gd  { color:#b45309; }   /* D           */
.gf  { color:#991b1b; }   /* F           */

/* ─── Grading scale ─── */
.scale-box     { border:1px solid #e5e7eb; background-color:#f9fafb;
                 padding:9px 14px; margin:12px 0; }
.scale-title   { font-size:7pt; font-weight:bold; letter-spacing:1px;
                 text-transform:uppercase; color:#374151; margin-bottom:6px; }
.scale-tbl     { width:100%; border-collapse:collapse; }
.scale-tbl th  { font-size:7pt; border:1px solid #e5e7eb; padding:3px 5px;
                 background-color:#f3f4f6; text-align:center; }
.scale-tbl td  { font-size:7pt; border:1px solid #e5e7eb; padding:3px 5px; text-align:center; }

/* ─── Final summary ─── */
.final-bar     { width:100%; background-color:#92400e; margin-top:14px;
                 border-collapse:collapse; }
.final-bar td  { color:#ffffff; padding:9px 10px; text-align:center; vertical-align:middle; }
.f-lbl         { font-size:7pt; color:rgba(255,255,255,0.65); display:block;
                 text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px; }
.f-val         { font-size:14pt; font-weight:bold; }
.standing-chip { background-color:#d97706; padding:3px 10px; font-size:8.5pt; font-weight:bold; }

/* ─── Signatures ─── */
.sig-tbl       { width:100%; border-collapse:collapse; margin-top:26px; }
.sig-tbl td    { padding:4px 8px; vertical-align:bottom; width:33%; }
.sig-line      { border-top:0.75px solid #374151; padding-top:5px; margin-top:30px; }
.sig-name      { font-weight:bold; font-size:8.5pt; color:#1a1a1a; }
.sig-role      { font-size:7.5pt; color:#6b7280; margin-top:2px; }

/* ─── Verification strip & footer ─── */
.verify-strip  { width:100%; background-color:#1f2937; color:#9ca3af;
                 text-align:center; padding:6px 10px; font-size:7.5pt; margin-top:16px;
                 border-collapse:collapse; }
.page-footer   { position:fixed; bottom:-0.5in; left:0; right:0;
                 text-align:center; font-size:7pt; color:#9ca3af;
                 border-top:0.5px solid #e5e7eb; padding-top:5px; }
</style>
</head>
<body>
@php
    /* ── Helper: percentage → letter, 4.0 points, CSS class ── */
    function hbciGrade(float $pct): array {
        if ($pct >= 90) return ['letter'=>'A+','pts'=>4.0,'cls'=>'ga'];
        if ($pct >= 80) return ['letter'=>'A', 'pts'=>4.0,'cls'=>'ga'];
        if ($pct >= 75) return ['letter'=>'A-','pts'=>3.7,'cls'=>'ga'];
        if ($pct >= 70) return ['letter'=>'B+','pts'=>3.3,'cls'=>'gb'];
        if ($pct >= 65) return ['letter'=>'B', 'pts'=>3.0,'cls'=>'gb'];
        if ($pct >= 60) return ['letter'=>'B-','pts'=>2.7,'cls'=>'gb'];
        if ($pct >= 55) return ['letter'=>'C+','pts'=>2.3,'cls'=>'gc'];
        if ($pct >= 50) return ['letter'=>'C', 'pts'=>2.0,'cls'=>'gc'];
        if ($pct >= 45) return ['letter'=>'C-','pts'=>1.7,'cls'=>'gc'];
        if ($pct >= 40) return ['letter'=>'D', 'pts'=>1.0,'cls'=>'gd'];
        return             ['letter'=>'F', 'pts'=>0.0,'cls'=>'gf'];
    }

    function hbciStanding(float $avg): string {
        if ($avg >= 80) return 'First Class Honours';
        if ($avg >= 70) return 'Upper Second Class';
        if ($avg >= 60) return 'Lower Second Class';
        if ($avg >= 50) return 'Third Class';
        if ($avg >= 40) return 'Pass';
        return 'Fail';
    }

    /* ── Logo as base64 ── */
    $logoPath = public_path('images/hbci-logo.png');
    $logoSrc  = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));

    /* ── Document metadata ── */
    $serialNo  = 'HBCI-TR-' . date('Y') . '-' . str_pad($student->id, 6, '0', STR_PAD_LEFT);
    $issuedAt  = now()->format('d F Y');

    /* ── Programme / cohort (best-effort) ── */
    $programmeName = optional(optional(optional($student->profile)->cohort)->programme)->name
                  ?? optional($modules->first())->programme?->name
                  ?? 'N/A';
    $cohortName    = optional(optional($student->profile)->cohort)->name ?? 'N/A';

    /* ── Per-module grade computation ── */
    $moduleGrades     = [];
    $totalCreditPts   = 0;
    $totalCreditHours = 0;

    foreach ($modules as $module) {
        $wMarks = 0; $wTotal = 0;
        foreach ($module->gradables as $gradable) {
            $sub = $gradable->submissions->first();
            if ($sub && $sub->grade !== null && $gradable->max_marks > 0) {
                $pct     = ($sub->grade / $gradable->max_marks) * 100;
                $wMarks += $pct * $gradable->weight;
                $wTotal += $gradable->weight;
            }
        }
        $finalPct = $wTotal > 0 ? round($wMarks / $wTotal, 1) : null;
        $gradeInfo = $finalPct !== null ? hbciGrade($finalPct) : null;
        $moduleGrades[$module->id] = ['pct' => $finalPct, 'info' => $gradeInfo];
        if ($finalPct !== null && $gradeInfo !== null) {
            $totalCreditPts   += $gradeInfo['pts'] * $module->credits;
            $totalCreditHours += $module->credits;
        }
    }

    $gpa4     = $totalCreditHours > 0 ? round($totalCreditPts / $totalCreditHours, 2) : null;
    $standing = is_numeric($gpa) ? hbciStanding((float) $gpa) : 'N/A';
    $studentNo = $student->student_number ?? ('STU-' . str_pad($student->id, 5, '0', STR_PAD_LEFT));
@endphp

{{-- ════ TOP RULE ════ --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:0;"><tr><td class="top-rule"></td></tr></table>
<table style="width:100%;border-collapse:collapse;margin-bottom:0;"><tr><td class="amber-rule"></td></tr></table>

{{-- ════ HEADER ════ --}}
<table class="header-wrap" style="padding-top:10px;">
  <tr>
    <td style="width:75px;padding-right:14px;vertical-align:middle;">
      <img src="{{ $logoSrc }}" alt="HBCI Logo" style="width:68px;height:auto;">
    </td>
    <td style="vertical-align:middle;">
      <div class="institute-name">Honey Bee Culinary Institute</div>
      <div class="doc-title">Official Academic Transcript</div>
      <div class="doc-subtitle">Maseru, Kingdom of Lesotho &nbsp;&middot;&nbsp; Registrar's Office</div>
    </td>
    <td style="width:155px;vertical-align:middle;" class="hdr-ref-box">
      <div>Document Reference</div>
      <div class="ref-num">{{ $serialNo }}</div>
      <div style="margin-top:8px;">Date of Issue</div>
      <div style="font-size:8pt;color:#1a1a1a;margin-top:2px;">{{ $issuedAt }}</div>
    </td>
  </tr>
</table>

<table style="width:100%;border-collapse:collapse;margin-top:10px;"><tr><td class="divider"></td></tr></table>

{{-- ════ STUDENT INFORMATION ════ --}}
<div class="info-box">
  <div class="info-box-title">Student Information</div>
  <table class="info-grid">
    <tr>
      <td class="lbl">Full Name</td>
      <td class="val">{{ strtoupper($student->name) }}</td>
      <td class="col-gap"></td>
      <td class="lbl">Student Number</td>
      <td class="val">{{ $studentNo }}</td>
    </tr>
    <tr>
      <td class="lbl">Programme</td>
      <td class="val">{{ $programmeName }}</td>
      <td class="col-gap"></td>
      <td class="lbl">Cohort / Year Group</td>
      <td class="val">{{ $cohortName }}</td>
    </tr>
    <tr>
      <td class="lbl">Email Address</td>
      <td class="val">{{ $student->email }}</td>
      <td class="col-gap"></td>
      <td class="lbl">Credits Attempted</td>
      <td class="val">{{ $totalCreditHours }}</td>
    </tr>
  </table>
</div>

{{-- ════ MODULE SECTIONS ════ --}}
@foreach ($modules as $i => $module)
@php
    $mg       = $moduleGrades[$module->id];
    $rowCount = 0;
@endphp

{{-- Module header --}}
<table class="mod-header-tbl" style="width:100%;border-collapse:collapse;background-color:#92400e;margin-top:{{ $i === 0 ? '4px' : '14px' }};">
  <tr>
    <td style="color:#ffffff;font-size:9pt;font-weight:bold;padding:5px 10px;">
      {{ strtoupper($module->code) }} &mdash; {{ strtoupper($module->name) }}
    </td>
    <td style="color:#ffffff;text-align:right;padding:5px 10px;">
      <span class="mod-credit-badge">{{ $module->credits }} Credit{{ $module->credits == 1 ? '' : 's' }}</span>
    </td>
  </tr>
</table>

{{-- Grades --}}
<table class="g-table">
  <thead>
    <tr>
      <th style="width:36%;text-align:left;">Assessment</th>
      <th style="width:12%;text-align:center;">Type</th>
      <th style="width:10%;text-align:center;">Weight</th>
      <th style="width:14%;text-align:center;">Score</th>
      <th style="width:14%;text-align:center;">Percentage</th>
      <th style="width:14%;text-align:center;">Grade</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($module->gradables as $gradable)
    @php
        $sub    = $gradable->submissions->first();
        $hasMk  = $sub && $sub->grade !== null;
        $aPct   = $hasMk ? round(($sub->grade / max($gradable->max_marks, 1)) * 100, 1) : null;
        $aGrade = $aPct !== null ? hbciGrade($aPct) : null;
        $rowCls = ($rowCount++ % 2 === 1) ? ' class="even"' : '';
    @endphp
    <tr{{ $rowCls }}>
      <td>{{ $gradable->title }}</td>
      <td class="tc" style="font-size:7.5pt;">{{ ucfirst($gradable->type ?? 'Assessment') }}</td>
      <td class="tc">{{ round($gradable->weight * 100) }}%</td>
      <td class="tc">{{ $hasMk ? $sub->grade . ' / ' . $gradable->max_marks : '&mdash;' }}</td>
      <td class="tc">{{ $aPct !== null ? $aPct . '%' : '&mdash;' }}</td>
      <td class="tc {{ $aGrade['cls'] ?? '' }}" style="font-weight:bold;">
        {{ $aGrade['letter'] ?? '&mdash;' }}
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="6" class="tc italic-muted" style="padding:10px;">
        No assessments recorded for this module.
      </td>
    </tr>
    @endforelse
  </tbody>
</table>

{{-- Module summary bar --}}
<table class="mod-sum-tbl">
  <tr>
    <td style="width:33%;">
      <span class="sum-lbl">Module Final Average</span>
      <span class="sum-val {{ $mg['info']['cls'] ?? '' }}">
        {{ $mg['pct'] !== null ? $mg['pct'] . '%' : 'Incomplete' }}
      </span>
    </td>
    <td style="width:33%;">
      <span class="sum-lbl">Letter Grade</span>
      <span class="sum-val {{ $mg['info']['cls'] ?? '' }}">
        {{ $mg['info']['letter'] ?? '&mdash;' }}
      </span>
    </td>
    <td style="width:33%;">
      <span class="sum-lbl">Grade Points (4.0)</span>
      <span class="sum-val">
        {{ $mg['info']['pts'] ?? '&mdash;' }}
      </span>
    </td>
  </tr>
</table>
@endforeach

{{-- ════ GRADING SCALE ════ --}}
<div class="scale-box">
  <div class="scale-title">Grading Scale &amp; Grade Point Equivalence</div>
  <table class="scale-tbl">
    <tr>
      <th>A+</th><th>A</th><th>A-</th><th>B+</th><th>B</th><th>B-</th>
      <th>C+</th><th>C</th><th>C-</th><th>D</th><th>F</th>
    </tr>
    <tr>
      <td>90–100</td><td>80–89</td><td>75–79</td><td>70–74</td><td>65–69</td><td>60–64</td>
      <td>55–59</td><td>50–54</td><td>45–49</td><td>40–44</td><td>&lt;40</td>
    </tr>
    <tr>
      <td>4.0</td><td>4.0</td><td>3.7</td><td>3.3</td><td>3.0</td><td>2.7</td>
      <td>2.3</td><td>2.0</td><td>1.7</td><td>1.0</td><td>0.0</td>
    </tr>
  </table>
</div>

{{-- ════ FINAL SUMMARY ════ --}}
<table class="final-bar">
  <tr>
    <td style="width:25%;border-right:1px solid rgba(255,255,255,0.15);">
      <span class="f-lbl">Cumulative Average</span>
      <span class="f-val">{{ is_numeric($gpa) ? $gpa . '%' : $gpa }}</span>
    </td>
    <td style="width:25%;border-right:1px solid rgba(255,255,255,0.15);">
      <span class="f-lbl">GPA &mdash; 4.0 Scale</span>
      <span class="f-val">{{ $gpa4 ?? '&mdash;' }}</span>
    </td>
    <td style="width:20%;border-right:1px solid rgba(255,255,255,0.15);">
      <span class="f-lbl">Credits Earned</span>
      <span class="f-val">{{ $totalCreditHours }}</span>
    </td>
    <td style="width:30%;">
      <span class="f-lbl">Academic Standing</span>
      <span class="standing-chip">{{ $standing }}</span>
    </td>
  </tr>
</table>

{{-- ════ SIGNATURES ════ --}}
<table class="sig-tbl">
  <tr>
    <td style="width:33%;">
      <div class="sig-line">
        <div class="sig-name">Registrar</div>
        <div class="sig-role">Honey Bee Culinary Institute</div>
      </div>
    </td>
    <td style="width:33%;text-align:center;">
      <div class="sig-line">
        <div class="sig-name">Academic Affairs — Official Stamp</div>
        <div class="sig-role">Registrar's Office, HBCI</div>
      </div>
    </td>
    <td style="width:33%;text-align:right;">
      <div class="sig-line">
        <div class="sig-name">Principal / Dean</div>
        <div class="sig-role">Honey Bee Culinary Institute</div>
      </div>
    </td>
  </tr>
</table>

{{-- ════ VERIFICATION STRIP ════ --}}
<table class="verify-strip" style="margin-top:16px;">
  <tr>
    <td>
      Official Document &nbsp;&middot;&nbsp; Serial: {{ $serialNo }} &nbsp;&middot;&nbsp;
      Generated: {{ now()->format('d F Y, H:i') }} UTC+2 &nbsp;&middot;&nbsp;
      Verification: registrar@hbci.ac.ls &nbsp;&middot;&nbsp;
      This document is only valid when bearing an official stamp
    </td>
  </tr>
</table>

<div class="page-footer">
  Honey Bee Culinary Institute &nbsp;&middot;&nbsp; Maseru, Kingdom of Lesotho &nbsp;&middot;&nbsp;
  Unauthorised reproduction or alteration of this document is a criminal offence
</div>
</body>
</html>
