function onload() {
    var tds = document.getElementsByTagName("td");
    for (var i = 0; i < tds.length; i++) {
        tds[i].onclick =
            function (td) {
                return function () {
                    tdOnclick(td);
                };
            }(tds[i]);
    }
    var inputs = document.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].onclick =
            function (input) {
                return function () {
                    inputOnclick(input);
                };
            }(inputs[i]);
    }
}
function tdOnclick(td) {
    for (var i = 0; i < td.childNodes.length; i++) {
        if (td.childNodes[i].nodeType == 1) {
            if (td.childNodes[i].nodeName == "INPUT") {
                if (td.childNodes[i].checked) {
                    td.childNodes[i].checked = false;
                } else {
                    td.childNodes[i].checked = true;
                }
            } else {
                tdOnclick(td.childNodes[i]);
            }
        }
    }
}
function inputOnclick(input) {
    input.checked = !input.checked;
    return false;
}