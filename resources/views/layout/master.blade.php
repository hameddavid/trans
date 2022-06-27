<!doctype html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <title>@yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Admin Dashboard Transcript Portal" name="description" />
        <meta content="Redeemers University" name="RUN ICT" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="../assets/images/run_logo.png">

         <!-- alertifyjs Css -->
         <link href="../assets/libs/alertifyjs/build/css/alertify.min.css" rel="stylesheet" type="text/css" />

        <!-- alertifyjs default themes  Css -->
        <link href="../assets/libs/alertifyjs/build/css/themes/default.min.css" rel="stylesheet" type="text/css" />

        <!-- plugin css -->
        <link href="../assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

        <!-- preloader css -->
        <link rel="stylesheet" href="../assets/css/preloader.min.css" type="text/css" />

        <!-- Bootstrap Css -->
        <link href="../assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
        <!-- Icons Css -->
        <link href="../assets/css/icons.min.css" rel="stylesheet" type="text/css" />
        <!-- App Css-->
        <link href="../assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

        <!-- JAVASCRIPT -->
        <script src="../assets/libs/jquery/jquery.min.js"></script>
        <script src="../assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="../assets/libs/metismenu/metisMenu.min.js"></script>
        <script src="../assets/libs/simplebar/simplebar.min.js"></script>
        <script src="../assets/libs/node-waves/waves.min.js"></script>
        <script src="../assets/libs/feather-icons/feather.min.js"></script>

        <!-- alertifyjs js -->
        <script src="../assets/libs/alertifyjs/build/alertify.min.js"></script>
        <!-- notification init -->
        <script src="../assets/js/pages/notification.init.js"></script>
        
        <!-- pace js -->
        <script src="../assets/libs/pace-js/pace.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js" integrity="sha512-QSb5le+VXUEVEQbfljCv8vPnfSbVoBF/iE+c6MqDDqvmzqnr4KL04qdQMCm0fJvC3gCWMpoYhmvKBFqm1Z4c9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body data-topbar="dark">
        <!-- Begin page -->
        <div id="layout-wrapper">            
            @include("partials.navbar")

            @include("partials.sidebar")

            @yield('content')
        </div>
        <script src="../assets/js/app.js"></script>
    </body>
</html>