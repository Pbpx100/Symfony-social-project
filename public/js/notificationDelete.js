//Deleting notification 
$(document).ready(function () {
    $('.close').click(function (e) {
        e.preventDefault();
        let id = e.target.id;
        $.post('/notificationDelete', { id: id }, function (data) {
            location.reload();
        });

    });

});