// Checking if necih is avaible -. sending information to root /nickCheck
$(document).ready(function () {
    $('.nick-input').blur(function (event) {

        var nick = this.value;

        $.ajax({
            url: '/nickCheck',
            data: { nick: nick },
            type: 'POST',
            success: function (data) {
                if (data == 'used') {
                    $('.nick-input').css("border", "3px solid red");
                }
                else {
                    $('.nick-input').css("border", "3px solid green");
                }

            }

        });

    });

});