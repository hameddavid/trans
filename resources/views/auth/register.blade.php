<!doctype html>
<html lang="en">

    
    <head>
        <meta charset="utf-8" />
        <title>Register | Transcript - Admin Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesbrand" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/run_logo.png">

        <!-- alertifyjs Css -->
        <link href="assets/libs/alertifyjs/build/css/alertify.min.css" rel="stylesheet" type="text/css" />

        <!-- alertifyjs default themes  Css -->
        <link href="assets/libs/alertifyjs/build/css/themes/default.min.css" rel="stylesheet" type="text/css" />

        <!-- preloader css -->
        <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    </head>

    <style>
      .invalid {
        color:#ff0000;
      }
    </style>

    <body data-topbar="dark">

    <!-- <body data-layout="horizontal"> -->
        <div class="auth-page">
            <div class="container-fluid p-0">
                <div class="row g-0">
                    <div class="col-xxl-3 col-lg-4 col-md-5">
                        <div class="auth-full-page-content d-flex p-sm-5 p-4">
                            <div class="w-100">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4 mb-md-5 text-center">
                                        <a href="#" class="d-block auth-logo">
                                            <img src="assets/images/run_logo.png" alt="" height="28"> <span class="logo-txt">Redeemer's University</span>
                                        </a>
                                    </div>
                                    <div class="auth-content my-auto">
                                        <div class="text-center">
                                            <h5 class="mb-0">Transcript Admin Dashboard</h5>
                                            <p class="text-muted mt-2">Create account.</p>
                                            <br>
                            @if(Session::get('success'))
                                <div class="alert dark alert-icon alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <i class="icon md-close" aria-hidden="true"></i> {{Session::get('success')}}
                                </div>
                            @endif
                            @if(Session::get('fail'))
                                <div class="alert dark alert-icon alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <i class="icon md-close" aria-hidden="true"></i> {{Session::get('fail')}}
                                </div>
                             @endif
                                        </div>
                                        <form method="POST" action="register" id="" class="mt-4 pt-2">
                                            @csrf
                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="email" class="form-control" name="email" id="email" required>
                                                <label for="email">Email</label>
                                                <div class="form-floating-icon">
                                                   <i data-feather="users"></i>
                                                </div>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text" class="form-control" name="surname" id="surname" required>
                                                <label for="surname">Surname</label>
                                                <div class="form-floating-icon">
                                                   <i data-feather="users"></i>
                                                </div>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text" class="form-control" name="firstname" id="firstname" required>
                                                <label for="firstname">Firstname</label>
                                                <div class="form-floating-icon">
                                                   <i data-feather="users"></i>
                                                </div>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text" class="form-control" name="othername" id="othername" required>
                                                <label for="othername">Othername</label>
                                                <div class="form-floating-icon">
                                                   <i data-feather="users"></i>
                                                </div>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <input type="text" class="form-control" name="phone" id="phone" required>
                                                <label for="phone">Phone</label>
                                                <div class="form-floating-icon">
                                                   <i data-feather="users"></i>
                                                </div>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <select class="" name="role" id="role" required>
                                                <option value=""> Select role</option>
                                                <option value="200">Recommended Role</option>
                                                <option value="300">Approved Role</option>
                                                 </select>
                                            </div>
                                            <div class="form-floating form-floating-custom mb-4">
                                                <select class="" name="title" id="title" required>
                                                <option value=""> Select title</option>
                                                <option value="Mr.">Mr.</option>
                                                <option value="Mrs.">Mrs.</option>
                                                <option value="Dr.">Dr.</option>
                                                <option value="Pst.">Miss</option>
                                                 </select>
                                            </div>
                                          

                                            <!-- <div class="form-floating form-floating-custom mb-4 auth-pass-inputgroup">
                                                <input type="password" class="form-control pe-5" name="password" id="password" required>
                                                
                                                <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                </button>
                                                <label for="password">Password</label>
                                                <div class="form-floating-icon">
                                                    <i data-feather="lock"></i>
                                                </div>
                                            </div> -->

                                            <!-- <div class="row mb-4">
                                                <div class="col">
                                                    <div class="form-check font-size-15">
                                                        <input class="form-check-input" type="checkbox" id="remember-check">
                                                        <label class="form-check-label font-size-13" for="remember-check">
                                                            Remember me
                                                        </label>
                                                    </div>  
                                                </div>
                                                
                                            </div> -->
                                            <div class="mb-3">
                                                <button id="btnLogin" class="btn btn-primary w-100 waves-effect waves-light" type="submit">Log In</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="mt-4 mt-md-5 text-center">
                                        <p class="mb-0">© <script>document.write(new Date().getFullYear())</script> Redeemer's University</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end auth full page content -->
                    </div>
                    <!-- end col -->
                    <div class="col-xxl-9 col-lg-8 col-md-7">
                        <div class="auth-bg pt-md-5 p-4 d-flex">
                            <div class="bg-overlay"></div>
                            <ul class="bg-bubbles">
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                            <!-- end bubble effect -->
                            
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container fluid -->
        </div>


        <!-- JAVASCRIPT -->
        <script src="assets/libs/jquery/jquery.min.js"></script>
        <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="assets/libs/simplebar/simplebar.min.js"></script>
        <script src="assets/libs/node-waves/waves.min.js"></script>
        <script src="assets/libs/feather-icons/feather.min.js"></script>
        <!-- pace js -->

        <!-- alertifyjs js -->
        <script src="assets/libs/alertifyjs/build/alertify.min.js"></script>
        <!-- notification init -->
        <script src="assets/js/pages/notification.init.js"></script>

        <script src="assets/libs/pace-js/pace.min.js"></script>

        <script src="assets/js/pages/pass-addon.init.js"></script>

        <script src="assets/js/pages/feather-icon.init.js"></script>
        <script src="assets/js/validation.min.js"></script>
        <script src="assets/js/login.js"></script>

    </body>
</html>