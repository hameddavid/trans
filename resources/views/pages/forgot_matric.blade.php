@extends("layout.master") 

    @section("title")
      Forgot Matric Number
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Applicants (Forgot Matric Number)</h4>
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
                                                    <th>Surname</th>
                                                    <th>Firstname</th>
                                                    <th>Othername</th>
                                                    <th>Programme</th>
                                                    <th>Graduation Year</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach($applicants as $applicant)
                                                <tr>
                                                    <td>{{$i}} @php $i++@endphp</td>
                                                    <td>{{$applicant->surname}}</td>
                                                    <td>{{$applicant->firstname}}</td>
                                                    <td>{{$applicant->othername}}</td>
                                                    <td>{{$applicant->program}}</td>
                                                    <td>{{$applicant->date_left}}</td>
                                                    <td>@php echo ($applicant->status == 'TREATED') ? '<span class="badge badge-soft-success">'.$applicant->status.'</span>' : '<span class="badge badge-soft-danger">'.$applicant->status.'</span>'@endphp</td>
                                                    <td>
                                                        <div class="btn-group btn-group-example mb-3" role="group">
                                                            @if($applicant->status == 'PENDING')
                                                            <button type="button" data-suggestions="{{$applicant->matno_found}}" data-date_left="{{$applicant->date_left}}" data-program="{{$applicant->program}}" data-email="{{$applicant->email}}" data-othername="{{$applicant->othername}}" data-phone="{{$applicant->phone}}" data-firstname="{{$applicant->firstname}}" data-surname="{{$applicant->surname}}" title="View" class="btn btn-secondary w-xs viewForgotMatric">
                                                                <i class="bx bx-show-alt"></i> View
                                                            </button>
                                                            @else
                                                            <button type="button" disabled class="btn btn-secondary w-xs">
                                                                <i class="bx bx-show-alt"></i> View
                                                            </button>
                                                            @endif
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

            <div class="modal fade" id="forgotMatric" tabindex="-1" aria-labelledby="forgotMatricLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header border-primary">
                            <h5 class="my-0 text-primary"><i class="mdi mdi-bullseye-arrow me-3"></i>Send Matric Number</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="col-lg-12">
                            <div class="card border border-primary">
                                <div class="card-body">
                                    <p class="card-text">Select the correct matric number for this applicant and click on the send button.</p>
                                    <label for="name" class="col-form-label">Fullname: <span id="name"></span></label><hr>
                                    <label for="email" class="col-form-label">Email: <span id="email"></span></label><hr>
                                    <label for="phone" class="col-form-label">Phone number: <span id="phone"></span></label><hr>
                                    <label for="program" class="col-form-label">Programme: <span id="program"></span></label><hr>
                                    <label for="graduation" class="col-form-label">Year of Graduation: <span id="graduation"></span></label>
                                </div>
                            </div>
                        </div>
                        <form method="POST" id="sendMatricForm">
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
                                <button type="submit" id="btnSendMatric" class="btn btn-success">Send</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        
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

        