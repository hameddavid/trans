$(document).ready(function ($) {
    $(".editApplicant").click(function () {
        $("#editApplicantForm").trigger("reset");
        $("#applicantModal").modal("show");
        $("#applicantModalLabel").html($(this).data("name"));

        $("#surname").val($("#surname").val() + $(this).data("surname"));
        $("#othernames").val(
            $("#othernames").val() + $(this).data("othernames")
        );
        $("#email").val($("#email").val() + $(this).data("email"));
        $("#phone").val($("#phone").val() + $(this).data("phone"));
        $("#matric").val($("#matric").val() + $(this).data("matric"));

        $("#btnEdit").click(function () {
            $("#editApplicantForm").validate({
                submitHandler: submitForm,
            });

            function submitForm() {
                var formData = $("#editApplicantForm").serialize();
                var type = "POST";
                var ajaxurl = "edit_applicant";

                $.ajax({
                    type: type,
                    url: ajaxurl,
                    data: formData,
                    dataType: "json",
                    beforeSend: function () {
                        $("#btnEdit").html(
                            '<i class="fa fa-spinner fa-spin"></i>'
                        );
                    },
                    success: function (response) {
                        console.log(response);
                        $("#btnEdit").html("Update");
                        alertify.success(response.message);
                        setTimeout(function () {
                            location.reload();
                        }, 2800);
                    },
                    error: function (response) {
                        console.log(response);
                        $("#btnEdit").html("Update");
                        alertify.error(response.responseJSON.message);
                    },
                });
            }
        });
    });

    $(".view_transcript").click(function () {
        $(".showHTML").html("");
        $("#transcriptModal").modal("show");
        $("#transcriptModalLabel").html($(this).data("name") + "'s Transcript");
        id = $(this).data("id");

        $(".showHTML").load(`transcript/${id}`, function (data, status, jqXGR) {
            console.log(data);
        });

        $("#btnReccommend").click(function () {
            reccommendTranscript(id);
        });
    });

    $(".viewForgotMatric").click(function () {
        $("#forgotMatric").modal("show");
        email = $(this).data("email");
        $("#name").html(
            $(this).data("surname") +
                " " +
                $(this).data("firstname") +
                " " +
                $(this).data("othername")
        );
        $("#email").html($(this).data("email"));
        $("#phone").html($(this).data("phone"));
        $("#program").html($(this).data("program"));
        $("#graduation").html($(this).data("date_left"));

        $("#btnSendMatric").click(function () {
            $("#sendMatricForm").validate({
                submitHandler: submitMatricForm,
            });

            function submitMatricForm() {
                var type = "POST";
                var ajaxurl = "api/treat_forgot_matno_request";
                matric = $("#matric_number").val();

                $.ajax({
                    type: type,
                    url: ajaxurl,
                    data: {
                        retrieve_matno: matric,
                        email: email,
                    },
                    dataType: "json",
                    beforeSend: function () {
                        $("#btnSendMatric").html(
                            '<i class="fa fa-spinner fa-spin"></i>'
                        );
                    },
                    success: function (response) {
                        console.log(response);
                        $("#btnSendMatric").html("Send");
                        alertify.success(response.message);
                        setTimeout(function () {
                            location.reload();
                        }, 2800);
                    },
                    error: function (response) {
                        console.log(response);
                        $("#btnSendMatric").html("Send");
                        alertify.error(response.responseJSON.message);
                    },
                });
            }
        });
    });

    $("#btnResetPassword").click(function () {
        $("#resetPasswordForm").validate({
            rules: {
                password: {
                    required: true,
                    minlength: 8,
                    maxlength: 15,
                },
                confirm_password: {
                    required: true,
                    equalTo: "#password",
                },
            },
            messages: {
                password: {
                    required: "please enter password",
                    minlength: "password must have at least 8 characters",
                },
                confirm_password: {
                    required: "please retype your password",
                    equalTo: "password doesn't match!",
                },
            },
            submitHandler: submitResetPasswordForm,
        });

        function submitResetPasswordForm() {
            var formData = $("#resetPasswordForm").serialize();
            var type = "POST";
            var ajaxurl = "api/admin_reset_password";

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $("#btnResetPassword").html(
                        '<i class="fa fa-spinner fa-spin"></i>'
                    );
                },
                success: function (response) {
                    console.log(response);
                    $("#btnResetPassword").html("Update Password");
                    alertify.success(response.message);
                },
                error: function (response) {
                    console.log(response);
                    $("#btnResetPassword").html("Update Password");
                    alertify.error(response.responseJSON.message);
                },
            });
        }
    });

    $(".reccommend").click(function () {
        id = $(this).data("id");
        reccommendTranscript(id);
    });

    const reccommendTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "api/reccommend",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                $("#btnReccommend").html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
            },
            success: function (response) {
                console.log(response);
                $("#btnReccommend").html("Reccommended");
                alertify.success(response.message);
            },
            error: function (response) {
                console.log(response);
                $("#btnReccommend").html("Reccommend");
                alertify.error(response.responseJSON.message);
            },
        });
    };

    const approveTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "api/approve",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                $("#btnApprove").html('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                console.log(response);
                $("#btnApprove").html("Approved");
                alertify.success(response.message);
            },
            error: function (response) {
                console.log(response);
                $("#btnApprove").html("Approve");
                alertify.error(response.responseJSON.message);
            },
        });
    };
});
