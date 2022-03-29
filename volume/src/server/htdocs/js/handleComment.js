function handleComment(id)
{
    $.ajax({
        url: "/ajax/getCommentById.php",
        method: "POST",
        data: {id: id},
        dataType: 'json',
        success: function (data) {
            handleModalComment(data);
        },
        error: function (data) {
            showMessage('Comment', 'Error: ' + JSON.stringify(data), 'danger', 10000);
            console.log('Error!');
            console.log(data);
        }
    });
}

function handleModalComment(data) {
    var debug = false;
    var id = data.result['id'];
    var filename = data.result['filename'];
    var comment = data.result['comment'];
    var enum_comment_status = data.result['enum_comment_status'];
    var comment_status = data.result['comment_status'];
    var comment_date = data.result['comment_date'];

    var debug = true;

    modalComment('Comment', filename, comment, enum_comment_status, comment_status, comment_date,function () {
        var comment = $('#modalcomment').val();
        var comment_status = $('#modalcomment_status').val();
        showMessage('Saving comment...',
            '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Saving comment...</span></div></div>',
            ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: {"Authorization": token}
        });

        $.ajax({
            url: "/ajax/saveComment.php",
            method: "POST",
            data: {'id': id, 'comment': comment, 'comment_status': comment_status},
            dataType: 'json',
            success: function (data) {
                saveCommentSuccess(data);
            },
            error: function (data) {
                showMessage('Comment', 'Error: ' + data.result['message'], 'danger', 10000);
                console.log('Error!');
                console.log(data);
            }
        });
    });
}

function saveCommentSuccess(data)
{
    var message = '';
    var msgClass = '';
    var fadeMsec = 1500;
    if (data.result) {
        if (data.result['message']) {
            message += '<h5>' + data.result['message'] + '</h5>';
        }
        if (data.result['success']) {
            message += 'Comment saved.';
            msgClass = 'success';
        } else {
            message += "Failed to save comment.";
            msgClass = 'warning';
            fadeMsec = 10000;
        }
        message += '<br />';
        showMessage('Comment', message, msgClass, fadeMsec);
    }
}
