@extends("layout.master") 

    @section("title")
      Account Settings
    @endsection

    @section("content")
            <div class="main-content">
                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Account Settings</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form method="POST" id="resetPasswordForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="old_pass" class="col-form-label">Current Password:</label>
                                                <input type="password" class="form-control" name="old_pass" id="old_pass" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="password" class="col-form-label">New Password:</label>
                                                <input type="password" class="form-control" name="password" id="password" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirm_password" class="col-form-label">Confirm New Password:</label>
                                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                            </div>
                                            <button type="submit" id="btnResetPassword" class="btn btn-secondary">Update Password</button>
                                        </form>
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
        
        <script src="../assets/js/validation.min.js"></script>
        <script src="../assets/js/utils.js"></script>   
    @endsection

        