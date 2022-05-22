$(document).ready(function ($) {
    $("#btnApprove").hide();
    //$(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
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
        $("#btnApprove").hide();
        $(".showHTML").html("");
        $("#transcriptModal").modal("show");
        $("#transcriptModalLabel").html($(this).data("name") + "'s Transcript");
        id = $(this).data("id");
        stat = $(this).data("status");
        if (stat === "APPROVED") {
            $("#btnRecommend").hide();
        } else if (stat === "RECOMMENDED") {
            $("#btnApprove").show();
            $("#btnRecommend").hide();
        } else {
            console.log(stat);
        }

        $(".showHTML").load(`transcript/${id}`, function (data, status, jqXGR) {
            console.log("fetched");
        });

        $("#btnRecommend").click(function () {
            recommendTranscript(id);
        });

        $("#btnDerecommend").click(function () {
            derecommendTranscript(id);
        });

        $("#btnApprove").click(function () {
            approveTranscript(id);
        });
    });

    $(".preview").click(function () {
        $("#previewModal").modal("show");
        $("#previewModalLabel").html($(this).data("name") + "'s Details");
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
                var ajaxurl = "treat_forgot_matno_request";
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
            var ajaxurl = "admin_reset_password";

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

    $(".recommend").click(function () {
        id = $(this).data("id");
        recommendTranscript(id);
    });

    $(".approve").click(function () {
        id = $(this).data("id");
        approveTranscript(id);
    });

    $(".regenerate").click(function () {
        id = $(this).data("id");
        regenerateTranscript(id);
    });

    const recommendTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "recommend_app ",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                if (confirm("Recommend Transcript?") == false) return false;
                $("#btnRecommend").html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
                $("#btnRecommend").prop("disabled", true);
                $.blockUI();
            },
            success: function (response) {
                console.log(response);
                $("#btnRecommend").html("Recommended");
                alertify.success(response.message);
                setTimeout(function () {
                    location.reload();
                }, 2800);
            },
            error: function (response) {
                console.log(response);
                $("#btnRecommend").html("Recommend");
                $("#btnRecommend").prop("disabled", false);
                $.unblockUI();
                alertify.error(response.responseJSON.message);
            },
        });
    };

    const approveTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "approve_app ",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                if (confirm("Approve Transcript?") == false) return false;
                $("#btnApprove").html('<i class="fa fa-spinner fa-spin"></i>');
                $("#btnApprove").prop("disabled", true);
                $.blockUI();
            },
            success: function (response) {
                console.log(response);
                $("#btnApprove").html("Approved");
                alertify.success(response.message);
                setTimeout(function () {
                    location.reload();
                }, 2800);
            },
            error: function (response) {
                console.log(response);
                $("#btnApprove").html("Approve");
                $("#btnApprove").prop("disabled", false);
                $.unblockUI();
                alertify.error(response.responseJSON.message);
            },
        });
    };

    const regenerateTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "regenerate_transcript  ",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                if (confirm("Regenerate Transcript?") == false) return false;
                $.blockUI();
            },
            success: function (response) {
                console.log(response);
                alertify.success(response.message);
                setTimeout(function () {
                    location.reload();
                }, 2800);
            },
            error: function (response) {
                console.log(response);
                $.unblockUI();
                alertify.error(response.responseJSON.message);
            },
        });
    };

    const derecommendTranscript = (id) => {
        $.ajax({
            type: "POST",
            url: "de_recommend_app",
            data: { id: id },
            dataType: "json",
            beforeSend: function () {
                if (confirm("Cancel recommedation?") == false) return false;
                $("#btnDerecommend").html(
                    '<i class="fa fa-spinner fa-spin"></i>'
                );
                $("#btnDerecommend").prop("disabled", true);
                $.blockUI();
            },
            success: function (response) {
                console.log(response);
                alertify.success(response.message);
                $("#btnDerecommend").html("Not Recommended");
                setTimeout(function () {
                    location.reload();
                }, 2800);
            },
            error: function (response) {
                console.log(response);
                $("#btnDerecommend").html("Cancel Recommendation");
                $("#btnDerecommend").prop("disabled", false);
                $.unblockUI();
                alertify.error(response.responseJSON.message);
            },
        });
    };
});
