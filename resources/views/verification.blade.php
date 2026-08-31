<!DOCTYPE HTML>
<html>
<head>
    <style type="text/css">
        @page {
            size: A4;
            margin: 0;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.6;
            background-image: url("{{ public_path('assets/images/original.png') }}");
            background-size: contain;
            background-repeat: no-repeat;
        }
        .content {
            margin: 0;
            padding: 200px 55px 50px 55px;
        }
        .meta-block {
            margin-bottom: 8px;
            font-size: 11.5px;
            line-height: 1.7;
        }
        .meta-date {
            font-weight: bold;
            font-size: 12px;
        }
        .meta-ref {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #555;
        }
        .meta-address {
            margin-top: 5px;
        }
        .attention {
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
        }
        .salutation {
            margin-bottom: 5px;
        }
        .subject-line {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            line-height: 1.6;
            margin: 15px 0;
        }
        .body-text {
            text-align: justify;
            line-height: 1.8;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 15px 0;
        }
        .info-table th {
            background-color: #f0f3f7;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            font-weight: bold;
            color: #1a3a6b;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #ccc;
        }
        .info-table td {
            padding: 6px 8px;
            border: 1px solid #ccc;
        }
        .closing {
            margin-top: 10px;
            margin-bottom: 0;
        }
        .signatory {
            margin-top: 35px;
            line-height: 1.5;
        }
        .sign-line {
            width: 180px;
            border-top: 1px solid #333;
            margin-bottom: 4px;
        }
        .sign-name {
            font-weight: bold;
            font-size: 12px;
        }
        .sign-title {
            font-size: 11px;
            color: #444;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="meta-block">
            <span class="meta-date">{{ date("F j, Y") }}</span><br>
            <span class="meta-ref">RUN/REG/Acad/Verifi/63/Vol.2/00{{ $data->id }}</span>
        </div>

        <div class="meta-block meta-address">
            {{ $data->institution_address }}
        </div>

        <p class="attention">Attention: {{ $data->institution_name }}</p>

        <p class="salutation">Dear Sir/Madam,</p>

        <div class="subject-line">
            RE: Reference Request for {{ $data->surname . ' ' . $data->firstname . ' ' . $data->othername }}
            [{{ $data->matno_found }}]
        </div>

        <p class="body-text">
            I write to acknowledge receipt of your request dated {{ $data->created_at }}
            in connection with the above-mentioned subject and verify that the under-mentioned
            person was admitted to the Redeemer's University to study for a degree course leading
            to the award of {{ $data->qualification }} as summarised below:
        </p>

        <table class="info-table">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Year of Admission</th>
                    <th>Course of Study</th>
                    <th>Class of Degree</th>
                    <th>Year of Graduation</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>{{ $data->surname . ' ' . $data->firstname . ' ' . $data->othername }}</td>
                    <td>{{ $data->yr_of_adms }}</td>
                    <td>{{ $data->program }}</td>
                    <td>{{ $data->class_of_degree }}</td>
                    <td>{{ $data->grad_year }}</td>
                </tr>
            </tbody>
        </table>

        <p class="body-text">I hope you will find the above information useful.</p>

        <p class="closing">Yours faithfully,</p>

        <div class="signatory">
            <div class="sign-line"></div>
            @if(!empty($signatory['signature_path']))
                <img src="{{ $signatory['signature_path'] }}" style="height: 45px; margin-bottom: 4px;">
            @endif
            <span class="sign-name">{{ $signatory['name'] }}</span><br>
            <span class="sign-title">{{ $signatory['title'] }}</span><br>
            <span class="sign-title">For: {{ $signatory['for'] }}</span>
        </div>
    </div>
</body>
</html>
