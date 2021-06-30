function importOverleaf(set, name, project_id, username)
{
    $.ajax({
        url: "/ajax/getCredsByUsername.php",
        method: "POST",
        data: {username: username, set: set, name: name, project_id: project_id},
        dataType: 'json',
        success: function (data) {
            runModalImport(data);
        },
        error: function (data) {
            showMessage('Document Pull', 'Error: ' + JSON.stringify(data), 'danger', 10000);
            console.log('Error!');
            console.log(data);
        }
    });
}

function runModalImport(data) {
    var debug = false;
    if (data.result['success']) {
        needsPassword = false;
    } else {
        needsPassword = true;
    }
    var set = data.result['set'];
    var name = data.result['name'];
    var project_id = data.result['project_id'];
    var username = data.result['username'];

    var debug = true;

    if (set === null) {
        showMessage('Set is empty', 'Please select a set from the given list.', 'warning', 2000);
        return;
    }
    if (needsPassword) {
        modalPassword('Pull document', 'Please authenticate ' + username, function () {
            var password = $('#modalpass').val();

            showMessage('Importing to ' + set + '...',
                '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                ''
            );

            var token = getCookie('jwToken');
            $.ajaxSetup({
                headers: {"Authorization": token}
            });

            $.ajax({
                url: "/ajax/importOverleaf.php",
                method: "POST",
                data: {'set': set, 'name': name, 'project_id': project_id, 'username': username, 'password': password},
                dataType: 'json',
                success: function (data) {
                    importOverleafSuccess(data);
                },
                error: function (data) {
                    showMessage('Import Overleaf', 'Error: ' + data.result['message'], 'danger', 10000);
                    console.log('Error!');
                    console.log(data);
                }
            });
        });
    } else {
        showMessage('Importing to ' + set + '...',
            '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Importing...</span></div></div>',
            ''
        );

        var token = getCookie('jwToken');
        $.ajaxSetup({
            headers: {"Authorization": token}
        });

        $.ajax({
            url: "/ajax/importOverleaf.php",
            method: "POST",
            data: {'set': set, 'name': name, 'project_id': project_id, 'username': username},
            dataType: 'json',
            success: function (data) {
                importOverleafSuccess(data);
            },
            error: function (data) {
                showMessage('Import Overleaf', 'Error: ' + data.result['message'], 'danger', 10000);
                console.log('Error!');
                console.log(data);
            }
        });
    }
}

function importOverleafSuccess(data)
{
    var message = '';
    var msgClass = '';
    var fadeMsec = 2500;
    if (data.result) {
        if (data.result['message']) {
            message += '<h5>' + data.result['message'] + '</h5>';
        }
        if (data.result['documentsImported']) {
            message += data.result['documentsImported'] + " article"
                + (data.result['documentsImported'] != 1 ? 's' : '')
                + " imported to set <em>" + escapeHtml(data.result['set']) + "</em>.";
            msgClass = 'success';
        } else {
            message += "No articles have been imported.";
            msgClass = 'warning';
            fadeMsec = 10000;
        }
        message += '<br />';
        var text = ['Not found', 'no tex file found', 'texfile exists', 'texfile added', 'directory exists', 'move directory error'];
        var subDirs = data.result['files'];
        message += '<small>';
        for (var prop in subDirs) {
            // if string, a file had been added
            if (typeof subDirs[prop] === 'string' || subDirs[prop] instanceof String) {
                message += prop + ": " + 'texfile ' + subDirs[prop] + ' added';
            } else {
                message += prop + ": " + text[subDirs[prop]]
            }
            message += '.<br />';
        }
        message += '</small>';
        showMessage('Article scan on <em>' + escapeHtml(data.result['set'] + '/' + data.result['name']) + '</em>', message, msgClass, fadeMsec);
    }
}
