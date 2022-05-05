@extends("layout.master") 

    @section("title")
      Pending Transcript Requests
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Pending Transcript Requests</h4>
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
                                                    <th>Recipient</th>
                                                    <th>Destination</th>
                                                    <th>Type</th>
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
                                                    <td>{{$app->surname.' '.$app->firstname}}</td>
                                                    <td>{{$app->matric_number}}</td>
                                                    <td>{{$app->recipient}}</td>
                                                    <td>{{$app->destination}}</td>
                                                    <td>{{$app->transcript_type}}</td>
                                                    <td><span class="badge badge-soft-warning">{{$app->app_status}}</span></td>
                                                    <td>{{ date("d M Y", strtotime($app->created_at)) }}</td>
                                                    <td>
                                                        <div class="dropdown align-self-start">
                                                            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="bx bx-dots-horizontal-rounded font-size-18 text-dark"></i>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                <div class="btn-group btn-group-example mb-3" role="group">
                                                                    <button type="button" title="View" class="btn btn-secondary w-xs"><i class="mdi mdi-eye-check-outline"></i></button>
                                                                    <button type="button" title="Regenerate" class="btn btn-info w-xs"><i class="mdi mdi-refresh"></i></button>
                                                                </div>
                                                                <div class="btn-group btn-group-example mb-3" role="group">
                                                                    <button type="button" title="Approve" class="btn btn-success w-xs"><i class="mdi mdi-thumb-up"></i></button>
                                                                    <button type="button" title="Disapprove" class="btn btn-danger w-xs"><i class="mdi mdi-thumb-down"></i></button>
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

        
        <!-- Required datatable js -->
        <script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="assets/libs/jszip/jszip.min.js"></script>
        <script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <!-- Responsive examples -->
        <script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="assets/js/pages/datatables.init.js"></script>    
    @endsection

        