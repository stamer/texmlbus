function deleteSty(element, isDir, file)
{
    if (isDir === 'true') {
        var item = 'directory';
        var upItem = 'Directory';
    } else {
        var item = 'file';
        var upItem = 'File';
    }
    modalConfirm('Delete files', 'Do you really want to delete ' + item + ' ' + file + '?', function() {
        showMessage('Deleting ' + file + '...',
                    '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Deleting ' + item + '...</span></div></div>',
                    ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: { "Authorization": token }
        });

        $.ajax({
            url: "/ajax/deleteSty.php?file=" + file + '&item=' + item,
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
                    if (data.result['filesDeleted']) {
                        message += upItem + ' <em>' + data.result['destFile'] + '</em> deleted.';
                        msgClass = 'success';
                        fadeMsec = 1000;
                    } else {
                        message += "No files have been deleted.";
                        msgClass = 'info';
                    }
                    showMessage('Sty Files Delete', message, msgClass, 2500);
                    if (item === 'directory') {
                        $(element).parent('div').next('ul').hide();
                    }
                    $(element).parent('div').hide();
                }
            },
            error: function (data) {
                console.log('Error!');
                console.log(data);
            }
        });
    }, function() {
            showMessage('Deleting ' + item + ' ' + file, 'Delete action canceled.', 'info', 1500);
    });
}
