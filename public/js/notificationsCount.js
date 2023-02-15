//Counting notification from root /countNotifications 
$(document).ready(function () {
    $('.badge-info').addClass('d-none');
    setInterval(function () {
        $.get('/countNotifications', null, function (data) {
            if (data > 0) {
                $('.badge-info').removeClass('d-none');
                $('.badge-info').html(data);

            } else {
                $('.badge-info').addClass('d-none');
            }
        });

    }, 300000000);
})