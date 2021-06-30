function pullDocument(element, id) {
    $.ajax({
        url: "/ajax/getCredsById.php",
        method: "POST",
        data: {id: id},
        dataType: 'json',
        success: function (data) {
            runModal(data);
        },
        error: function (data) {
            showMessage('Document Pull', 'Error: ' + JSON.stringify(data), 'danger', 10000);
            console.log('Error!');
            console.log(data);
        }
    });
}

function runModal(data) {
    var debug = false;
    if (data.result['success']) {
        needsPassword = false;
    } else {
        needsPassword = true;
    }
    var id = data.result['id'];
    var username = data.result['username'];

    if (needsPassword) {
        modalPassword('Pull document from Overleaf', 'Please authenticate <em>' + username + '</em>:', function () {
            var password = $('#modalpass').val();
            showMessage('Pulling document...',
                '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Pulling...</span></div></div>',
                ''
            );

            var token = getCookie('jwToken');
            $.ajaxSetup({
                headers: {"Authorization": token}
            });

            $.ajax({
                url: "/ajax/pullDocument.php",
                method: "POST",
                data: {id: id, password: password},
                dataType: 'json',
                success: function (data) {
                    pullDocumentSuccess(data);
                },
                error: function (data) {
                    showMessage('Document Pull', 'Error: ' + JSON.stringify(data), 'danger', 10000);
                    console.log('Error!');
                    console.log(data);
                }
            });
        }, function () {
            showMessage('Pull document', 'Pull action canceled.', 'info', 1500);
        });
    } else {
        showMessage('Pulling document...',
            '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Pulling...</span></div></div>',
            ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: {"Authorization": token}
        });

        $.ajax({
            url: "/ajax/pullDocument.php",
            method: "POST",
            data: {id: id},
            dataType: 'json',
            success: function (data) {
                pullDocumentSuccess(data);
            },
            error: function (data) {
                showMessage('Document Pull', 'Error: ' + JSON.stringify(data), 'danger', 10000);
                console.log('Error!');
                console.log(data);
            }
        });
    }
}

function pullDocumentSuccess(data)
{
    var message = '';
    var msgClass = '';
    var fadeMsec = 800;

    if (data.result) {
        if (data.result['message']) {
            message += '<h5>' + data.result['message'] + '</h5>';
        }
        if (data.result['success']) {
            message += "Document updated.";
            msgClass = 'success';
        } else {
            message += "Document has not been updated.";
            msgClass = 'warning';
            fadeMsec = 10000;
        }
        showMessage('Document Pull', message, msgClass, fadeMsec);
    }
}