<!DOCTYPE HTML>

<html>
    <head>
        <style type="text/css">
        body {
      
        }
            .bodyBody {
                font-family: Arial;
                font-size: 11px;
                background-image: url("https://transcriptapp.run.edu.ng/assets/images/original.jpg");
                background-size: contain;
                background-repeat: no-repeat;

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