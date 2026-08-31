<!DOCTYPE HTML>
<html>
<head>
    <style type="text/css">
        @page {
            size: A4;
            margin: 1.5cm 2cm 1.5cm 2cm;
        }
        html, body {
            margin: 0;
            padding: 0;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.6;
        }
        .letterhead {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1a3a6b;
            margin-bottom: 0;
        }
        .letterhead td {
            vertical-align: middle;
            padding: 0;
        }
        .logo {
            height: 75px;
        }
        .uni-name {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 22px;
            font-weight: bold;
            color: #1a3a6b;
            margin: 0 0 2px 0;
        }
        .uni-office {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: bold;
            color: #977b1f;
            margin: 0 0 4px 0;
        }
        .uni-detail {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #555;
            margin: 0;
            line-height: 1.5;
        }
        .gold-bar {
            width: 100%;
            height: 3px;
            background-color: #977b1f;
            margin-bottom: 30px;
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
        .verification-box {
            background-color: #f7f6f1;
            border-left: 3px solid #977b1f;
            padding: 6px 12px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 15px 0 20px 0;
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
        .doc-footer {
            text-align: center;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 45px;
        }
    </style>
</head>
<body>
    <table class="letterhead" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 85px; padding-right: 12px;">
                <img src="{{ public_path('assets/images/run_logo_big.png') }}" class="logo">
            </td>
            <td>
                <p class="uni-name">REDEEMER'S UNIVERSITY</p>
                <p class="uni-office">OFFICE OF THE REGISTRAR</p>
                <p class="uni-detail">P.M.B. 230, Ede, Osun State, Nigeria</p>
                <p class="uni-detail">Tel: +234 902 859 5221 &nbsp;|&nbsp; Email: transcripts@run.edu.ng &nbsp;|&nbsp; Website: run.edu.ng</p>
            </td>
        </tr>
    </table>
    <div class="gold-bar"></div>

    <div class="meta-block">
        <span class="meta-date">{{ date("F j, Y") }}</span><br>
        <span class="meta-ref">RUN/REG/Acad/Stud.Trscpt/53/Vol.12/00{{ $data->application_id }}</span>
    </div>

    <div class="meta-block meta-address">
        {{ $data->address }}
    </div>

    <div class="verification-box">
        Verification Code: <strong>{{ $data->used_token }}</strong>
    </div>

    <p class="salutation">Dear Sir/Madam,</p>

    <div class="subject-line">
        TRANSCRIPT OF
        @if(strtoupper($data->sex) == 'M') MR.
        @elseif(strtoupper($data->sex) == 'F') MISS
        @endif
        {{ $data->surname . ' ' . $data->firstname }} AND ATTESTATION TO PROFICIENCY IN ENGLISH LANGUAGE
        @if($data->reference)
            <br>REFERENCE NUMBER: {{ $data->reference }}
        @endif
    </div>

    <p class="body-text">
        Please find attached herewith, the transcript of
        @if(strtoupper($data->sex) == 'M') Mr.
        @elseif(strtoupper($data->sex) == 'F') Miss
        @endif
        <strong>{{ strtoupper($data->surname) }}</strong> {{ ucwords(strtolower($data->firstname)) }}
        (Matric. No. {{ $data->matric_number }}).
    </p>

    <p class="body-text">
        @if(strtoupper($data->sex) == 'M') His
        @elseif(strtoupper($data->sex) == 'F') Her
        @endif
        Cumulative Grade Point Average (CGPA) at the end of a {{ $data->years_spent }}-year(s)
        {{ ucwords(strtolower($data->qualification)) }} degree programme, in the {{ $data->last_session_in_sch }}
        academic session, was {{ $data->cgpa }} &ndash; {{ $data->class_of_degree }}.
    </p>

    <p class="body-text">
        The official language of teaching and examining the course was English.
    </p>

    <p class="body-text">
        Kindly note that any alteration on the transcript renders the records invalid and that the transcript is being forwarded in strict confidence and under no circumstance should it be released to the applicant.
    </p>

    <p class="body-text">I hope you will find the academic records useful.</p>

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

    <div class="doc-footer">
        To verify the authenticity of this document, visit <strong>https://records.run.edu.ng</strong><br>
        Redeemer's University, Ede, Osun State, Nigeria &nbsp;&bull;&nbsp; <em>...running with a vision</em>
    </div>
</body>
</html>
