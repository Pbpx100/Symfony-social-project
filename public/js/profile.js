
//Setting infinity ajax scroll and applying toogle to button like - unlike  and follow .- unfollow
$(document).ready(function () {

    let ias = new InfiniteAjaxScroll('.content-profile', {
        item: '.profile-item"',
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
        btn_tootip();
        likeBtn();
        unlikeBtn();
        followBtn();
        unfollowBtn();
    })
    ias.on('binded', function () {
        btn_tootip();
        likeBtn();
        unlikeBtn();
        followBtn();
        unfollowBtn();

    })
});


// Displaying button like or unlike with toogle - sending information - Ajax
function btn_tootip() {
    $('[data-toggle="tooltip"]').tooltip();
}
function likeBtn() {
    $(".like-btn").unbind("click").click(function () {
        $(this).addClass("d-none");
        $(this).parent().find(".unlike-btn").removeClass("d-none");
        $.ajax({
            url: '/like',
            data: { publication: $(this).attr('data-btn-like') },
            type: 'POST',
            success: function (data) {
                console.log(data);

            }

        });
    });
}
function unlikeBtn() {
    $(".unlike-btn").unbind("click").click(function () {
        $(this).addClass("d-none");
        $(this).parent().find(".like-btn").removeClass("d-none");
        $.ajax({
            url: '/unlike',
            data: { publication: $(this).attr('data-btn-unlike') },
            type: 'POST',
            success: function (data) {
                console.log(data);

            }

        });
    });
}


function followBtn() {
    $(".btn-follow").unbind("click").click(function () {
        $(this).addClass("d-none");
        $(this).parent(".float-end").find(".bnt-unfollow").removeClass("d-none");
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
        $(this).parent(".float-end").find(".bnt-follow").removeClass("d-none");
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