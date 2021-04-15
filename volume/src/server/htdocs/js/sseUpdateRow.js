/**
 * updates a row via SSE
 * @param set
 * @param stage
 * @param retval
 */
function sseUpdateRow(set, stage, retval)
{
    const delayBeforeRemove = 2000;

    if (window.EventSource) {
        var evtSource = new EventSource('/sse/sseUpdateRow.php?set=' + set + '&stage=' + stage + '&retval=' + retval);

        evtSource.onopen = function () {
            // console.log("Connection to server opened.");
        };

        evtSource.onerror = function () {
            console.log("EventSource failed.");
        };

        window.onbeforeunload = function () {
            evtSource.close();
        };

        evtSource.addEventListener("updaterow", function (e) {
            var obj = JSON.parse(e.data);
            if (!obj) {
                console.log("EventSource event failed.");
                return;
            }
            var row = document.getElementById(obj.fieldid);
            if (row) {
                /* get the current countnr and replace it */
                tdcount = document.getElementById(obj.countid);
                if (tdcount) {
                    var newhtml = obj.html.replace('__COUNT__', tdcount.textContent);
                } else {
                    var newhtml = obj.html.replace('__COUNT__', '');
                }
                row.outerHTML = newhtml;
            }
        }, false);

        // show the updated status, then, after some time, delete complete row.
        evtSource.addEventListener("deleterow", function (e) {
            var obj = JSON.parse(e.data);
            if (!obj) {
                console.log("EventSource event failed.");
                return;
            }
            var row = document.getElementById(obj.fieldid);
            /* get the current countnr and replace it */
            tdcount = document.getElementById(obj.countid);
            if (row) {
                if (tdcount) {
                    var newhtml = obj.html.replace('__COUNT__', tdcount.textContent);
                } else {
                    var newhtml = obj.html.replace('__COUNT__', '');
                }
                row.outerHTML = newhtml;
                setTimeout(deleteRow, delayBeforeRemove, obj.fieldid);
            }
        }, false);
    }
}

function deleteRow(fieldid)
{
    row = document.getElementById(fieldid);
    if (row) {
        row.remove();
    }
}