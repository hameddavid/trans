<!DOCTYPE HTML>

<html>
    <head>
        <style type="text/css">
            .bodyBody {
                margin: 10px;
                font-size: 1.50em;
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
        </style>
    </head>
    <body class="bodyBody">
        <div class="divSubject">
<pre>
                                                                                    {{date("F j, Y")}}    
RUN/REG/Attestation/15/Vol.1/00{{$data->application_id}}                                                                                                      

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
                <p>@if(strtoupper($data->sex) == 'M') <b>{{'MR.'}}</b>
                    @elseif(strtoupper($data->sex) == 'F') <b>{{'MISS'}}</b>
                    @else <b>{{''}}</b>
                    @endif
                 {{$data->surname.' '.$data->firstname}} (Matric. No. {{$data->matric_number}}) 
                was a student in the Department of {Finance (Banking and Finance Programme)}
                 in the Faculty of {Management Sciences}, Redeemer’s University.</p>

    

                <p>{Her} Cumulative Grade Point Average (CGPA) at the end of a {4-year} {Bachelor of Science} degree programme, in the {2009/2010} academic session, in {Psychology} was {3.52} – {Second Class (Honours) Upper Division}.</p>

                <p>Kindly note that English is the medium of communication in Nigerian institutions. You may also wish to note that in Redeemer’s University, all lectures, examinations, tests,
                    Seminars, presentations, and all kinds of student assessments are conducted in English.</p>

                <p>Please accord her the necessary assistance.</p>

                Yours faithfully,

            </p>
        </div>

        <div class="divAdios">
            MISS ADETUTU ADEWOLE<br>
            Administrative Officer, Academic Affairs<br>
            For:  REGISTRAR
        </div>
    </body>
</html>