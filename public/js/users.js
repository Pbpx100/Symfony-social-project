//Applying infinity ajax and toogling the follow and unfollow button
$(document).ready(function () {

    let ias = new InfiniteAjaxScroll('.content-users', {
        item: '.user-item',
        next: '.pagination .link-next',
        pagination: '.pagination',

        spinner: {
            // element to show as spinner
            element: '.spinner',
            delay: 1000,

            // this function is called when the button has to be shown
            show: function (element) {
                element.style.opacity = '1'; // default behaviour
            },

            // this function is called when the button has to be hidden
            hide: function (element) {
                element.style.opacity = '0'; // default behaviour
            }
        }
    });

    ias.on('last', function () {
        let el = document.querySelector('.no-more');
        el.style.opacity = '1';
    })
    ias.on('appended', function () {
        followBtn();
        unfollowBtn();
    })
    ias.on('binded', function () {

        followBtn();
        unfollowBtn();
    })
});
function followBtn() {
    $(".btn-follow").unbind("click").click(function () {
        $(this).addClass("d-none");
        $(this).parent(".float-end").find(".btn-unfollow").removeClass("d-none");
        $.ajax({
            url: '/follow',
            data: { followed: $(this).attr('data-btn-follow') },
            type: 'POST',
            success: function (data) {
                console.log(data);

            }

        });
    });
}
function unfollowBtn() {
    $(".btn-unfollow").unbind("click").click(function () {
        $(this).addClass("d-none");
        $(this).parent(".float-end").find(".btn-follow").removeClass("d-none");
        $.ajax({
            url: '/unfollow',
            data: { followed: $(this).attr('data-btn-unfollow') },
            type: 'POST',
            success: function (data) {
                console.log(data);

            }

        });
    });
}