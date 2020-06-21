function dequeueDocument(element, id)
{
    var debug = false;

    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });

    $.ajax({
        url: "/ajax/dequeueDocument.php?id=" + id,
        method: "GET",
        dataType: 'json',
        success: function(data) {
            if (debug) {
                console.log(JSON.stringify(data.result));
            }
            var message = '';
            var msgClass = '';
            if (data.result) {
                if (data.result['message']) {
                    message += '<h5>' + data.result['message'] + '</h5>';
                }
                if (data.result['success']) {
                    message += "Document dequeued.";
                    msgClass = 'success';
                } else {
                    message += "No document has been dequeued.";
                    msgClass = 'warning';
                }
                showMessage('Document Dequeue', message, msgClass, 800);
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
