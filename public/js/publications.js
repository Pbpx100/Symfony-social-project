//Setting infinity ajax and toogle to comments - read comments - counting likes - unlike
$(document).ready(function () {

    let ias = new InfiniteAjaxScroll('.content-publications', {
        item: '.publication-item',
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
        countLikes();
        addComment()
        readComment();
    })
    ias.on('binded', function () {
        btn_tootip();
        likeBtn();
        unlikeBtn();
        countLikes();
        addComment();
        readComment();


    })
});

// function i like tooltip te dice el titulo

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
                countLikes();
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
                countLikes();
                console.log(data);

            }

        });
    });
}
function countLikes() {
    $.ajax({
        url: '/countLikes',
        type: 'POST',
        success: function (data) {
            // console.log(data[0]['likes'][0]['countlikes'])
            data.forEach(task => {
                task.likes.forEach(likesp => {
                    let content = '<small>likes ' + likesp.countlikes + '</small>';
                    $('#like' + task.id).html(content);
                });
            });

            // data.forEach(task => {
            //     let content = '<small>likes ' + task.likes.countlikes + '</small>';
            //     console.log(content);
            //     $('#like' + task.id).html(content);
            // });
        }
    });
}
function addComment() {
    $('.commentBtn').unbind('click').click(function () {
        let publication_id = $(this).attr('id');
        let comment = $('#commentText' + publication_id).val();
        const postData = {
            publication_id: publication_id,
            comment: comment,
        };
        $.post('/addComment', postData, function (data) {
            //the data return the the information come back from the php , we send the information with post to the route and we recuperate data
            $('#commentText' + publication_id).val(null);
            readComment();
            console.log(data);
        });
    });
}
function readComment() {
    //here i put null cause i am ot sending data
    $.post('/readComments', null, function (data) {
        let content = '';
        data.forEach(comment => {
            //Here we are giving as id the date of the comment, the iterator is the comment of the data and the key is tje comment.comment_date and we are replacing the parameters of the date form
            let id = comment.comment_date.replace(/\s/g, "").replace("-", "").replace("-", "").replace(":", "").replace(":", "")      //this comment_date we are takiing from the commentController.php the data
            if ($('#' + id).length == false) {
                let image = comment.comment_u_image; //here we are saving in the variable the image od the user witch comment
                if (image == null) {
                    image = 'default.png';
                }//end if 2
                //console.log($("#content" + comment.comment_p_id));
                $("#content" + comment.comment_p_id).append('<div id="' + id + '">' +
                    `
                    <div class="card-body">
                <img src="img/${image}" width="50em" height="50em"
                class="float-left me-3 rounded-circle">
                <h6 class="mt-0">${comment.comment_u_name}
                ${comment.comment_u_surname}
                ${comment.comment_u_nick}</h6>
                <small> ${comment.comment_text}</small>
                </div>
                <small class="text-muted float-right mr-4">${comment.comment_date}</small>
                <br>

                `
                    + '</div>');


            }//end if 1


        });//End foreach


    });//End post function

}//end function
