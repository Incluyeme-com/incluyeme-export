const usersToUpdate = []

jQuery(document).ready(function () {
    const columns = ['Nombre', 'Apellido', 'Email', 'Telefono', 'Provincia', 'Zona',
        'Genero',
        'Pais de nacimiento',
        'Discapacidad',
        'Nivel Maximo de Estudio',
        '¿Tiene trabajo?',
        '¿En busqueda laboral?',
        'CCD',
        'Nivel de Ingles',
        'Area de Interes',
        'Etiquetas',
        'Ajustes Razonables'];
    let table = jQuery('#candidates').DataTable({
        initComplete: function () {
            let columnsToSelect = [4, 5, 6, 7, 9, 10, 11, 12, 13, 14];
            columnsToSelect.forEach(function (columnIndex) {
                let column = table.column(columnIndex);
                let select = jQuery('<br><select><option value=""></option></select>')
                    .appendTo(jQuery(column.header()))
                    .on('change', function () {
                        let val = jQuery.fn.dataTable.util.escapeRegex(
                            jQuery(this).val()
                        );
                        column
                            .search(val || '', true, false)
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
                        .search(val || '', true, false)
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
                        column.search(val || '', true, false).draw();
                    });

                select.addClass('form-select');

                values.forEach(function (value) {
                    select.append('<option value="' + value + '">' + value + '</option>');
                });

                select.trigger('change');
            });

        },
        dom: "<'row'<'col-sm-8 col-lg-10'B><'col-2 col-md-2'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-8 col-lg-10'i><'col-2 col-md-2'p>>",
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
                    columns: [':visible'],
                    format: {
                        header: function (data, columnIdx) {
                            return columns[columnIdx];
                        }
                    }
                }
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        header: function (data, columnIdx) {
                            return columns[columnIdx];
                        }
                    }
                },
            },
            {
                extend: 'pdfHtml5',
                exportOptions: {
                    columns: [':visible'],
                    format: {
                        header: function (data, columnIdx) {
                            return columns[columnIdx];
                        }
                    }
                },
            },
            {
                text: 'JSON',
                action: function (e, dt, button, config) {
                    let data = dt.buttons.exportData();
                    let transformedData = [];
                    data.body.forEach(function (row) {
                        let rowData = {};
                        for (let i = 0; i < data.header.length; i++) {
                            console.log({ad: columns[i]})
                            rowData[columns[i]] = row[i];
                        }
                        transformedData.push(rowData);
                    });
                    jQuery.fn.dataTable.fileSave(
                        new Blob([JSON.stringify(transformedData)]),
                        'Export.json'
                    );
                }
            },
            {
                text: 'Eliminar Tags',
                action: function (e, dt, button, config) {
                    this.processing(true);
                    let selectedRows = dt.rows({selected: true}).data();
                    const users = []
                    selectedRows.map(function (row) {
                        users.push(Number(row.user_id));
                    });
                    const that = this;
                    jQuery.ajax({
                        type: 'POST',
                        url: datatable_ajax_url.ajax_url,
                        data: {
                            action: 'delete_candidates_tags',
                            users: users
                        },
                    }).done(function (result) {
                        that.clear()
                            .draw();
                        that.ajax.reload();
                        that.processing(false);
                    });

                }
            },
            {
                text: 'Añadir Tags',
                action: function (e, dt, button, config) {
                    jQuery('#tagsModal').modal('show');
                    let selectedRows = dt.rows({selected: true}).data();
                    selectedRows.map(function (row) {
                        usersToUpdate.push(Number(row.user_id));
                    });
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
            {width: '250px', targets: 16},
            {
                targets: 17,
                searchable: false,
                visible: false
            },
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
            {data: 'reasonable_adjustments'},
            {data: 'user_id'}
        ],
    });


})

function closeModals() {
    jQuery('#tagsModal').modal('hide');
}

function checkTags() {

    const checkboxes = document.querySelectorAll('.modal-body input[type="checkbox"]');


    const selectedData = [];
    closeModals();
    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            const id = checkbox.id;
            const label = checkbox.nextElementSibling.textContent.trim();
            const selected = true;

            const item = {id, label, selected};
            selectedData.push(item);
        }
    });
    const table = jQuery('#candidates').DataTable();
    if (selectedData.length !== 0 && usersToUpdate.length !== 0) {

        table.button(9).processing(true);
        jQuery.ajax({
            type: 'POST',
            url: datatable_ajax_url.ajax_url,
            data: {
                action: 'add_candidates_tags',
                users: usersToUpdate,
                tags: JSON.stringify(selectedData)
            },
        }).done(function (result) {
            table
                .clear()
                .draw();
            table.ajax.reload();
            table.button(9).processing(false);
        });
    } else {
        table.ajax.reload();
        table.button(9).processing(false);
    }
}
