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

RUN/REG/Acad/Verifi/63/Vol.2/00{{$data->id}}                                                                                                         
{{$data->institution_address}} 

Attention: {{$data->institution_name}} 
</pre>
        </div>

        <div class="divContents" align="justify">
            <p>
                Dear Sir,
            </p>
            <h5>
                <u>RE: REFERENCE REQUEST FOR {{$data->surname.' '.$data->firstname.' '.$data->othername}} [{{$data->matno_found}}]</u>
            </h5>
            
            <p>
                <p>I write to acknowledge receipt of your request dated {{$data->created_at}}
                 in connection with the above-mentioned subject and verify 
                 that the under-mentioned person was admitted to the Redeemer’s University 
                 to study for a degree course leading to the award of {{$data->qualification}}
                 as summarised below:</p>


                <p>
                    <table class="table">
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
                                <td>{{$data->surname.' '.$data->firstname.' '.$data->othername}}</td>
                                <td>{{$data->yr_of_adms}}</td>
                                <td>{{$data->program}}</td>
                                <td>{{$data->class_of_degree}}</td>
                                <td>{{$data->grad_year}}</td>
                            </tr>
                        </tbody>
                    </table>
                </p>

                <p>I hope you will find the above information useful.</p>

                Yours faithfully,

            </p>
        </div>

        <div class="divAdios">
            D. K. T. Akintola<br>
            Deputy Registrar, Academic Affairs<br>
            For:  REGISTRAR
        </div>
    </body>
</html>