
// Counting messages - getting information from root /countReceiverMessages
$(document).ready(function () {
    $('.badge-primary').addClass('d-none');
    setInterval(function () {
        $.get('/countReceiverMessages', null, function (data) {
            if (data > 0) {
                $('.badge-primary').removeClass('d-none');
                $('.badge-primary').html(data);

            } else {
                $('.badge-primary').addClass('d-none');
            }
        });

    }, 300000);
})