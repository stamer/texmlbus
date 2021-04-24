function fixMakefiles()
{
    var debug = false;
    showMessage('Fixing Makefiles ...',
                '<div class="text-center"><div class="spinner-grow text-warning" role="status"><span class="sr-only">Fixing Makefiles...</span></div></div>',
                ''
    );

    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });

    $.ajax({
        url: "/ajax/fixMakefiles.php",
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
                if (data.result['makefilesFixed']) {
                    message += data.result['makefilesFixed'] + " Makefile" + (data.result['makefilesFixed'] != 1 ? 's' : '') + " fixed.";
                    msgClass = 'success';
                } else {
                    message += "No Makefiles have been fixed.";
                    msgClass = 'warning';
                }
                message += '<br />';
                showMessage('Makefiles fixed', message, msgClass, 2500);
            }
        },
        error: function(data) {
            showMessage('Makefile fixed', 'Error: ' + JSON.stringify(data), 'danger', 10000);
            console.log('Error!');
            console.log(data);
        }
    });
}
