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
                beforeSend: function () {},
                success: function (response) {
                    console.log(response);
                    alertify.success(response.message);
                },
                error: function (response) {
                    console.log(response);
                    alertify.error(response.responseJSON.message);
                },
            });
        }
    });
});
