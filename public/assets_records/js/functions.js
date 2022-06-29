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
        } else if ($("#doc_type").val() === "degree") {
            $(".degree").show();
            $(".transcript").hide();
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
            var ajaxurl = "verify_transcript";

            $.ajax({
                type: type,
                url: ajaxurl,
                data: formData,
                dataType: "json",
                beforeSend: function () {
                    $.blockUI();
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
