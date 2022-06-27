$(document).ready(function () {
    $(".transcript").hide();
    $(".degree").hide();

    $("#doc_type").change(function () {
        if ($("#doc_type").val() === "transcript") {
            $(".transcript").show();
            $(".degree").hide();
        } else if ($("#doc_type").val() === "degree") {
            $(".degree").show();
            $(".transcript").hide();
        } else {
            $(".transcript").hide();
            $(".degree").hide();
        }
    });

    $("#btnSubmitVerification").click(function () {
        $("#resetPasswordForm").validate({
            submitHandler: submitResetPasswordForm,
        });

        function submitResetPasswordForm() {
            var formData = $("#resetPasswordForm").serialize();
            var type = "POST";
            var ajaxurl = "admin_reset_password";

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $("#btnSubmitVerification").html(
                        '<i class="fa fa-spinner fa-spin"></i>'
                    );
                },
                success: function (response) {
                    console.log(response);
                    $("#btnSubmitVerification").html("Update Password");
                    alertify.success(response.message);
                },
                error: function (response) {
                    console.log(response);
                    $("#btnSubmitVerification").html("Update Password");
                    alertify.error(response.responseJSON.message);
                },
            });
        }
    });
});
