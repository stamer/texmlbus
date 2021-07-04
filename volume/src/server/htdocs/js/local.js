function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cookieName) {
    var name = cookieName + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function openHelp(id) {
    showUrlInModal('/ajax/getHelp.php?id=' + id);
}

function showUrlInModal(url, options){
    options = options || {};
    var tag = $("#myModal");
    if (!tag.length) {
        tag = $("<div id='myModal'></div>"); // This tag will then hold the dialog content.
    }
    $.ajax({
        url: url,
        type: (options.type || 'GET'),
        beforeSend: options.beforeSend,
        error: options.error,
        complete: options.complete,
        success: function(data, textStatus, jqXHR) {
            $('.modal-header').attr('class', 'modal-header bg-info-t');
            if (typeof data == "object" && data.html) { // Response is assumed to be JSON.
                $('.modal-title').html(data.title);
                $('.modal-body').html(data.html);
            } else { //response is assumed to be HTML
                $('.modal-title').html(options.title);
                $('.modal-body').html(data);
            }
            $('#myModal').modal('show');
        }
    });
}

/**
 * returns the x coordinate for the centered message box.
 */
function getMsgXPos(msgwidth)
{
	var xpos;
	var docwidth = $(document).width();

	xpos = parseInt((docwidth * 0.5) - (msgwidth * 0.5));
	//alert(docwidth+' '+msgwidth+' '+xpos);

	return xpos + 'px';
}

function showMessage(title, message, msgClass = 'info', fadeMsec = 0)
{
    if (msgClass != '') {
        msgClass = 'bg-' + msgClass + '-t';
    }
    $('.modal-header').attr('class', 'modal-header ' + msgClass);
    $('.modal-title').html(title);
    $('.modal-body').html(message);
    $('#myModal').modal('show');
    if (fadeMsec) {
        //$('#myModal').fadeTo(fadeMsec, 0.8, hideMessageBox);
        setTimeout(function() {
                $('#myModal').modal('hide');
            }, fadeMsec
        );
    }
}

function modalConfirm(title, question, onConfirm, onCancel = null)
{
    var fClose = function(){
        $('#myModal').modal("hide");
        $('.modal-footer').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>');
    };

    var delayedOnConfirm = function() {
        setTimeout(onConfirm, 500);
    }

    var delayedOnCancel = function() {
        setTimeout(onCancel, 500);
    }

    $('.modal-header').attr('class', 'modal-header');
    $('.modal-title').html(title);
    $('.modal-body').html(question);
    $('.modal-footer').html('<button type="button" class="btn btn-secondary" id="confirmOk">Ok</button>\n' +
        '            <button type="button" class="btn btn-secondary" id="confirmCancel">Cancel</button>\n');
    $('#confirmOk').unbind().one('click', fClose).one('click', delayedOnConfirm);
    $('#confirmCancel').unbind().one("click", fClose).one('click', delayedOnCancel);

    $('#myModal').modal('show');
}

function modalPassword(title, text, onLogin, onCancel = null)
{
    var fClose = function(){
        $('#myModal').modal("hide");
        $('.modal-footer').html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>');
    };

    var delayedOnLogin = function(event) {
        setTimeout(onLogin, 500);
    }

    var delayedOnCancel = function() {
        setTimeout(onCancel, 500);
    }

    $('.modal-header').attr('class', 'modal-header');
    $('.modal-title').html(title);

    $('.modal-body').html(text
        + '<form id="loginForm">'
        + '<div class="form-group"> '
        + '<label for="modalpass" class="col-form-label">Password:</label>'
        + '<input type="password" name="modalpass" class="form-control" id="modalpass">'
        + '</div>'
        + '<div class="form-check">'
        + '<input type="checkbox" class="form-check-input" name="modalcache" id="modalcache">'
        + '<label class="form-check-label" for="modalcache">Cache credentials</label>'
        + '</div>'
        + '<div style="margin-top: 20px; text-align: end">'
        + '    <button type="button" id="submitLogin" class="btn btn-primary">Submit</button>'
        + '</div>'
        + '</form>'
    );

    $('.modal-footer').html('<button type="button" class="btn btn-secondary" id="confirmCancel">Cancel</button>\n');
    $('#submitLogin').unbind().one('click', fClose).one('click', delayedOnLogin);
    $('#confirmCancel').unbind().one("click", fClose).one('click', delayedOnCancel);

    $("#loginForm").submit(function(event) {
        event.preventDefault();
    });

    $('#myModal').modal('show');
}

function hideMessageBox()
{
    $('#myModal').hide();
}

function cleanupID(str)
{
	// . and : are valid, but do not work well with jquery.
	return str.replace(/[\\.:@]/g);
}

function rerunById(id, stage, target)
{
    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });
    $.post('/api/rerun',
        {'id':id, 'stage':stage, 'target':target},
        function(data) {
            if (data.success) {
                var msg_class = 'success';
            } else {
                var msg_class = 'warning';
            }
            if (data.output) {
                showMessage('Rerun', data.output, 'info', 2000);
            }

            var field = '#rerun_' + id + '_' + stage;
            if (data.success) {
                $(field).html('<span class="ok">'+'queued'+'</span>');
            } else {
                $(field).html('<span class="error">'+'error'+'</span>');
            }
        }, "json"
    );
}

function rerunByIds(ids, stage, target)
{
    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: {"Authorization": token}
    });
    $.post('/api/rerun',
        {'ids': ids, 'stage': stage, 'target': target},
        function (data) {
            if (data.successArray) {
                Object.entries(data.successArray).forEach(([id, value]) => {
                    var field = '#rerun_' + id + '_' + stage;
                    if (value) {
                        $(field).html('<span class="ok">' + 'queued' + '</span>');
                    } else {
                        $(field).html('<span class="error">' + 'error' + '</span>');
                    }
                });
            }
        }, "json"
    );
}

function rerunBySet(set, stage, target) {
    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: {"Authorization": token}
    });
    $.post('/api/rerun',
        {'set': set, 'stage': stage, 'target': target},
        function (data) {
            if (data.successArray) {
                Object.entries(data.successArray).forEach(([id, value]) => {
                    var field = '#rerun_' + id + '_' + stage;
                    if (value) {
                        $(field).html('<span class="ok">' + 'queued' + '</span>');
                    } else {
                        $(field).html('<span class="error">' + 'error' + '</span>');
                    }
                });
            }
        }, "json"
    );
}

function createSnapshotBySet(set)
{
    var token = getCookie('jwToken');
    $.ajaxSetup({
        headers: { "Authorization": token }
    });
    $.post('/api/snapshot',
        { 'set':set},
        function(data) {
            if (data.success) {
                var msg_class = 'success';
            } else {
                var msg_class = 'warning';
            }
            if (data.output) {
                showMessage('Create snapshot', data.output, 'info', 2000);
            }

            var field = '#snapshot';
            if (data.success) {
                $(field).html('<span class="ok">'+'created'+'</span>');
                location.reload(true);
            } else {
                $(field).html('<span class="error">'+'error'+'</span>');
            }
        }, "json"
    );
    return false;
}

function selfUpdate(seconds)
{
    setTimeout(function() {
            // deactivate reload if modal is open
            if (!$('#myModal').is(':visible')) {
                location.reload(true);
            } else {
                selfUpdate(seconds);
            }
        }, seconds);
}
