/**
 * updates a row via SSE
 * @param set
 * @param stage
 * @param retval
 */
function sseUpdateRow(set, stage, retval) {
    if (window.EventSource) {
        var evtSource = new EventSource('/sse/sseUpdateRow.php?set=' + set + '&stage=' + stage + '&retval=' + retval);
        var eventList = document.querySelector('ul');

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
    }
}