<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transcript</title>
    <style>
        @page {
            size: A4;
            margin: 0.8cm 0.5cm 0.5cm 0.5cm;
        }
        body, html {
            width: 100%;
            height: auto;
            background-color: #ffffff;
            padding: 0;
            margin: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 10px;
            color: #1a1a1a;
        }
        body {
            background-image: url("{{ public_path('assets/images/metal_logo.png') }}");
            background-repeat: no-repeat;
            background-size: cover;
        }
        .page {
            width: 100%;
            page-break-after: always;
        }
        .header {
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            padding: 8px 0 10px 0;
            width: 100%;
            border-bottom: 2px solid #1a3a6b;
            margin-bottom: 0;
        }
        .header h1, .header h2, .header h3, .header h4, .header h5, .header h6, .header p {
            padding: 0;
            margin: 0;
        }
        .header h1 {
            font-size: 22px;
            color: #1a3a6b;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 16px;
            color: #1a3a6b;
            margin-top: 2px;
        }
        .header h5 {
            font-style: italic;
            font-size: 10px;
            color: #444;
            font-weight: normal;
        }
        .header h6 {
            font-style: italic;
            font-size: 8px;
            color: #777;
            font-weight: normal;
            margin-top: 3px;
        }
        #recipient_h {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-top: 4px;
        }
        .logo {
            float: left;
            height: 90px;
            margin-left: 5%;
            margin-top: 0;
        }
        .gold-accent {
            width: 100%;
            height: 3px;
            background-color: #977b1f;
            margin-bottom: 0;
        }
        .header2 {
            border-bottom: 1px solid #999;
            width: 100%;
            padding: 6px 0;
        }
        .header2 table {
            width: 90%;
            margin-left: 5%;
            margin-right: 5%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        .header2 td {
            padding: 2px 0;
        }
        .result_table {
            border-collapse: collapse;
            width: 90%;
            margin-left: 5%;
            margin-right: 5%;
            font-size: 10px;
            font-family: Georgia, 'Times New Roman', serif;
        }
        .result_table th, .result_table td {
            padding: 3px 5px;
            font-weight: normal;
            border: 1px solid #ccc;
        }
        .result_table th {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            font-size: 9px;
            background-color: #f0f3f7;
            color: #1a3a6b;
            padding: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .result_table2 {
            border-collapse: collapse;
            width: 90%;
            margin-left: 5%;
            margin-right: 5%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        .result_table2 td {
            padding: 3px 5px;
            font-weight: normal;
            border: 1px solid #ddd;
        }
        .result_table2 caption {
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            padding-top: 12px;
            font-weight: bold;
            color: #1a3a6b;
        }
        caption {
            text-align: left;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            padding-top: 14px;
            font-weight: bold;
            color: #333;
        }
        .semester-label {
            border: none !important;
            padding: 14px 5px 4px 0 !important;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }
        .summary-divider {
            border-top: 1px dotted #999;
            margin: 8px 5% 0 5%;
        }
        .footer_ {
            width: 90%;
            margin-left: 5%;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 11px;
            font-style: italic;
            margin-top: 20px;
        }
        .footer_3 {
            width: 90%;
            margin-left: 5%;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 11px;
            font-style: italic;
            margin-top: 30px;
        }
        .footer_4 {
            font-size: 9px;
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
            color: #888;
            padding: 20px 5%;
            border-top: 1px solid #ddd;
            margin-top: 25px;
        }
        @media print {
            .page {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    {!! $data1 !!}
</body>
</html>
