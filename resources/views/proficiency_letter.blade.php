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
        .addressed-to {
            font-weight: bold;
            font-size: 12px;
            margin-top: 15px;
            text-transform: uppercase;
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
    </style>
</head>
<body>
    <div class="content">
        <div class="meta-block">
            <span class="meta-date">{{ date("F j, Y") }}</span><br>
            <span class="meta-ref">RUN/REG/Attestation/15/Vol.1/00{{ $data->id }}</span>
        </div>

        <p class="addressed-to">To Whom It May Concern</p>

        <p class="salutation">Dear Sir/Madam,</p>

        <div class="subject-line">
            Letter of Attestation<br>
            Language of Instruction / Proficiency in English
        </div>

        <p class="body-text">
            @if(strtoupper($data->sex) == 'M') Mr.
            @elseif(strtoupper($data->sex) == 'F') Miss
            @endif
            <strong>{{ strtoupper($data->surname) }}</strong> {{ ucwords(strtolower($data->firstname)) }}
            (Matric. No. {{ $data->matric_number }}) was a student in the Department of
            {{ ucwords(strtolower($data->dept)) }} ({{ ucwords(strtolower($data->prog_name)) }} Programme)
            in the Faculty of {{ ucwords(strtolower($data->fac)) }}, Redeemer's University.
        </p>

        <p class="body-text">
            @if(strtoupper($data->sex) == 'M') His
            @elseif(strtoupper($data->sex) == 'F') Her
            @endif
            Cumulative Grade Point Average (CGPA) at the end of a {{ $data->years_spent }}-year(s)
            {{ ucwords(strtolower($data->qualification)) }} degree programme,
            in the {{ $data->last_session_in_sch }} academic session,
            in {{ ucwords(strtolower($data->prog_name)) }} was {{ $data->cgpa }}
            &ndash; {{ ucwords(strtolower($data->class_of_degree)) }}.
        </p>

        <p class="body-text">
            Kindly note that English is the medium of communication in Nigerian institutions.
            You may also wish to note that in Redeemer's University, all lectures, examinations, tests,
            seminars, presentations, and all kinds of student assessments are conducted in English.
        </p>

        <p class="body-text">
            Please accord
            @if(strtoupper($data->sex) == 'M') him
            @elseif(strtoupper($data->sex) == 'F') her
            @endif
            the necessary assistance.
        </p>

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
