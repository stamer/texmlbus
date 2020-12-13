function sseUpdateColumn() {
    if (window.EventSource) {
        var evtSource = new EventSource('/sse/sseUpdateColumn.php');
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

        evtSource.addEventListener("updatecolumn", function (e) {
            var obj = JSON.parse(e.data);
            if (!obj) {
                console.log("EventSource event failed.");
                return;
            }
            var column = document.getElementById(obj.fieldid);
            if (column) {
                column.outerHTML = obj.html;
                column.style.backgroundColor = obj.bgcolor;
            }
        }, false);
    }
}
