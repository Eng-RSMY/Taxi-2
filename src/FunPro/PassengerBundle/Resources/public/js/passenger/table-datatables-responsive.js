var TableDatatablesResponsive = function () {

    var initTable = function () {
        var table = $('#sample_2');

        var oTable = table.dataTable({
            "oSearch": {"sSearch": search},
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
                {name: "p.id", data: "id", "defaultContent": "", orderable: true, searchable: false},
                {name: "p.mobile", data: "mobile", "defaultContent": "", orderable: false, searchable: true},
                {name: "p.name", data: "name", orderable: true, searchable: true},
                {name: "p.age", data: "age", "defaultContent": "", orderable: true, searchable: false},
                {name: "p.sex", data: "sex", "defaultContent": "", orderable: true, searchable: false},
                {name: "p.rate", data: "rate", "defaultContent": 0, orderable: true, searchable: false},
                {name: "p.description", data: "description", "defaultContent": "", orderable: false, searchable: false},
                {name: "p.avatar", data: "avatar", "defaultContent": "", orderable: false, searchable: false, className: "avatar"},
                //{name: "edit", "defaultContent": "<i class='btn btn-warning'>ویرایش</i>", orderable: false, searchable: false, className: "edit text-center"},
                //{name: "delete", "defaultContent": "<i class='btn btn-danger'>حذف</i>", orderable: false, searchable: false, className: "delete text-center"},
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
        $('td.avatar').each(function(index, item) {
            var avatar = $(item).text() ? $(item).text() : 'default_avatar.jpg';
            $(item).html('<img src="' + Routing.generate('liip_imagine_filter', {filter: 'panel_avatar_thumb', path: avatar}) + '" />');
        });
    } );
});