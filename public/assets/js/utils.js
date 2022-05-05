$(document).ready(function ($) {
    $(".edit").click(function () {
        $("#editApplicantForm").trigger("reset");
        $("#applicantModal").modal("show");
        $("#applicantModalLabel").html($(this).data("name"));

        $("#fullname").val($("#fullname").val() + $(this).data("name"));
        $("#email").val($("#email").val() + $(this).data("email"));
        $("#phone").val($("#phone").val() + $(this).data("phone"));
        $("#matric").val($("#matric").val() + $(this).data("matric"));

        // id = $(this).attr("id");
        // btn = $(this).data("btn");
        // $.ajax({
        //     type: "POST",
        //     url: "/activate_post",
        //     data: { id: id },
        //     dataType: "json",
        //     beforeSend: function () {
        //         $(`#${btn}`).html('<i class="fa fa-spinner fa-spin"></i>');
        //     },
        //     success: function (response) {
        //         console.log(response);
        //         $(`#${btn}`).html(
        //             '<i class="bx bx-dots-horizontal-rounded"></i>'
        //         );
        //         toastr.options;
        //         toastr["success"](response.message);
        //         setTimeout(function () {
        //             location.reload();
        //         }, 2800);
        //     },
        //     error: function (response) {
        //         console.log(response);
        //         $(`#${btn}`).html(
        //             '<i class="bx bx-dots-horizontal-rounded"></i>'
        //         );
        //         toastr.options;
        //         toastr["error"](response.responseJSON.message);
        //     },
        // });
    });
});
