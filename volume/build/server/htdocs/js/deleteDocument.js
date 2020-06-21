function deleteDocument(element, id)
{
    var debug = false;
    modalConfirm('Delete document', 'Do you really want to delete this document?', function() {
        showMessage('Deleting document...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                    ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: { "Authorization": token }
        });

        $.ajax({
            url: "/ajax/deleteDocument.php?id=" + id,
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
                    if (data.result['documentsDeleted']) {
                        message += data.result['documentsDeleted'] + " document" + (data.result['documentsDeleted'] != 1 ? 's' : '') + " deleted.";
                        msgClass = 'success';
                    } else {
                        message += "No documents have been deleted.";
                        msgClass = 'warning';
                    }
                    showMessage('Document Delete', message, msgClass, 1500);
                    $(element).closest('tr').hide();
                    $(element).closest('tr').next().hide();
                }
            },
            error: function(data) {
                    showMessage('Document Delete', 'Error: ' + JSON.stringify(data), 'danger', 10000);
                    console.log('Error!');
                    console.log(data);
            }
        });
    }, function () {
        showMessage('Delete document', 'Delete action canceled.', 'info', 1500);
    });

    return;
}
