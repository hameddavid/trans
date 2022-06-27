@extends("layout.master") 

    @section("title")
      Applicants
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Applicants</h4>
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
                                                    <th>Name</th>
                                                    <th>Matric Number</th>
                                                    <th>Email</th>
                                                    <th>Phone number</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach($applicants as $applicant)
                                                <tr>
                                                    <td>{{$i}} @php $i++@endphp</td>
                                                    <td>{{$applicant->surname.' '.$applicant->firstname}}</td>
                                                    <td>{{$applicant->matric_number}}</td>
                                                    <td>{{$applicant->email}}</td>
                                                    <td>{{$applicant->mobile}}</td>
                                                    <td>{{ date("d M Y", strtotime($applicant->created_at)) }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-example mb-3" role="group">
                                                            <button type="button" data-email="{{$applicant->email}}" data-matric="{{$applicant->matric_number}}" data-phone="{{$applicant->mobile}}" data-othernames="{{$applicant->firstname}}" data-surname="{{$applicant->surname}}" title="Edit" class="btn btn-secondary w-xs editApplicant">
                                                                <i class="bx bx-edit-alt"></i> Edit
                                                            </button>
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

            <div class="modal fade" id="applicantModal" tabindex="-1" aria-labelledby="applicantModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="applicantModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" id="editApplicantForm">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="surname" class="col-form-label">Surname:</label>
                                    <input type="text" class="form-control" name="surname" id="surname" required>
                                </div>
                                <div class="mb-3">
                                    <label for="othernames" class="col-form-label">Othernames:</label>
                                    <input type="text" class="form-control" name="othernames" id="othernames" required>
                                </div>
                                <div class="mb-3">
                                    <label for="matric" class="col-form-label">Matric number:</label>
                                    <input type="text" class="form-control" name="matric" id="matric" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="col-form-label">Email:</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="col-form-label">Phone number:</label>
                                    <input type="tel" class="form-control" name="phone" id="phone" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" id="btnEdit" class="btn btn-danger">Update</button>
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

        