@extends("layout.master") 

    @section("title")
      Dashboard
    @endsection

    @section("content")
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Requests</span>
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="{{count($total)}}">0</span>
                                                </h4>
                                            </div>
        
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart1" data-colors='["#1c84ee", "#33c38e"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Approved Requests</span>
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="{{$approved}}">0</span>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart2" data-colors='["#1c84ee", "#33c38e"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col-->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Pending Requests</span>
                                                <h4 class="mb-3">
                                                    <span class="counter-value" data-target="{{$pending}}">0</span>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart3" data-colors='["#1c84ee", "#33c38e"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->
        
                            <div class="col-xl-3 col-md-6">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <span class="text-muted mb-3 lh-1 d-block text-truncate">Total Payments</span>
                                                <h4 class="mb-3">
                                                ₦ <span class="counter-value" data-target="{{$payments}}">0</span>
                                                </h4>
                                            </div>
                                            <div class="flex-shrink-0 text-end dash-widget">
                                                <div id="mini-chart4" data-colors='["#1c84ee", "#33c38e"]' class="apex-charts"></div>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div><!-- end col -->    
                        </div><!-- end row-->

                        <div class="row">
                            <div class="col-xl-8">
                                <!-- card -->
                                <div class="card">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center mb-4">
                                            <h5 class="card-title me-2">Transcript Request Overview</h5>
                                        </div>

                                        <div class="row align-items-center">
                                            <div class="col-xl-12">
                                                <div>
                                                    <div id="market-overview" data-colors='["#1c84ee", "#33c38e"]' class="apex-charts"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end card -->
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row-->
        
                            <div class="col-xl-4">
                                <!-- card -->
                                <div class="card">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center mb-4">
                                            <h5 class="card-title me-2">Transcript Request by Locations</h5>
                                        </div>

                                        <div id="sales-by-locations" data-colors='["#33c38e"]' style="height: 253px"></div>

                                        <div class="px-2 py-2">
                                            @php 
                                                use App\Http\Controllers\Admin\AdminController; 
                                                $data = new AdminController();
                                                $location = $data->transcriptLocation(); 
                                            @endphp
                                            @foreach($location as $val)
                                            @if($val->number != 0)
                                            @php $percentage = round(($val->number / count($total) * 100)); @endphp
                                            <p class="mb-1">{{$val->destination}} <span class="float-end">{{$percentage}}%</span></p>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                                    style="width: {{$percentage}}%" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                            @endif
                                            @endforeach                                            
                                        </div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row-->

                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1">Recent Transcript Requests</h4>
                                        <div class="flex-shrink-0">
                                            <div class="dropdown">
                                                <a class=" dropdown-toggle" href="#" id="dropdownMenuButton2"
                                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="text-muted">View<i class="mdi mdi-chevron-down ms-1"></i></span>
                                                </a>
                        
                                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton2">
                                                    <a class="dropdown-item" href="#">View all</a>
                                                    <a class="dropdown-item" href="pending_applications">View Pending Requests</a>
                                                    <a class="dropdown-item" href="approved_applications">View Approved Requests</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end card header -->

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">

                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Matric number</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $i = 1 @endphp
                                                    @foreach($total as $app)
                                                    <tr>
                                                        <th scope="row">{{$i}} @php $i++ @endphp</th>
                                                        <td>{{$app->surname.' '.$app->firstname}}</td>
                                                        <td>{{$app->matric_number}}</td>
                                                        <td>@php echo ($app->app_status == 'APPROVED') ? '<span class="badge badge-soft-success">'.$app->app_status.'</span>' : '<span class="badge badge-soft-danger">'.$app->app_status.'</span>'@endphp</td>
                                                        <td>
                                                            <button type="button" data-id="{{$app->application_id}}" data-name="{{$app->surname.' '.$app->firstname}}" class="btn btn-light btn-sm view_transcript">View</button>
                                                        </td>
                                                    </tr>
                                                    @if ($i > 5)
                                                        @break
                                                    @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            
                            <div class="col-xl-6">
                                <div class="card">
                                    <div class="card-header align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1">Recent Payments</h4>
                                        <div class="flex-shrink-0">
                                            <div class="dropdown align-self-start">
                                                <a class="dropdown-toggle" href="payments">
                                                    <span class="text-muted">View all</span>
                                                </a>
                                            </div>
                                        </div>

                                    </div><!-- end card header -->

                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-dark mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Name</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $i = 1 @endphp
                                                    @foreach($recent_payments as $payment)
                                                    <tr>
                                                        <th scope="row">{{$i}} @php $i++ @endphp</th>
                                                        <td>{{$payment->names}}</td>
                                                        <td>{{$payment->amount}}</td>
                                                        <td>@php echo ($payment->status_msg == 'success') ? '<span class="badge badge-soft-success">'.$payment->status_msg.'</span>' : '<span class="badge badge-soft-danger">'.$payment->status_msg.'</span>'@endphp</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div><!-- end row -->
                    </div>
                    <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                @include("partials.footer")
            </div>
            <!-- end main content-->     
            
            <!-- Transcript modal -->
            <div id="transcriptModal" class="modal fade" tabindex="-1" aria-labelledby="transcriptModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transcriptModalLabel"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body showHTML">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary waves-effect waves-light">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /modal -->

        <!-- apexcharts -->
        <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

        <!-- Plugins js-->
        <script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
        <script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>
        <!-- dashboard init -->
        <script src="assets/js/pages/dashboard.init.js"></script>
        <script src="assets/js/pages/modal.init.js"></script>
        <script src="assets/js/validation.min.js"></script>
        <script src="assets/js/utils.js"></script>
    @endsection

        