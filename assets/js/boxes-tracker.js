document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("bt__form");
    const result = document.getElementById("bt__result");

    if (!form) return;

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const trackingNumber = document.getElementById("bt_tracking_number").value;

        result.innerHTML = "<p>Consultando...</p>";

        fetch(bt_ajax.ajax_url, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({
                action: "boxes_tracker_lookup",
                tracking_number: trackingNumber,
                _ajax_nonce: bt_ajax.nonce
            })
        })
        .then(response => response.text())
        .then(html => {
            result.innerHTML = html;
        })
        .catch(err => {
            result.innerHTML = "<p>Error al consultar el tracking.</p>";
        });
    });
});
