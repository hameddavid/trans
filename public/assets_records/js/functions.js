$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $(document).ajaxStart($.blockUI).ajaxStop($.unblockUI);
    $(".transcript").hide();
    $(".degree").hide();

    $("#doc_type").change(function () {
        if ($("#doc_type").val() === "transcript") {
            $(".transcript").show();
            $(".degree").hide();
            $("#matno").prop("required", true);
            $("#used_token").prop("required", true);
            $(".degree_required").prop("required", false);
        } else if ($("#doc_type").val() === "degree") {
            $(".degree").show();
            $(".transcript").hide();
            $(".degree_required").prop("required", true);
            $("#matno").prop("required", false);
            $("#used_token").prop("required", false);
        } else {
            $(".transcript").hide();
            $(".degree").hide();
        }
    });

    $(".btnSubmitVerification").click(function () {
        $("#verification_form").validate({
            submitHandler: submitVerificationForm,
        });

        function submitVerificationForm() {
            var formData = $("#verification_form").serialize();
            var type = "POST";
            $("#doc_type").val() === "transcript"
                ? (ajaxurl = "verify_transcript")
                : (ajaxurl = "degree_verification");

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $(".btnSubmitVerification").html(
                        `<div class="spinner-border text-light" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>`
                    );
                    $(".btnSubmitVerification").prop("disabled", true);
                },
                success: function (response) {
                    console.log(response);
                    $(".btnSubmitVerification").prop("disabled", false);
                    $(".btnSubmitVerification").html("Submit");
                    alertify.success(response.message);
                    if ($("#doc_type").val() === "transcript") {
                        $("head").append(
                            $('<link rel="stylesheet" type="text/css" />').attr(
                                "href",
                                "assets/css/transcript.css"
                            )
                        );
                        $.unblockUI();
                        $(".showDIV").html("");
                        $("#verificationModal").modal("hide");
                        $("#transcriptModal").modal("show");
                        $(".showDIV").html(response.data);
                    }
                },
                error: function (response) {
                    console.log(response);
                    $(".btnSubmitVerification").prop("disabled", false);
                    $(".btnSubmitVerification").html("Submit");
                    alertify.error(response.responseJSON.message);
                },
            });
        }
    });
});
