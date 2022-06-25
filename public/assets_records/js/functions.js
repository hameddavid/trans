$(document).ready(function () {
  $(".transcript").hide();

  $("#doc_type").change(function () {
    $("#doc_type").val() === "degree"
      ? $(".transcript").show()
      : $(".transcript").hide();
  });
});
