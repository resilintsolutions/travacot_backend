// reservations.js

// --------------------
// View reservation modal
// --------------------
function viewReservation(id) {
    $('#reservationDetailModal').modal('show');
    $('#reservationDetailsContent').html(
        '<div class="text-center py-5">Loading...</div>'
    );

    $.get(`${APP_URL}/admin/reservations/${id}`, function (res) {
        $('#reservationDetailsContent').html(res.html);
    });
}

// --------------------
// Generic AJAX action handler
// --------------------
function ajaxAction(url, id, actionName) {
    let btn = $(`button[data-id="${id}"].btn-${actionName}`);
    let successBox = $(`#actionSuccess${id}`);
    let errorBox = $(`#actionError${id}`);

    btn.prop("disabled", true).html("Processing...");

    $.ajax({
        url: url,
        method: "POST",
        data: {
            _token: CSRF_TOKEN
        },
        success: function (res) {
            btn.prop("disabled", false).html(
                btn.data("original") || btn.text()
            );

            successBox.removeClass("d-none").text(res.message || "Success");
            errorBox.addClass("d-none").text("");

            // Live update status badge
            if (res.new_status_html) {
                $(`#statusBadge${id}`).html(res.new_status_html);
            }
        },
        error: function (xhr) {
            btn.prop("disabled", false);

            let msg = "Something went wrong.";
            if (xhr.responseJSON?.message) {
                msg = xhr.responseJSON.message;
            }

            successBox.addClass("d-none");
            errorBox.removeClass("d-none").text(msg);
        }
    });
}

// --------------------
// Action bindings
// --------------------
$(document).on("click", ".btn-retry", function () {
    let id = $(this).data("id");
    $(this).data("original", "Retry Booking");
    ajaxAction(`${APP_URL}/admin/reservations/${id}/retry`, id, "retry");
});

$(document).on("click", ".btn-check-status", function () {
    let id = $(this).data("id");
    $(this).data("original", "Check Status");
    ajaxAction(`${APP_URL}/admin/reservations/${id}/check-status`, id, "check-status");
});

$(document).on("click", ".btn-cancel-booking", function () {
    let id = $(this).data("id");
    $(this).data("original", "Cancel Booking");
    ajaxAction(`${APP_URL}/admin/reservations/${id}/cancel`, id, "cancel-booking");
});

$(document).on("click", ".btn-rebook", function () {
    let id = $(this).data("id");
    $(this).data("original", "Rebook");
    ajaxAction(`${APP_URL}/admin/reservations/${id}/rebook`, id, "rebook");
});
