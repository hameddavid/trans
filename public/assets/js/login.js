$(document).ready(function ($) {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $("#btnLogin").click(function () {
        $("#loginForm").validate({
            submitHandler: submitLoginForm,
            errorClass: "invalid",
        });

        function submitLoginForm(e) {
            var formData = $("#loginForm").serialize();
            var type = "POST";
            var ajaxurl = "admin_login_auth";

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $("#btnLogin").html(
                        '<i class="fa fa-spinner fa-spin"></i>'
                    );
                    $("#btnLogin").prop("disabled", true);
                },
                success: function (response) {
                    console.log(response);
                    alertify.success(response.message);
                    setTimeout(function () {
                        window.location.href = "dashboard";
                    }, 2800);
                },
                error: function (response) {
                    console.log(response);
                    $("#btnLogin").html("Login");
                    $("#btnLogin").prop("disabled", false);
                    alertify.error(response.responseJSON.message);
                },
            });
        }
    });
});
