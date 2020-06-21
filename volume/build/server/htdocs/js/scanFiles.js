function scanFiles(set)
{
    var debug = false;
    if (set === null) {
        showMessage('Set is empty', 'Please select a set from the given list.', 'warning', 2000);
        return;
    }
    showMessage('Scanning ' + set + '...',
                '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Scanning and importing...</span></div></div>',
                ''
    );

    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });

    $.ajax({
        url: "/ajax/scanFiles.php?set="+set,
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
                if (data.result['documentsImported']) {
                    message += data.result['documentsImported'] + " article" + (data.result['documentsImported'] != 1 ? 's' : '') + " imported to set <em>" + data.result['destSet'] + "</em>.";
                    msgClass = 'success';
                } else {
                    message += "No articles have been imported.";
                    msgClass = 'warning';
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
                showMessage('Article scan on set <em>' + data.result['destSet'] + '</em>', message, msgClass, 2500);
            }
        },
        error: function(data) {
            showMessage('Document Delete', 'Error: ' + JSON.stringify(data), 'danger', 10000);
            console.log('Error!');
            console.log(data);
        }
    });
}
