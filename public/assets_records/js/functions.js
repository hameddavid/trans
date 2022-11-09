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
        if ($("#doc_type").val() === "transcript") {
            $("#verification_form").validate({
                submitHandler: submitVerificationForm,
            });
        } else {
            matno = $("#matno").val();
            email = $("#institution_email").val();
            institution_name = $("#institution_name").val();
            phone = $("#phone").val();
            firstname = $("#firstname").val();
            surname = $("#surname").val();
            fullname = firstname + " " + surname;
            $(".btnSubmitVerification").html(
                `<div class="spinner-border text-light" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>`
            );
            $(".btnSubmitVerification").prop("disabled", true);

            getRemitaConfig(function (response) {
                console.log(response);
                if (response.status === "success") {
                    var merchantId = response.data.merchantId;
                    var serviceTypeId = response.data.serviceTypeID;
                    var apiKey = response.data.apiKey;
                    var orderId = response.data.orderID;
                    var amount = response.data.amount;
                    console.log(serviceTypeId);
                    setTimeout(function () {
                        processPayment(
                            merchantId,
                            serviceTypeId,
                            apiKey,
                            orderId,
                            email,
                            phone,
                            institution_name,
                            amount
                        );
                    }, 5000);
                } else {
                    alertify.error(response.message);
                    $(".btnSubmitVerification").html("Submit");
                    $(".btnSubmitVerification").prop("disabled", false);
                    return false;
                }
            });
        }
    });

    const getRemitaConfig = (callback) => {
        $.ajax({
            url: `https://records.run.edu.ng/api/degree/get_gateway_config?matno=${matno}&gateway=REMITA&email=${email}`,
            type: "GET",
            success: callback,
        });
    };

    const submitVerificationForm = () => {
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
    };

    const processPayment = (
        merchantId,
        serviceTypeId,
        apiKey,
        orderId,
        email,
        phone,
        institution_name,
        amount
    ) => {
        console.log(serviceTypeId);
        console.log(email);
        var toHash = merchantId + serviceTypeId + orderId + amount + apiKey;
        var apiHash = sha512(toHash);
        var desc = "Degree Verification Payment";
        settings = {
            url: "https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit",
            method: "POST",
            timeout: 0,
            headers: {
                "Content-Type": "application/json",
                Authorization:
                    "remitaConsumerKey=" +
                    merchantId +
                    ",remitaConsumerToken=" +
                    apiHash,
            },
            data: JSON.stringify({
                serviceTypeId: serviceTypeId,
                amount: amount,
                orderId: orderId,
                payerName: institution_name,
                payerEmail: email,
                payerPhone: phone,
                description: desc,
            }),
        };

        $.ajax({
            type: "GET",
            url: `https://records.run.edu.ng/api/degree/check_pend_rrr?matno=${mat_no}&institution_email=${email}&gateway=REMITA`,
            success: function (response) {
                if (response.status == "success") {
                    var rrr = response.p_rrr;
                    console.log(rrr);
                    makePayment(mat_no, firstname, surname, email, amount, rrr);
                } else if (response.status == "failed") {
                    $.ajax(settings).done(function (res) {
                        var obj = res.substring(7, res.length - 1);
                        var objJson = JSON.parse(obj);
                        rrr = objJson.RRR;
                        console.log(rrr);
                        $.ajax({
                            type: "POST",
                            url: `https://records.run.edu.ng/api/degree/log_new_rrr_trans_ref?mat_no=${mat_no}&rrr=${rrr}&amount=${amount}&institution_name=${institution_name}&gateway=REMITA&statuscode=${objJson.statuscode}&statusMsg=${objJson.status}&orderID=${orderId}&institution_email=${email}`,
                            success: function (response) {
                                console.log(response);
                                if (response.status == "success") {
                                    alert("Proceed to Pay");
                                    makePayment(
                                        mat_no,
                                        firstname,
                                        surname,
                                        email,
                                        amount,
                                        rrr
                                    );
                                } else {
                                    $(".btnSubmitVerification").html("Submit");
                                    $(".btnSubmitVerification").prop(
                                        "disabled",
                                        false
                                    );
                                    alertify.error(response.message);
                                }
                            },
                            error: function (response) {
                                console.log(response);
                                $(".btnSubmitVerification").html("Submit");
                                $(".btnSubmitVerification").prop(
                                    "disabled",
                                    false
                                );
                                alertify.error(response.responseJSON.message);
                            },
                        });
                    });
                } else {
                    $(".btnSubmitVerification").html("Submit");
                    $(".btnSubmitVerification").prop("disabled", false);
                    alertify.error("Network Error!, Try again later");
                }
            },
            error: function (response) {
                console.log(response);
                $(".btnSubmitVerification").html("Submit");
                $(".btnSubmitVerification").prop("disabled", false);
                alertify.error(response.responseJSON.message);
            },
        });
    };

    const makePayment = (mat_no, firstname, surname, email, amount, rrr) => {
        var paymentEngine = RmPaymentEngine.init({
            key: "QzAwMDAxNTY4NzN8OTU3M3w1OWUwZmVmMmUxYWYwZTlhMjk3MTU5MzIwNzcxNjc1NWYwYmI5ZWNkZWYyYzcwYWZiZGIwOGZkYmViYzhiYjI3MTkyYzA3MGRhOWZkZDgxNDhlMjdjNmVkMGI0ZjgwYjQ4ZDM1OTkwMzhmNzU4OTJmN2NjMTUxMTljZDY1NjA1NQ==",
            customerId: mat_no,
            firstName: firstname,
            lastName: surname,
            email: email,
            narration: "Degree Verification Payment",
            amount: amount,
            processRrr: true,
            extendedData: {
                customFields: [
                    {
                        name: "rrr",
                        value: rrr,
                    },
                ],
            },
            onSuccess: function (response) {
                console.log(response);
                console.log(response.amount);
                if (
                    response.amount !== "" &&
                    response.transactionId !== "" &&
                    response.paymentReference !== ""
                ) {
                    $.ajax({
                        type: "POST",
                        url: `https://records.run.edu.ng/api/degree/update_payment?paymentReference=${response.paymentReference}&transactionId=${response.transactionId}&amount=${response.amount}&mat_no=${mat_no}`,
                        beforeSend: function () {
                            $(".btnSubmitVerification").html(
                                "Updating Payment..."
                            );
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.status == "success") {
                                submitVerificationForm();
                                alertify.success("Transaction Successful");
                                $(".btnSubmitVerification").html("Submit");
                            } else {
                                alertify.error("Payment Updating Failed");
                                $(".btnSubmitVerification").prop(
                                    "disabled",
                                    false
                                );
                                $(".btnSubmitVerification").html("Submit");
                            }
                        },
                        error: function (response) {
                            $(".btnSubmitVerification").html("Submit");
                            $(".btnSubmitVerification").prop("disabled", false);
                            alertify.error(response.responseJSON.msg);
                        },
                    });
                } else {
                    $(".btnSubmitVerification").html("Submit");
                    $(".btnSubmitVerification").prop("disabled", false);
                    alertify.error("Transaction Failed");
                }
            },

            onError: function (response) {
                console.log(response);
                alertify.error("Network error, Try again later!");
                $(".btnSubmitVerification").html("Submit");
                $(".btnSubmitVerification").prop("disabled", false);
            },

            onClose: function () {
                $(".btnSubmitVerification").html("Submit");
                $(".btnSubmitVerification").prop("disabled", false);
            },
        });

        paymentEngine.showPaymentWidget();
    };
});
