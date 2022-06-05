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
        $("head").append(
            $('<link rel="stylesheet" type="text/css" />').attr(
                "href",
                "assets/css/transcript.css"
            )
        );
        $("#btnApprove").hide();
        $(".showHTML").html("");
        $("#transcriptModal").modal("show");
        $("#transcriptModalLabel").html($(this).data("name") + "'s Transcript");
        id = $(this).data("id");
        stat = $(this).data("status");
        var type = $(this).data("type");
        if (stat === "APPROVED") {
            $("#btnRecommend").hide();
        } else if (stat === "RECOMMENDED") {
            $("#btnApprove").show();
            $("#btnRecommend").hide();
        } else {
            console.log(stat);
        }

        $(".showHTML").load(
            `transcript/${type}/${id}`,
            function (data, status, jqXGR) {
                console.log("fetched");
            }
        );

        $("#btnRecommend").click(function () {
            recommendTranscript(id, type);
        });

        $("#btnDerecommend").click(function () {
            derecommendTranscript(id, type);
        });

        $("#btnApprove").click(function () {
            approveTranscript(id, type);
        });
    });

    $('input[type="checkbox"]').click(function () {
        $("#check_recipient").prop("checked") == true
            ? $(".recipient").show()
            : $(".recipient").hide();
        $("#check_reference").prop("checked") == true
            ? $(".reference").show()
            : $(".reference").hide();
        $("#check_address").prop("checked") == true
            ? $(".address_box").show()
            : $(".address_box").hide();
        $("#check_email").prop("checked") == true
            ? $(".email_box").show()
            : $(".email_box").hide();
        $("#check_certificate").prop("checked") == true
            ? $(".certificate_box").show()
            : $(".certificate_box").hide();
    });

    $(".preview").click(function () {
        $("#previewModal").modal("show");
        $("#appid").val("");
        $(".recipient").hide();
        $(".reference").hide();
        $(".address_box").hide();
        $(".email_box").hide();
        $(".certificate_box").hide();
        $("#previewModalLabel").html($(this).data("name") + "'s Details");
        var id = $(this).data("id");
        var recipient = $(this).data("recipient");
        var address = $(this).data("address");
        var reference = $(this).data("reference");
        var _href = $("a.viewcert").attr("href");
        var link = $(this).data("certificate");
        $("a.viewcert").attr("href", _href + link);

        $("#appid").val($("#appid").val() + id);

        if ($(this).data("mode") === "Hard") {
            $(".email").hide();
            $("#show_address").html(address);
        }
        if ($(this).data("mode") === "Soft") {
            $("#show_email").html(address);
            $(".address").hide();
        }

        $("#show_recipient").html(recipient);
        $("#show_reference").html(reference);

        $("#btnPreview").click(function () {
            $("#previewForm").validate({
                submitHandler: submitpreviewForm,
            });

            function submitpreviewForm() {
                var formData = $("#previewForm").serialize();
                var type = "POST";
                var ajaxurl = "send_corrections_to_applicant";

                $.ajax({
                    type: type,
                    url: ajaxurl,
                    data: formData,
                    dataType: "json",
                    beforeSend: function () {
                        if (confirm("Send corrections?") == false) return false;
                        $("#btnPreview").html(
                            '<i class="fa fa-spinner fa-spin"></i>'
                        );
                    },
                    success: function (response) {
                        console.log(response);
                        $("#btnPreview").html("Send");
                        alertify.success(response.message);
                    },
                    error: function (response) {
                        console.log(response);
                        $("#btnPreview").html("Send");
                        alertify.error(response.responseJSON.message);
                    },
                });
            }
        });
    });

    $(".viewForgotMatric").click(function () {
        $("#forgotMatric").modal("show");
        $("#sendMatricForm").trigger("reset");
        $("#matric_number")
            .empty()
            .append("<option value=''>Select Matric Number</option>");
        email = $(this).data("email");
        suggestions = $(this).data("suggestions");
        console.log(suggestions);
        if (suggestions !== "") {
            $(".matric_number").hide();
            $(".select_matric_number").show();
            // $.each(suggestions, function (i, item) {
            //     $("#matric_number").append(
            //         $("<option>", {
            //             value: item,
            //             text: item,
            //         })
            //     );
            // });
            suggestions.forEach((suggestion) => {
                for (let key in suggestion) {
                    $("#matric_number").append(
                        $("<option>", {
                            value: suggestion[key],
                            text: suggestion[key],
                        })
                    );
                }
            });
        } else {
            $(".matric_number").show();
            $(".select_matric_number").hide();
        }

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
                suggestions !== ""
                    ? (matric = $("#matric_number").val())
                    : (matric = $("#matric_number_").val());
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
        var type = $(this).data("type");
        recommendTranscript(id, type);
    });

    $(".approve").click(function () {
        var id = $(this).data("id");
        var type = $(this).data("type");
        approveTranscript(id, type);
    });

    $(".regenerate").click(function () {
        id = $(this).data("id");
        var type = $(this).data("type");
        regenerateTranscript(id, type);
    });

    $(".download").click(function () {
        var id = $(this).data("id");
        var type = $(this).data("type");
        downloadPDF(id, type);
    });

    const recommendTranscript = (id, type) => {
        $.ajax({
            type: "POST",
            url: "recommend_app ",
            data: { id: id, transcript_type: type },
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

    const approveTranscript = (id, type) => {
        $.ajax({
            type: "POST",
            url: "approve_app ",
            data: { id: id, transcript_type: type },
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

    const regenerateTranscript = (id, type) => {
        $.ajax({
            type: "POST",
            url: "regenerate_transcript  ",
            data: { id: id, transcript_type: type },
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

    const derecommendTranscript = (id, type) => {
        $.ajax({
            type: "POST",
            url: "de_recommend_app",
            data: { id: id, transcript_type: type },
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

    const downloadPDF = (id, type) => {
        $.ajax({
            type: "POST",
            url: "download_approved",
            data: { id: id, transcript_type: type },
            dataType: "json",
            beforeSend: function () {
                if (confirm("Download PDF?") == false) return false;
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

    // const arrayToObject = (array, key) =>
    //     array.reduce(
    //         (obj, item) => ({
    //             ...obj,
    //             [item[key]]: item,
    //         }),
    //         {}
    //     );
});
