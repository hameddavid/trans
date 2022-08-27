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
                font-family: Arial;
                font-size: 11px;
                background-image: url("https://records.run.edu.ng/assets/images/original.jpg");
                /* background-image: url('/www/wwwroot/trans/public/assets/images/original.jpg'); */
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
RUN/REG/Acad/Stud.Trscpt/53/Vol.12/00{{$data->application_id}}    
                                                                                            
{{$data->address}}  
</pre>
<pre><p>Verification Code: {{$data->used_token}} </p> </pre>
        </div>

        <div class="divContents" align="justify">
            <p>
                Dear Sir,
            </p>
            <h5>
                <u>TRANSCRIPT OF 
                    @if(strtoupper($data->sex) == 'M') <b>{{'MR.'}}</b>
                    @elseif(strtoupper($data->sex) == 'F') <b>{{'MISS'}}</b>
                    @else <b>{{''}}</b>
                    @endif
                     {{$data->surname.' '.$data->firstname}} AND ATTESTATION TO PROFICIENCY IN ENGLISH LANGUAGE<br>
                    @if($data->reference) {{'REFERENCE NUMBER: '.$data->reference}}
                    @endif
                </u>
            </h5>
            
            <p>   

                <p>Please find attached herewith, the transcript of 
                @if(strtoupper($data->sex) == 'M') {{'Mr.'}}
                    @elseif(strtoupper($data->sex) == 'F') {{'Miss'}}
                    @else <b>{{''}}</b>
                    @endif
                     <b>{{ strtoupper($data->surname)}}</b> {{' '. ucwords(strtolower($data->firstname))}} (Matric. No. {{$data->matric_number}}).</p>
                    
                <p> @if(strtoupper($data->sex) == 'M') {{'His'}}
                    @elseif(strtoupper($data->sex) == 'F') {{'Her'}}
                    @else <b>{{''}}</b>
                    @endif
                    Cumulative Grade Point Average (CGPA) at the end of a {{$data->years_spent.'-year(s)'}} 
                     {{ucwords(strtolower($data->qualification))}} degree programme, in the {{$data->last_session_in_sch}} 
                     academic session, was {{$data->cgpa}} – {{$data->class_of_degree}}. 
                    <br><br>The official language of teaching and examining the course was English.</p>

                <p>Kindly note that any alteration on the transcript renders the records invalid and that the transcript is being forwarded in strict confidence and under no circumstance should it be released to the applicant.</p>

                <p>I hope you will find the academic records useful.</p>

                Yours faithfully,

            </p>
            <pre><p>To verify the authenticity of this document visit https://records.run.edu.ng </p> </pre>
        </div>

        <div class="divAdios">
            D. K. T. Akintola<br>
            Deputy Registrar, Academic Affairs<br>
            For:  REGISTRAR
        </div>
    </body>
</html>