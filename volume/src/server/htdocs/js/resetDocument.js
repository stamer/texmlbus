function resetDocument(element, id)
{
    var debug = false;
    modalConfirm('Reset document', 'Do you really want to reset this document?', function() {
        showMessage('Resetting document...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Resetting document...</span></div></div>',
                    ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: { "Authorization": token }
        });

        $.ajax({
            url: "/ajax/resetDocument.php?id=" + id,
            method: "GET",
            dataType: 'json',
            success: function(data) {
                if (debug) {
                    console.log(JSON.stringify(data.result));
                }
                var message = '';
                var msgClass = '';
                var fadeMsec = 1500;

                if (data.result) {
                    if (data.result['message']) {
                        message += '<h5>' + data.result['message'] + '</h5>';
                    }
                    if (data.result['stagesReset']) {
                        message += data.result['stagesReset'] + " stage" + (data.result['stagesReset'] != 1 ? 's' : '') + " reset.";
                        msgClass = 'success';
                        fadeMsec = 1000;
                    } else {
                        message += "No stages have been reset.";
                        msgClass = 'warning';
                    }
                    showMessage('Document Reset', message, msgClass, fadeMsec);
                }
            },
            error: function(data) {
                    showMessage('Document Reset', 'Error: ' + JSON.stringify(data), 'danger', 10000);
                    console.log('Error!');
                    console.log(data);
            }
        });
    }, function () {
        showMessage('Reset document', 'Reset action canceled.', 'info', 1500);
    });

    return;
}
