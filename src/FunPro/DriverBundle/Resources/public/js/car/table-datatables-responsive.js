var addPlaque = function (td, cellData, rowData, row, col) {
    var html = '<span class="label label-info">ایران'
        + '<span style="vertical-align: bottom">'+cellData.cityNumber+'</span></span>&nbsp;<span style="font-size: medium" class="label label-info">' +
        cellData.firstNumber + cellData.areaCode + cellData.secondNumber + '</span>';
    $(td).html(html);
};

var TableDatatablesResponsive = function () {

    var initTable = function () {
        var table = $('#sample_2');

        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": "",

            // Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "No data available in table",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered1 from _MAX_ total entries)",
                "lengthMenu": "_MENU_ entries",
                "search": "Search:",
                "zeroRecords": "No matching records found"
            },

            // Or you can use remote translation file
            //"language": {
            //   url: '//cdn.datatables.net/plug-ins/3cfcc339e89/i18n/Portuguese.json'
            //},

            // setup buttons extentension: http://datatables.net/extensions/buttons/
            buttons: [
                { extend: 'print', className: 'btn dark btn-outline' },
                { extend: 'pdf', className: 'btn green btn-outline' },
                { extend: 'csv', className: 'btn purple btn-outline ' }
            ],

            // setup responsive extension: http://datatables.net/extensions/responsive/
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            columns: [
                {"defaultContent": ""},
                {name: "c.plaque", data: "plaque", orderable: false, searchable: false, className: "plaque", "createdCell": addPlaque},
                {name: "c.brand", data: "brand", orderable: true, searchable: true},
                {name: "c.type", data: "type", orderable: true, searchable: true},
                {name: "c.color", data: "color", orderable: false, searchable: false},
                {name: "c.born", data: "born", orderable: true, searchable: false},
                {name: "c.rate", data: "rate", orderable: true, searchable: false},
                {name: "c.description", data: "description", "defaultContent": "", orderable: false, searchable: false},
                {name: "c.current", data: "current", orderable: true, searchable: false, className: "isCurrent"},
                {name: "c.status", data: "status", orderable: false, searchable: false},
                {name: "edit", "defaultContent": "<i class='btn btn-warning'>ویرایش</i>", orderable: false, searchable: false, className: "edit text-center"},
                {name: "delete", "defaultContent": "<i class='btn btn-danger'>حذف</i>", orderable: false, searchable: false, className: "delete text-center"},
            ],

            order: [ 1, 'asc' ],

            // pagination control
            "lengthMenu": [
                [5, 10, 15, 20, 50, 100],
                [5, 10, 15, 20, 50, 100] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,
            "pagingType": 'bootstrap_extended', // pagination type

            "dom": "<'row' <'col-md-12'B>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
            // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js).
            // So when dropdowns used the scrollable div should be removed.
            //"dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r>t<'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>",
        });
    }

    return {

        //main function to initiate the module
        init: function () {

            if (!jQuery().dataTable) {
                return;
            }

            initTable();
        }

    };

}();

jQuery(document).ready(function() {
    TableDatatablesResponsive.init();

    $('#sample_2').on( 'draw.dt', function () {
        $('.isCurrent').each(function(index, item) {
            if ($(item).text() == 'true') {
                $(item).html('<button class="btn btn-info glyphicon glyphicon-ok changeStatus"></button>')
            } else if ($(item).text() == 'false') {
                $(item).html('<button class="btn btn-info glyphicon glyphicon-remove changeStatus"></button>')
            }
        });
    } );
});