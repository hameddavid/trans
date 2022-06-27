$(document).ready(function () {
    $(".transcript").hide();
    $(".degree").hide();

    $("#doc_type").change(function () {
        $("#doc_type").val() === "transcript"
            ? $(".transcript").show()
            : $(".transcript").hide();
    });

    $("#doc_type").change(function () {
        $("#doc_type").val() === "degree"
            ? $(".degree").show()
            : $(".degree").hide();
    });
});
