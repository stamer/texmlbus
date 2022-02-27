function dequeueDocument(element, id, stage)
{
    var debug = false;

    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });

    $.ajax({
        url: "/ajax/dequeueDocument.php?id=" + id + "&stage=" + stage,
        method: "GET",
        dataType: 'json',
        success: function(data) {
            if (debug) {
                console.log(JSON.stringify(data.result));
            }
            var message = '';
            var msgClass = '';
            var mSec = 1500;

            if (data.result) {
                if (data.result['message']) {
                    message += '<h5>' + data.result['message'] + '</h5>';
                }
                if (data.result['success']) {
                    message += "Document dequeued.";
                    msgClass = 'success';
                    fadeMsec = 1500;
                } else {
                    message += "No document has been dequeued.";
                    msgClass = 'warning';
                }
                showMessage('Document Dequeue', message, msgClass, fadeMsec);
                $(element).closest('tr').hide();
            }
        },
        error: function(data) {
                showMessage('Document Dequeue', 'Error: ' + JSON.stringify(data), 'danger', 10000);
                console.log('Error!');
                console.log(data);
        }
    });

    return;
}
