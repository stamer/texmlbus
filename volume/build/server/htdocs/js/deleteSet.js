function deleteSet(element, set)
{
    modalConfirm('Delete set', 'Do you really want to delete set ' + set + '?', function() {
        showMessage('Deleting ' + set + '...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                    ''
        );

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
                if (data.result) {
                    if (data.result['message']) {
                        message += '<h5>' + data.result['message'] + '</h5>';
                    }
                    if (data.result['documentsDeleted']) {
                        message += data.result['documentsDeleted'] + " article" + (data.result['documentsDeleted'] != 1 ? 's' : '') + " deleted from set <em>" + data.result['destSet'] + "</em>.";
                        msgClass = 'success';
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
