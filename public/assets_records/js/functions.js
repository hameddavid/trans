$(document).ready(function () {
    $(".transcript").hide();
    $(".degree").hide();

    $("#doc_type").change(function () {
        if ($("#doc_type").val() === "transcript") {
            $(".transcript").show();
        } else if ($("#doc_type").val() === "degree") {
            $(".degree").show();
        } else {
            $(".transcript").hide();
            $(".degree").hide();
        }
    });
});
