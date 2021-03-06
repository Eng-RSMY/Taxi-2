var addPlaque = function (td, cellData, rowData, row, col) {
    var html = '<span class="label label-info">ایران'
        + '<span style="vertical-align: bottom">'+cellData.cityNumber+'</span></span>&nbsp;<span style="font-size: medium" class="label label-info">' +
        cellData.firstNumber + cellData.areaCode + cellData.secondNumber + '</span>';
    $(td).html(html);
};

var showType = function (td, cellData, rowData, row, col) {
    var types = {
        '1': 'پراید',
        '2': '405',
        '3': '206',
        '4': '207',
        '5': 'سمند',
        '6': 'پرشیا',
        '7': 'زانتیا',
        '8': 'مگان',
        '9': 'جک'
    };
    if (types.hasOwnProperty(cellData)) {
        $(td).text(types[cellData]);
    }
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
                {"defaultContent": "", name: "c.id", data: "id", orderable: true, searchable: false, className: "carId"},
                {name: "c.plaque", data: "plaque", orderable: false, searchable: false, className: "plaque", "createdCell": addPlaque},
                {name: "c.type", data: "type", orderable: true, searchable: true, "createdCell": showType},
                {name: "c.thirdPartyInsurance", data: "thirdPartyInsurance", orderable: true, searchable: false, className: "timestamp"},
                {name: "c.pullInsurance", data: "pullInsurance", orderable: true, searchable: false, className: "timestamp"},
                {name: "c.technicalDiagnosis", data: "technicalDiagnosis", orderable: true, searchable: false, className: "timestamp"},
                {name: "c.current", data: "current", orderable: true, searchable: false, className: "isCurrent"},
                {name: "c.status", data: "status", orderable: true, searchable: false},
                {name: "edit", "defaultContent": "<i class='btn btn-warning'>ویرایش</i>", orderable: false, searchable: false, className: "edit text-center"},
            ],

            order: [ 0, 'desc' ],

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
        $('.edit').on('click', function() {
            document.location.href = Routing.generate('fun_pro_admin_edit_driver_car', {id: $(this).parent().find('.carId').text()})
        });

        $('.isCurrent').each(function(index, item) {
            if ($(item).text() == 'true') {
                $(item).html('<button class="btn btn-sm btn-success disabled glyphicon glyphicon-ok changeStatus"></button>')
            } else if ($(item).text() == 'false') {
                $(item).html('<button class="btn btn-sm btn-warning disabled glyphicon glyphicon-remove changeStatus"></button>')
            }
        });

        $('.timestamp').each(function(index, item) {
            if (isNaN(parseInt($(item).text()))) {
                return;
            }
            var timestamp = $(item).text();
            var date = persianDate.unix(timestamp);
            $(item).text(date.pDate.year + '/' + date.pDate.month + '/' + date.pDate.date);
        });
    } );
});
