<!DOCTYPE HTML>

<html>
    <head>
        <style type="text/css">
            html {
            margin:0;
            padding:0; 
            }
            @page {
                size: A4;
                margin-top:0.5cm;
                margin-bottom:0;
                margin-left:0;
                margin-right:0;
                padding: 0;
            }
            
            .bodyBody {
                /* 
                background-image: url('/www/wwwroot/trans/public/assets/images/original.jpg');
                 */
                background-image: url("https://records.run.edu.ng/assets/images/original.png");
                background-size: contain;
                background-repeat: no-repeat;
                font-family: Arial;
                font-size: 11px;

            }
            .divHeader {
                text-align: right;
                border: 1px solid;
            }
            .divReturnAddress {
                text-align: left;
                float: right;
            }
            .divSubject {
                clear: both;
                font-weight: bold;
                padding-top: 80px;
            }
            .divAdios {
                float: left;
                padding-top: 50px;
            }
            .main{
                margin: 20% auto;
                padding-top: 5px;
                padding-right: 30px; 
                padding-bottom: 15px; 
                padding-left: 30px; 
            }
        </style>
    </head>
    <body class="bodyBody">
            <div class="main"> 
            <div class="divSubject">
<pre>
{{date("F j, Y")}}  
 
RUN/REG/Attestation/15/Vol.1/00{{$data->id}}                                                                                                    

TO WHOM IT MAY CONCERN
</pre>
        </div>


        <div class="divContents" align="justify">
            <p>
                Dear Sir,
            </p>
            <h5>
                <u>LETTER OF ATTESTATION<br>
                    LANGUAGE OF INSTRUCTION/PROFICIENCY IN ENGLISH                    
                </u>
            </h5>
            
            <p>
                <p>@if(strtoupper($data->sex) == 'M') {{'Mr.'}}
                    @elseif(strtoupper($data->sex) == 'F') {{'Miss'}}
                    @else <b>{{''}}</b>
                    @endif
                <b>{{ strtoupper($data->surname)}}</b> {{' '. ucwords(strtolower($data->firstname))}} (Matric. No. {{$data->matric_number}}) 
                was a student in the Department of {{ucwords(strtolower($data->dept))}} ({{ucwords(strtolower($data->prog_name))}} Programme)
                 in the Faculty of {{ucwords(strtolower($data->fac))}}, Redeemer’s University.</p>

                <p>
                @if(strtoupper($data->sex) == 'M') {{'His'}}
                @elseif(strtoupper($data->sex) == 'F') {{'Her'}}
                @else <b>{{''}}</b>
                @endif
                 Cumulative Grade Point Average (CGPA) at the end of a {{$data->years_spent.'-year(s)'}}
                 {{ucwords(strtolower($data->qualification))}} degree programme, 
                 in the {{$data->last_session_in_sch}} 
                academic session, in {{ucwords(strtolower($data->prog_name))}} was {{$data->cgpa}} – {{ucwords(strtolower($data->class_of_degree))}}. 
                    <br><br></p>

                <p>Kindly note that English is the medium of communication in Nigerian institutions. You may also wish to note that in Redeemer’s University, all lectures, examinations, tests,
                    Seminars, presentations, and all kinds of student assessments are conducted in English.</p>

                <p>Please accord  @if(strtoupper($data->sex) == 'M') {{'his'}}
                @elseif(strtoupper($data->sex) == 'F') {{'her'}}
                @else <b>{{''}}</b>
                @endif the necessary assistance.</p>

                Yours faithfully,

            </p>
        </div>

        <div class="divAdios">
            MISS ADETUTU ADEWOLE<br>
            Administrative Officer, Academic Affairs<br>
            For:  REGISTRAR
        </div>
            </div>
    </body>
</html>