function deleteSet(element, set)
{
    modalConfirm('Delete set', 'Articles will be deleted in database and also from the filesystem.<br />Do you really want to delete set <em>' + set + '</em>?', function() {
        showMessage('Deleting ' + set + '...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                    ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: { "Authorization": token }
        });

        $.ajax({
            url: "/ajax/deleteSet.php?set=" + set,
            method: "GET",
            dataType: 'json',
            success: function (data) {
                if (false) {
                    alert(JSON.stringify(data.result));
                }
                var message = '';
                var msgClass = '';
                var fadeMsec = 2500;

                if (data.result) {
                    if (data.result['message']) {
                        message += '<h5>' + data.result['message'] + '</h5>';
                    }
                    if (data.result['documentsDeleted']) {
                        message += data.result['documentsDeleted'] + " article" + (data.result['documentsDeleted'] != 1 ? 's' : '') + " deleted from set <em>" + data.result['destSet'] + "</em>.";
                        msgClass = 'success';
                        fadeMsec = 1000;
                    } else {
                        message += "No articles have been deleted.";
                        msgClass = 'info';
                    }
                    showMessage('Sets Delete', message, msgClass, 2500);
                    $(element).parent('div').hide();
                }
            },
            error: function (data) {
                console.log('Error!');
                console.log(data);
            }
        });
    }, function() {
            showMessage('Deleting set ' + set, 'Delete action canceled.', 'info', 1500);
    });
}
