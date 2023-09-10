jQuery(document).ready(function () {
    let table = jQuery('#candidates').DataTable({
        initComplete: function () {
            let columnsToSelect = [6, 9, 10, 11, 12, 13, 14];
            columnsToSelect.forEach(function (columnIndex) {
                let column = table.column(columnIndex);
                let select = jQuery('<br><select><option value=""></option></select>')
                    .appendTo(jQuery(column.header()))
                    .on('change', function () {
                        let val = jQuery.fn.dataTable.util.escapeRegex(
                            jQuery(this).val()
                        );
                        column
                            .search(val ? val : '', true, false)
                            .draw();
                    });
                select.addClass('form-select');
                column.data().unique().sort().each(function (d, j) {
                    select.append('<option value="' + d + '">' + d + '</option>');
                });
                select.trigger('change');
            });

            let select2 = jQuery('<br><select><option value=""></option></select>')
                .appendTo(jQuery(table.column(8).header()))
                .on('change', function () {
                    let val = jQuery.fn.dataTable.util.escapeRegex(
                        jQuery(this).val()
                    );
                    table.column(8)
                        .search(val ? val : '', true, false)
                        .draw();
                });
            select2.addClass('form-select');
            let tags = ["Motriz", "Auditiva", "Psíquica", "Visceral", "Visual", "Habla", "Intelectual"];
            tags.forEach(function (tag) {
                select2.append('<option value="' + tag + '">' + tag + '</option>');
            });
            select2.trigger('change');
            this.api().columns([15]).every(function () {
                let column = this;
                let values = ["#TopList", "#SoftSkills", "#Migrante", "#Tech", "#Pasantia", "#Academias", "#DTF", "#Laboratorios", "#Pasantia"];

                let select = jQuery('<br><select><option value=""></option></select>')
                    .appendTo(jQuery(column.header()))
                    .on('change', function () {
                        let val = jQuery.fn.dataTable.util.escapeRegex(jQuery(this).val());
                        column.search(val ? val : '', true, false).draw();
                    });

                select.addClass('form-select');

                values.forEach(function (value) {
                    select.append('<option value="' + value + '">' + value + '</option>');
                });

                select.trigger('change');
            });

        },
        dom: 'Bfrtip',
        lengthMenu: [
            [10, 25, 50, 100, -1],
            ['10 elementos', '25 elementos', '50 elementos', '100 elementos', 'Mostrar todo']
        ],
        processing: true,
        serverSide: false,
        searching: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json',
            buttons: {
                selectAll: "TODO",
                selectNone: "Deseleccionar",
                pageLength: {
                    _: "Mostrar %d elementos",
                    '-1': "Mostrar todo"
                }
            },
            select: {
                rows: {
                    _: "Seleccionadas %d filas",
                    1: "Seleccionada 1 fila"
                }
            }
        },
        searchPanes: {
            viewTotal: true,
        },
        buttons: [
            'pageLength',
            {
                extend: 'colvis',
                text: "Items"
            },
            'selectAll',
            'selectNone',
            {
                extend: 'csv',
                exportOptions: {
                    columns: [':visible']
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [':visible']
                }
            },
            {
                text: 'JSON',
                action: function (e, dt, button, config) {
                    let data = dt.buttons.exportData();
                    let transformedData = [];
                    data.body.forEach(function (row) {
                        let rowData = {};
                        for (let i = 0; i < data.header.length; i++) {
                            rowData[data.header[i]] = row[i];
                        }
                        transformedData.push(rowData);
                    });
                    jQuery.fn.dataTable.fileSave(
                        new Blob([JSON.stringify(transformedData)]),
                        'Export.json'
                    );
                }
            }
        ],
        select: {
            style: 'multi'
        },
        paging: true,
        ajax: {
            url: datatable_ajax_url.ajax_url,
            type: 'POST',
            data: {
                action: 'get_candidates'
            }
        },
        ordering: false,
        columnDefs: [
            {width: '250px', targets: 16}
        ],
        columns: [
            {data: 'first_name'},
            {data: 'last_name'},
            {data: 'user_email'},
            {data: 'phone'},
            {data: 'province'},
            {data: 'zone'},
            {data: 'gender'},
            {data: 'birth_country'},
            {data: 'disability'},
            {data: 'max_education_level'},
            {data: 'has_job'},
            {data: 'looking_for_job'},
            {data: 'ccd'},
            {data: 'name_level'},
            {data: 'area_of_interest'},
            {data: 'tags'},
            {
                data: 'reasonable_adjustments'
            },
        ],
    });
});
