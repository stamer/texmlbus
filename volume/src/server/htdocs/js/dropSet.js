function dropSet(element, set)
{
    modalConfirm('Drop set', 'Articles will be removed from DB, but will stay in filesystem.<br />Do you really want to drop set <em>' + set + '</em>?', function() {
        showMessage('Deleting ' + set + '...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                    ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: { "Authorization": token }
        });

        $.ajax({
            url: "/ajax/dropSet.php?set=" + set,
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
                    if (data.result['documentsDropped']) {
                        message += data.result['documentsDropped'] + " article" + (data.result['documentsDropped'] != 1 ? 's' : '') + " dropped from set <em>" + data.result['destSet'] + "</em>.";
                        msgClass = 'success';
                        fadeMsec = 1000;
                    } else {
                        message += "No articles have been dropped.";
                        msgClass = 'info';
                    }
                    showMessage('Sets Drop', message, msgClass, 2500);
                    $(element).parent('div').hide();
                }
            },
            error: function (data) {
                console.log('Error!');
                console.log(data);
            }
        });
    }, function() {
            showMessage('Dropping set ' + set, 'Drop action canceled.', 'info', 1500);
    });
}
