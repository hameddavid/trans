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
        $("#transcriptModal").modal("show");
        $("#transcriptModalLabel").html($(this).data("name") + "'s Transcript");
        var id = $(this).data("id");
        $.get(`/transcript/${id}`, function (data, textStatus, jqXHR) {
            console.log("status: " + textStatus + ", data:" + data);
            $(".showHTML").append(data.transcript_raw);
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
});
