@extends("layout.master") 

    @section("title")
      Failed Transcript Requests
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Failed Transcript Requests</h4>
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
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $i = 1; @endphp
                                                @foreach($apps as $app)
                                                <tr>
                                                    <td>{{$i}} @php $i++@endphp</td>
                                                    <td><button data-certificate="{{$app->certificate}}" data-status="{{$app->app_status}}" data-reference="{{$app->reference}}" data-recipient="{{$app->recipient}}" data-mode="{{$app->delivery_mode}}" data-address="{{$app->address}}" data-id="{{$app->application_id}}" data-name="{{$app->surname.' '.$app->firstname}}" class="btn btn-primary preview">{{$app->surname.' '.$app->firstname}}</a></td>
                                                    <td>{{$app->matric_number}}</td>
                                                    <td>{{$app->recipient}}</td>
                                                    <td>{{$app->destination}}</td>
                                                    <td>{{$app->transcript_type}}</td>
                                                    <td><span class="badge badge-soft-danger">{{$app->app_status}}</span></td>
                                                    <td>{{ date("d M Y", strtotime($app->created_at)) }}</td>
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

            <!-- Preview modal -->
            <div id="previewModal" class="modal fade" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" id="previewForm">
                        @csrf
                        <input value="" type="hidden" id="appid" name="appid" class="form-control">
                        <div class="modal-header">
                            <h5 class="modal-title" id="previewModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-check form-check-right">
                                <input class="form-check-input" type="checkbox" id="check_recipient">
                                <label class="form-check-label" for="check_recipient">
                                    Name of Institution/Organization: <span style="color:red" id="show_recipient"></span>
                                </label>
                                <input type="text" id="recipient" name="recipient" class="form-control recipient" required>
                            </div><hr>
                            <div class="form-check form-check-right">
                                <input class="form-check-input" type="checkbox" id="check_reference">
                                <label class="form-check-label" for="check_reference">
                                    Reference Number: <span style="color:red" id="show_reference"></span>
                                </label>
                                <input type="text" id="reference" name="reference" class="form-control reference" required>
                            </div><hr>
                            <div class="form-check form-check-right email">
                                <input class="form-check-input" type="checkbox" id="check_email">
                                <label class="form-check-label" for="check_email">
                                    Email: <span style="color:red" id="show_email"></span>
                                </label>
                                <input type="email" id="email" name="email" class="form-control email_box" required><hr>
                            </div>
                            <div class="form-check form-check-right address">
                                <input class="form-check-input" type="checkbox" id="check_address">
                                <label class="form-check-label" for="check_address">
                                    Adress of Institution/Organization: <span style="color:red" id="show_address"></span>
                                </label>
                                <textarea class="form-control address_box" id="address" name="address" required></textarea>
                            </div><hr>
                            <div class="form-check form-check-right certificate">
                                <input class="form-check-input" type="checkbox" id="check_certificate">
                                <label class="form-check-label" for="check_certificate">
                                    Degree Certificate: <a href="" target="_blank" class="viewcert">VIEW</a>
                                </label>
                                <input type="text" id="certificate" name="certificate" class="form-control certificate_box" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="btnPreview" class="btn btn-danger waves-effect">Send</button>
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

        