var selectedItems = [];
$(document).ready(function () {
    table = $('.datatable-ajax').DataTable();

    table.on('xhr.dt', function (e, settings, json, xhr) {
        $('#select-all-drop-down button').removeAttr("disabled")
    });
    table.on('select', function (e, dt, type, indexes) {
        if (type === 'row') {
            var data = table.rows(indexes).nodes().to$();
            var id = data.find(".entityId").data("id");
            selectedItems.push(id);
            showHideMassUpdateBtns();
        }
    });
    table.on('deselect', function (e, dt, type, indexes) {
        if (type === 'row') {
            var data = table.rows(indexes).nodes().to$();
            var id = data.find(".entityId").data("id");

            for (var i = 0; i < selectedItems.length; i++) {
                if (selectedItems[i] === id) {
                    selectedItems.splice(i, 1);
                    break;
                }
            }
            showHideMassUpdateBtns();
        }
    });

    $("body").on("click", ".select-all-items", (function (e) {
        console.log(table.rows());
        table.rows().select();
        selectedItems.splice(0, selectedItems.length)
        $(".datatable-ajax .entityId").each(function () {
            selectedItems.push($(this).data("id"));
        }).promise().done(function () {
            $("#select-all-drop-down button:first-child").removeClass("select-all-items").addClass("deselect-all-items");
            $("#select-all-drop-down button:first-child i").removeClass("icon-checkbox-unchecked").addClass("icon-checkbox-checked2");
            showHideMassUpdateBtns();
        });

    }));

    $("body").on("click", ".deselect-all-items", (function (e) {
        table.rows().deselect();
        selectedItems.splice(0, selectedItems.length)
        $("#select-all-drop-down button:first-child").removeClass("deselect-all-items").addClass("select-all-items");
        $("#select-all-drop-down button:first-child i").removeClass("icon-checkbox-checked2").addClass("icon-checkbox-unchecked");

        showHideMassUpdateBtns();
    }));
});
function showHideMassUpdateBtns() {
    if (selectedItems.length == 0) {
        $("#mass-update-btn").addClass("hidden");
    } else {
        $("#mass-update-btn").removeClass("hidden");
    }
    if (selectedItems.length > 0) {
        $("#select-all-drop-down button:first-child span.text").text(selectedItems.length + " items selected");
    } else {
        $("#select-all-drop-down button:first-child span.text").text("");
    }
}
function getSelectedItemsInGETParam(arrayParamName) {
    var s = "";
    for (var i = 0; i < selectedItems.length; i++) {
        if (i > 0) {
            s += "&";
        }
        s += arrayParamName + "[]=" + selectedItems[i];
    }
    return s;
}

function openNewWindow(url, arrayParamName) {
    var params = getSelectedItemsInGETParam(arrayParamName);
    url = url + "?" + params;
    alert(params);
    alert(url);
    window.open(url, 'window');

}