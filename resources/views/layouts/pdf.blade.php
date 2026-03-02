<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Document')</title>
    <style>
        @page {
            margin: 1.5cm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        
        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 3px solid #FF6B35;
            padding-bottom: 15px;
        }
        
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 8px;
        }
        
        .company-info {
            font-size: 8pt;
            color: #666;
            line-height: 1.5;
        }
        
        .document-title {
            font-size: 22pt;
            font-weight: bold;
            color: #FF6B35;
            margin-bottom: 5px;
        }
        
        .document-number {
            font-size: 11pt;
            color: #666;
        }
        
        .document-meta {
            margin-top: 15px;
            font-size: 10pt;
        }
        
        /* Sections */
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #004E89;
            margin-bottom: 8px;
            border-bottom: 1px solid #004E89;
            padding-bottom: 4px;
        }
        
        .client-section {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        table thead {
            background-color: #004E89;
        }
        
        table th {
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: 600;
        }
        
        table td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 9pt;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Totals */
        .totals-wrapper {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        
        .totals-spacer {
            display: table-cell;
            width: 50%;
        }
        
        .totals-section {
            display: table-cell;
            width: 50%;
        }
        
        .total-row {
            display: table;
            width: 100%;
            padding: 4px 0;
        }
        
        .total-label {
            display: table-cell;
            text-align: right;
            padding-right: 15px;
            font-size: 9pt;
        }
        
        .total-value {
            display: table-cell;
            text-align: right;
            width: 130px;
            font-size: 9pt;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #004E89;
            margin-top: 8px;
            padding-top: 8px;
        }
        
        .total-row.grand-total .total-label,
        .total-row.grand-total .total-value {
            font-size: 12pt;
            font-weight: bold;
            color: #FF6B35;
        }
        
        /* Amount in words */
        .amount-in-words {
            margin-top: 25px;
            padding: 12px;
            background-color: #fff8f5;
            border-left: 4px solid #FF6B35;
            font-style: italic;
            font-size: 9pt;
        }
        
        /* Notes */
        .notes-section {
            margin-top: 20px;
            padding: 12px;
            background-color: #fffde7;
            border: 1px solid #ffd54f;
            border-radius: 4px;
            font-size: 9pt;
        }
        
        /* Status badges */
        .status-paid {
            margin-top: 15px;
            padding: 10px;
            background-color: #d4edda;
            border: 2px solid #28a745;
            text-align: center;
            font-weight: bold;
            color: #155724;
        }
        
        .status-partial {
            margin-top: 15px;
            padding: 10px;
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            text-align: center;
            color: #856404;
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 70pt;
            color: rgba(0, 0, 0, 0.04);
            z-index: -1;
            white-space: nowrap;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 1.5cm;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #888;
        }
        
        /* Signature */
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 45%;
            padding: 15px;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 50px;
            margin-bottom: 5px;
        }
        
        .signature-label {
            font-size: 9pt;
            color: #666;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Utilities */
        .fw-bold {
            font-weight: bold;
        }
        
        .mb-0 {
            margin-bottom: 0;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
    </style>
    @yield('styles')
</head>
<body>
    @if(isset($watermark))
        <div class="watermark">{{ $watermark }}</div>
    @endif
    
    @yield('content')
    
    <div class="footer">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0; text-align: left;">
                    {{ $company->name ?? config('app.name') }}
                </td>
                <td style="border: none; padding: 0; text-align: center;">
                    Généré le {{ now()->format('d/m/Y à H:i') }}
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    @yield('footer-right', 'Page 1/1')
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
