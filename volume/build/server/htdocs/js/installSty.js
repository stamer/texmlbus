function installSty(element, name)
{
    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });

    $(element).html('<i class="fas fa-spinner fa-spin"></i><span></span>');

    $.ajax({
        url: "/ajax/installSty.php?name=" + name,
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
                if (data.result['installed']) {
                    message += ' <em>' + data.result['installedCls'] + '</em> installed.';
                    msgClass = 'success';
                    $(element).removeClass();
                    $(element).addClass('btn btn-success install');
                    $(element).html('<i class="fas fa-chevron-down"></i><span></span>');
                    fadeMsec = 1000;
                } else {
                    message += "No files have been installed.";
                    $(element).removeClass();
                    $(element).addClass('btn btn-primary');
                    $(element).html('<i class="fas fa-cloud-download-alt"></i><span></span>');
                    msgClass = 'info';
                }
                showMessage('Sty Files Install', message, msgClass, 2500);
            }
        },
        error: function (data) {
            console.log('Error!');
            console.log(data);
        }
    });
}
