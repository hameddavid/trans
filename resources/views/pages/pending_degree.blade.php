@extends("layout.master") 

    @section("title")
      Pending Degree Verification Requests
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Pending Degree Verification Requests</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <table id="datatable-buttons" class="table table-bordered dt-responsive nowrap w-100">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Institution Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Address</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach($apps as $app)
                                                <tr>
                                                    <td>{{$i}} @php $i++@endphp</td>
                                                    <td>{{$app->institution_name}}</td>
                                                    <td>{{$app->institution_email}}</td>
                                                    <td>{{$app->institution_phone}}</td>
                                                    <td>{{$app->institution_address}}</td>
                                                    <td><span class="badge badge-soft-warning">{{$app->status}}</span></td>
                                                    <td>{{ date("d M Y", strtotime($app->created_at)) }}</td>
                                                    <td>
                                                        <div class="dropdown align-self-start">
                                                            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="bx bx-dots-horizontal-rounded font-size-18 text-dark"></i>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                <div class="btn-group mb-3">
                                                                    <button type="button" data-suggestions="{{$app->matno_found}}" data-id="{{$app->id}}" data-grad="{{$app->grad_year}}" data-programme="{{$app->program}}" data-name="{{$app->surname.' '.$app->firstname.' '.$app->othername}}" class="btn btn-secondary waves-effect btn-label waves-light view_verification"><i class="bx bx-show-alt label-icon"></i>View App</button>
                                                                    @if($app->status=='TREATED')
                                                                        <a href="view_treated_degree_verification/{{$app->id}}" target="_blank" type="button" class="btn btn-primary waves-effect btn-label waves-light p-3"><i class="bx bx-show-alt label-icon"></i>View File</a>
                                                                    @endif
                                                                </div>
                                                                <div class="btn-group btn-group-example mb-3 p-3" role="group">
                                                                    @if($data->role == 200)<button type="button" data-id="{{$app->id}}" title="Recommend" class="btn btn-success w-xs recommend_verification"><i class="mdi mdi-thumb-up"></i></button>@endif
                                                                    @if($data->role == 200)<button type="button" title="Disapprove" class="btn btn-danger w-xs"><i class="mdi mdi-thumb-down"></i></button>@endif
                                                                </div> 
                                                            </div>
                                                        </div>                                                        
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- end cardaa -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                @include("partials.footer")
            </div>
            <!-- end main content-->

            <!-- Verification modal -->
            <div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header border-primary">
                            <h5 class="my-0 text-primary"><i class="mdi mdi-bullseye-arrow me-3"></i>Degree Verification</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="col-lg-12">
                            <div class="card border border-primary">
                                <div class="card-body">
                                    <p class="card-text">Select the correct matric number for this student and click on the Generate button.</p>
                                    <label for="name" class="col-form-label">Fullname: <span id="name"></span></label><hr>
                                    <label for="program" class="col-form-label">Programme: <span id="program"></span></label><hr>
                                    <label for="graduation" class="col-form-label">Year of Graduation: <span id="graduation"></span></label>
                                </div>
                            </div>
                        </div>
                        <form method="POST" id="verificationForm">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="matric_number" class="col-form-label">Matric Number: <span style="color:red">(Based on our suggestion)</span></label>
                                    <select class="form-control select_matric_number" name="matric_number" id="matric_number" required>
                                    </select>
                                    <input type="text" class="form-control matric_number"  name="matric_number_" id="matric_number_" 
                                        placeholder="No suggestions, Please enter matric number here" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" id="btnverification" class="btn btn-success">Generate</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>            
            <!-- /modal -->

        
        <!-- Required datatable js -->
        <script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="../assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="../assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="../assets/libs/jszip/jszip.min.js"></script>
        <script src="../assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="../assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="../assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="../assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="../assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <!-- Responsive examples -->
        <script src="../assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="../assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="../assets/js/pages/datatables.init.js"></script> 
        <script src="../assets/js/pages/modal.init.js"></script>
        <script src="../assets/js/validation.min.js"></script>
        <script src="../assets/js/utils.js"></script>   
    @endsection

        