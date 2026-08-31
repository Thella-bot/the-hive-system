<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student ID Card</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'DejaVu Sans', sans-serif; background: #ffffff; }

        /* ===== FRONT OF CARD ===== */
        .card-front {
            width: 242pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
            background: #ffffff;
            page-break-after: always;
        }

        /* Subtle guilloche pattern */
        .guilloche {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(45deg, transparent, transparent 1.5pt, rgba(245, 200, 66, 0.02) 1.5pt, rgba(245, 200, 66, 0.02) 3pt);
        }

        /* Gold accent stripe */
        .accent-stripe {
            position: absolute;
            top: 0;
            left: 0;
            width: 4pt;
            height: 100%;
            background: linear-gradient(180deg, #f5c842 0%, #facc15 50%, #d4a503 100%);
        }

        /* Header */
        .header {
            position: absolute;
            top: 0;
            left: 4pt;
            right: 0;
            height: 33.5pt;
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        }

        .logo {
            position: absolute;
            top: 8pt;
            left: 12pt;
            height: 18pt;
            width: auto;
            filter: brightness(0) invert(1);
        }

        .institute-name {
            position: absolute;
            top: 7pt;
            left: 36pt;
            white-space: nowrap;
        }

        .institute-name-primary {
            color: #f5c842;
            font-size: 13pt;
            font-weight: 900;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
            line-height: 1;
        }

        .institute-name-secondary {
            color: #ffffff;
            font-size: 6.5pt;
            font-weight: 600;
            letter-spacing: 0.3pt;
            text-transform: uppercase;
            line-height: 1.4;
        }

        .card-type {
            position: absolute;
            top: 13pt;
            right: 10pt;
            color: #f5c842;
            font-size: 5pt;
            font-weight: 700;
            letter-spacing: 0.4pt;
            text-transform: uppercase;
        }

        /* Main content */
        .content {
            position: absolute;
            top: 33.5pt;
            left: 12pt;
            right: 10pt;
            bottom: 6pt;
        }

        .content-row {
            display: flex;
            height: 100%;
            gap: 8pt;
        }

        /* Left column - details */
        .details-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .student-name {
            color: #111827;
            font-size: 11pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
            margin-bottom: 5pt;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
        }

        .detail-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 2pt;
        }

        .detail-label {
            color: #6b7280;
            font-size: 4.8pt;
            font-weight: 600;
            text-transform: uppercase;
            width: 22pt;
            flex-shrink: 0;
        }

        .detail-value {
            color: #111827;
            font-size: 5.5pt;
            font-weight: 700;
            text-transform: uppercase;
            overflow: hidden;
            white-space: nowrap;
        }

        .detail-value-valid {
            color: #d4a503;
        }

        /* Right column - photo & qr */
        .media-col {
            width: 68pt;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5pt;
        }

        .photo-frame {
            width: 58pt;
            height: 48pt;
            border: 1.5pt solid rgba(245, 200, 66, 0.4);
            border-radius: 2pt;
            overflow: hidden;
        }

        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .initials-fallback {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #fef3c7 0%, #fef08a 100%);
            color: #d4a503;
            font-size: 18pt;
            font-weight: 900;
            text-align: center;
            line-height: 48pt;
        }

        .qr-row {
            display: flex;
            align-items: center;
            gap: 3pt;
            width: 100%;
        }

        .qr-code {
            width: 22pt;
            height: 22pt;
            padding: 1pt;
            background: #ffffff;
            border: 0.5pt solid #e5e7eb;
            border-radius: 1pt;
        }

        .qr-label {
            color: #9ca3af;
            font-size: 3.5pt;
            text-transform: uppercase;
            line-height: 1.1;
        }

        /* Footer accent */
        .footer-accent {
            position: absolute;
            bottom: 0;
            left: 4pt;
            right: 0;
            height: 2pt;
            background: linear-gradient(90deg, #f5c842 0%, #facc15 50%, #d4a503 100%);
        }

        /* ===== BACK OF CARD ===== */
        .card-back {
            width: 242pt;
            height: 153pt;
            position: relative;
            overflow: hidden;
            background: #ffffff;
        }

        /* Magnetic stripe */
        .magnetic-stripe {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 34pt;
            background: #1f2937;
        }

        /* Back content */
        .back-content {
            position: absolute;
            top: 44pt;
            left: 14pt;
            right: 14pt;
            bottom: 12pt;
        }

        .signature-section {
            margin-bottom: 10pt;
        }

        .signature-label {
            color: #6b7280;
            font-size: 4.8pt;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 3pt;
        }

        .signature-line {
            width: 130pt;
            border-bottom: 1pt dashed #d1d5db;
        }

        .info-row {
            display: flex;
            align-items: baseline;
            margin-bottom: 3pt;
        }

        .info-label {
            color: #6b7280;
            font-size: 4.5pt;
            font-weight: 600;
            text-transform: uppercase;
            width: 28pt;
            flex-shrink: 0;
        }

        .info-value {
            color: #111827;
            font-size: 5pt;
            font-weight: 500;
        }

        .info-value-accent {
            color: #d4a503;
            font-weight: 700;
        }

        .barcode-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }

        .barcode {
            display: flex;
            align-items: center;
            gap: 4pt;
        }

        .barcode-bars {
            display: flex;
            gap: 0.5pt;
            height: 16pt;
        }

        .barcode-bar {
            background: #111827;
        }

        .barcode-number {
            color: #374151;
            font-size: 6pt;
            font-family: monospace;
            font-weight: 700;
            letter-spacing: 1pt;
        }

        .institute-footer {
            color: #f5c842;
            font-size: 6pt;
            font-weight: 700;
            letter-spacing: 0.3pt;
            text-transform: uppercase;
        }

        .back-footer-accent {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2pt;
            background: linear-gradient(90deg, #1f2937 0%, #4b5563 50%, #1f2937 100%);
        }
    </style>
</head>
<body>
    <!-- FRONT -->
    <div class="card-front">
        <div class="guilloche"></div>
        <div class="accent-stripe"></div>

        <!-- Header -->
        <div class="header">
            <img class="logo" src="{{ public_path('images/hbci-logo.png') }}" alt="Honey Bee Culinary Institute">
            <div class="institute-name">
                <div class="institute-name-primary">Honey Bee</div>
                <div class="institute-name-secondary">Culinary Institute</div>
            </div>
            <div class="card-type">Student Identification</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="content-row">
                <!-- Details -->
                <div class="details-col">
                    <div class="student-name">{{ $name }}</div>

                    <div class="detail-row">
                        <span class="detail-label">ID No.</span>
                        <span class="detail-value">{{ $studentNumber ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Programme</span>
                        <span class="detail-value">{{ $programme ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Cohort</span>
                        <span class="detail-value">{{ $cohort ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Year</span>
                        <span class="detail-value">{{ $year ?? 'N/A' }}</span>
                    </div>
                    @if($validUntil)
                    <div class="detail-row">
                        <span class="detail-label">Valid Until</span>
                        <span class="detail-value detail-value-valid">{{ $validUntil }}</span>
                    </div>
                    @endif
                </div>

                <!-- Photo & QR -->
                <div class="media-col">
                    <div class="photo-frame">
                        @if($photoPath)
                            <img class="photo" src="{{ $photoPath }}" alt="Student photo">
                        @else
                            <div class="initials-fallback">{{ $initials }}</div>
                        @endif
                    </div>

                    <div class="qr-row">
                        @if($qrCode)
                            <img class="qr-code" src="{{ $qrCode }}" alt="QR Code">
                        @endif
                        <span class="qr-label">Scan to<br>verify</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-accent"></div>
    </div>

    <!-- BACK -->
    <div class="card-back">
        <div class="magnetic-stripe"></div>

        <div class="back-content">
            <div class="signature-section">
                <div class="signature-label">Holder's Signature</div>
                <div class="signature-line"></div>
            </div>

            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value">{{ $email ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Issued</span>
                <span class="info-value">{{ $year ?? 'N/A' }}</span>
            </div>
            @if($validUntil)
            <div class="info-row">
                <span class="info-label">Expires</span>
                <span class="info-value info-value-accent">{{ $validUntil }}</span>
            </div>
            @endif

            <div class="barcode-section">
                <div class="barcode">
                    <div class="barcode-bars">
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 1pt;"></div>
                        <div class="barcode-bar" style="width: 2pt;"></div>
                    </div>
                    <span class="barcode-number">{{ $studentNumber ?? 'N/A' }}</span>
                </div>
                <span class="institute-footer">HBCI</span>
            </div>
        </div>

        <div class="back-footer-accent"></div>
    </div>
</body>
</html>
