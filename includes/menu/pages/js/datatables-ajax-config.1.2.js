const usersToUpdate = [];
var table;

jQuery(document).ready(function () {
  const columns = [
    "Creacion",
    "Nombre",
    "Apellido",
    "Email",
    "Telefono",
    "Provincia",
    "Zona",
    "Genero",
    "Pais de nacimiento",
    "Discapacidad",
    "Nivel Maximo de Estudio",
    "¿Tiene trabajo?",
    "¿Busqueda laboral?",
    "Nivel de Ingles",
    "Area de Interes",
    "Etiquetas",
    "Ajustes Razonables",
    "Estudios",
    "Experiencia",
    "CV adjunto",
  ];
  window.table = jQuery("#candidates").DataTable({
    initComplete: function () {
      let api = this.api();

      jQuery.ajax({
        url: datatable_ajax_url.ajax_url,
        type: "POST",
        data: {
          action: "get_filter_options",
        },
        success: function (responseData) {
          let filters = JSON.parse(responseData);
          let dynamicFilters = filters.data;

          let filterColumns = {
            5: "provincia",
            6: "zona",
            7: "genero",
            9: "discapacidad",
            10: "Nivel Maximo de Estudio",
            11: "¿Tiene trabajo?",
            12: "¿Busqueda laboral?",
            13: "Nivel de Ingles",
            14: "Area de Interes",
            15: "Etiquetas",
          };

          Object.keys(filterColumns).forEach(function (columnIndex) {
            let column = api.column(columnIndex);
            let filterName = filterColumns[columnIndex];
            let select = jQuery(
              '<select><option value="">' +
                filterName.charAt(0).toUpperCase() +
                filterName.slice(1) +
                "</option></select>"
            )
              .appendTo(jQuery(column.header()))
              .on("change", function () {
                let val = jQuery.fn.dataTable.util.escapeRegex(
                  jQuery(this).val()
                );

                column.search(val ? val : "", false, false).draw();
              });

            select.addClass("form-select");

            let filterValues = [];
            if (columnIndex == 15) {
              filterValues = [
                "#TopList",
                "#SoftSkills",
                "#Migrante",
                "#Tech",
                "#Pasantia",
                "#Academias",
                "#DTF",
                "#Laboratorios",
                "#Pasantia",
              ];
            } else {
              filterValues = Array.isArray(dynamicFilters[filterName])
                ? dynamicFilters[filterName]
                : Object.values(dynamicFilters[filterName]);
            }

            filterValues.forEach(function (value) {
              select.append(
                '<option value="' + value + '">' + value + "</option>"
              );
            });

            select.trigger("change");
          });
        },
        error: function (xhr, status, error) {
          console.error("Error al obtener los filtros dinámicos:", error);
        },
      });
    },
    scrollX: true,
    fixedColumns: {
      leftColumns: 3,
    },
    dom:
      "<'row'<'d-md-flex justify-content-between align-items-center dt-layout-start col-md-12 py-2 me-auto'f>>" +
      "<'row'<'d-md-flex justify-content-between align-items-center dt-layout-start col-md-12 py-2 me-auto'B>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'d-md-flex justify-content-between align-items-center dt-layout-start col-md-auto me-auto pt-3'i><'d-md-flex justify-content-between align-items-center dt-layout-start col-md-auto me-auto pt-3'p>>",
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [
        "10 elementos",
        "25 elementos",
        "50 elementos",
        "100 elementos",
        "Mostrar todo",
      ],
    ],
    processing: true,
    serverSide: true,
    deferRender: true,
    language: {
      url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json",
      buttons: {
        selectAll: "Seleccionar",
        selectNone: "Deseleccionar",
        pageLength: {
          _: "Mostrar %d elementos",
          "-1": "Mostrar todo",
        },
      },
      select: {
        rows: {
          _: "Seleccionadas %d filas",
          1: "Seleccionada 1 fila",
        },
      },
      search: "_INPUT_",
      searchPlaceholder:
        "Ingresa una palabra clave para iniciar la búsqueda...",
    },
    search: {
      boundary: true,
    },
    searchPanes: {
      viewTotal: true,
    },
    searchDelay: 400,
    buttons: [
      "pageLength",
      {
        extend: "collection",
        text: "Todo",
        buttons: ["selectAll", "selectNone"],
      },
      {
        extend: "collection",
        text: "Exportar",
        buttons: [
          {
            extend: "csv",
            exportOptions: {
              modifier: {
                page: "all",
              },
              columns: [":visible:not(:eq(20))"],
              format: {
                header: function (data, columnIdx) {
                  return columns[columnIdx];
                },
                body: function (data, row, column, node) {
                  if (
                    node &&
                    node.firstChild &&
                    node.firstChild.nodeType === 1 &&
                    node.firstChild.getAttribute
                  ) {
                    return node.firstChild.getAttribute("title") || data;
                  }
                  return data;
                },
              },
            },
          },
          {
            extend: "excelHtml5",
            exportOptions: {
              columns: [":visible:not(:eq(20))"],
              format: {
                header: function (data, columnIdx) {
                  return columns[columnIdx];
                },
                body: function (data, row, column, node) {
                  if (
                    node &&
                    node.firstChild &&
                    node.firstChild.nodeType === 1 &&
                    node.firstChild.getAttribute
                  ) {
                    return node.firstChild.getAttribute("title") || data;
                  }
                  return data;
                },
              },
            },
          },
          {
            extend: "pdfHtml5",
            orientation: "landscape",
            pageSize: "A4",
            exportOptions: {
              columns: [":visible:not(:eq(20))"],
              format: {
                header: function (data, columnIdx) {
                  return columns[columnIdx];
                },
                body: function (data, row, column, node) {
                  if (
                    node &&
                    node.firstChild &&
                    node.firstChild.nodeType === 1 &&
                    node.firstChild.getAttribute
                  ) {
                    return node.firstChild.getAttribute("title") || data;
                  }
                  return data;
                },
              },
            },
            customize: function (doc) {
              doc.defaultStyle.fontSize = 8;
              doc.styles.tableHeader.fontSize = 10;
            },
          },
          {
            text: "JSON",
            action: function (e, dt, button, config) {
              exportAll = true;
              $.ajax({
                url: datatable_ajax_url.ajax_url,
                type: "POST",
                data: {
                  action: "get_candidates",
                  exportAll: true,
                },
                success: function (response) {
                  exportAll = false;
                  const result = JSON.parse(response).data;
                  const transformed = result.map((user) => ({
                    Creacion: user.created_at,
                    Nombre: user.first_name,
                    Apellido: user.last_name,
                    Email: user.user_email,
                    Telefono: user.phone,
                    Provincia: user.province,
                    Zona: user.zone,
                    Genero: user.gender,
                    "Pais de nacimiento": user.birth_country,
                    Discapacidad: user.disability,
                    "Nivel Maximo de Estudio": user.max_education_level,
                    "¿Tiene trabajo?": user.has_job,
                    "¿Busqueda laboral?": user.looking_for_job,
                    "Nivel de Ingles": user.name_level,
                    "Area de Interes": user.area_of_interest,
                    Etiquetas: user.tags,
                    "Ajustes Razonables": user.reasonable_adjustments,
                    Estudios: (user.education || [])
                      .map(
                        (edu) =>
                          `Título: ${edu.detail_title}, Institución Educativa: ${edu.grantor}, Nivel: ${edu.detail_description}, Desde: ${edu.started_at}, Hasta: ${edu.completed_at}`
                      )
                      .join(" | "),
                    Experiencia: (user.experience || [])
                      .map(
                        (exp) =>
                          `Título: ${exp.detail_title}, Empresa: ${exp.grantor}, Desde: ${exp.started_at}, Hasta: ${exp.completed_at}`
                      )
                      .join(" | "),
                    "CV adjunto": user.cv || false,
                  }));
                  console.log(transformed.length);
                  jQuery.fn.dataTable.fileSave(
                    new Blob([JSON.stringify(transformed)]),
                    "Export.json"
                  );
                },
                error: function () {
                  exportAll = false;
                  alert("No se pudo exportar la información.");
                },
              });
            },
          },
        ],
      },
      {
        extend: "collection",
        text: "Tags",
        buttons: [
          {
            text: "Eliminar Tags",
            action: function (e, dt, button, config) {
              this.processing(true);
              let selectedRows = dt.rows({ selected: true }).data();
              const users = [];
              selectedRows.map(function (row) {
                users.push(Number(row.user_id));
              });
              if (users.length === 0) {
                $(".dt-button-background").trigger("click");
                toastr.error("Debes seleccionar al menos un usuario");
                return;
              }
              const that = this;
              jQuery
                .ajax({
                  type: "POST",
                  url: datatable_ajax_url.ajax_url,
                  data: {
                    action: "delete_candidates_tags",
                    users: users,
                  },
                })
                .done(function (result) {
                  toastr.success("La etiqueta se ha eliminado con exito");
                  that.clear().draw();
                  that.ajax.reload();
                  that.processing(false);
                });
            },
          },
          {
            text: "Añadir Tags",
            action: function (e, dt, button, config) {
              $(".dt-button-background").trigger("click");
              jQuery("#tagsModal").modal("show");
              let selectedRows = dt.rows({ selected: true }).data();
              selectedRows.map(function (row) {
                usersToUpdate.push(Number(row.user_id));
              });
            },
          },
        ],
      },
      {
        text: "Aumentar o reducir columnas",
        action: function (e, dt, button, config) {
          jQuery("#columnModal").modal("show");
        },
      },
    ],
    select: {
      style: "multi",
    },
    ajax: {
      url: datatable_ajax_url.ajax_url,
      type: "POST",
      data: function (d) {
        d.action = "get_candidates";
        d.limit = d.length;
        d.offset = d.start;
      },
      error: function (xhr, error, thrown) {
        console.log("AJAX request failed: " + error);
      },
    },
    columnDefs: [
      { targets: 0, width: "auto" },
      { targets: "_all", width: "170px" },
      {
        targets: [
          3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 18, 19, 20, 21,
        ],
        orderable: false,
      },
      {
        targets: 5,
        render: function (data, type, row) {
          if (type === "display" && data && data.length > 20) {
            return (
              '<span title="' +
              data +
              '">' +
              data.substring(0, 20) +
              "...</span>"
            );
          }
          return data;
        },
      },
      {
        targets: 16,
        render: function (data, type, row) {
          if (type === "display" && data && data.length > 30) {
            return (
              '<span title="' +
              data +
              '">' +
              data.substring(0, 30) +
              "...</span>"
            );
          }
          return data;
        },
      },
      {
        targets: 17,
        render: function (data, type, row) {
          if (type === "display" && data && data.length > 20) {
            return (
              '<span title="' +
              data +
              '">' +
              data.substring(0, 20) +
              "...</span>"
            );
          }
          return data;
        },
      },
      {
        targets: 18,
        render: function (data, type, row) {
          if (type === "display" && data && data.length > 20) {
            return (
              '<span title="' +
              data +
              '">' +
              data.substring(0, 20) +
              "...</span>"
            );
          }
          return data;
        },
      },
      {
        targets: 19,
        render: function (data, type, row) {
          if (type === "display" && data && data.length > 30) {
            return (
              '<a href="#" class="no-click" data-toggle="modal" data-target="#myModalCV" data-content="' +
              data +
              '" title="' +
              data +
              '">' +
              data.substring(0, 30) +
              "...</a>"
            );
          }
          return data;
        },
        createdCell: function (td, cellData, rowData, row, col) {
          $(td).addClass("no-click");
        },
      },
      {
        targets: 20,
        searchable: false,
        visible: false,
      },
      {
        targets: 21,
        render: function (data, type, row) {
          return (
            '<a href="https://wa.me/+' +
            row.phone.replaceAll(" ", "") +
            '" target="_blank" class="btn btn-success btn-sm">Contactar</a>'
          );
        },
      },
    ],
    rowCallback: function (row, data, index) {
      $(row).addClass("clickable-row");
    },
    columns: [
      { data: "created_at" },
      { data: "first_name" },
      { data: "last_name" },
      { data: "user_email" },
      { data: "phone" },
      { data: "province" },
      { data: "zone" },
      { data: "gender" },
      { data: "birth_country" },
      { data: "disability" },
      { data: "max_education_level" },
      { data: "has_job" },
      { data: "looking_for_job" },
      { data: "name_level" },
      { data: "area_of_interest" },
      { data: "tags" },
      { data: "reasonable_adjustments" },
      {
        data: "education",
        render: function (data, type, row) {
          if (Array.isArray(data) && data.length > 0) {
            let fullText = data
              .map(
                (edu) =>
                  `Título: ${
                    edu.detail_title ?? "No indica"
                  }, Institución Educativa: ${
                    edu.grantor ?? "No indica"
                  }, Desde: ${edu.started_at ?? "No indica"}, Hasta: ${
                    edu.completed_at ?? "No indica"
                  }`
              )
              .join(" | ");

            let shortText =
              fullText.length > 30
                ? fullText.substring(0, 30) + "..."
                : fullText;

            return `<span title="${fullText}">${shortText}</span>`;
          }
          return "No indica";
        },
      },
      {
        data: "experience",
        render: function (data, type, row) {
          if (Array.isArray(data) && data.length > 0) {
            let fullText = data
              .map(
                (exp) =>
                  `Título: ${exp.detail_title ?? "No indica"}, Empresa: ${
                    exp.grantor ?? "No indica"
                  }, Desde: ${exp.started_at ?? "No indica"}, Hasta: ${
                    exp.completed_at ?? "No indica"
                  }`
              )
              .join(" | ");

            let shortText =
              fullText.length > 30
                ? fullText.substring(0, 30) + "..."
                : fullText;

            return `<span title="${fullText}">${shortText}</span>`;
          }
          return "No indica";
        },
      },
      { data: "cv" },
      { data: "user_id", visible: false },
      { data: null, defaultContent: "" },
    ],
  });
  window.candidatesTable = table;
});

function closeModals(idModal) {
  if (typeof jQuery === "undefined") {
    console.error("jQuery no está cargado.");
    return;
  }

  if (!idModal || typeof idModal !== "string") {
    console.error("ID del modal no válido.");
    return;
  }

  var $modal = jQuery(`#${idModal}`);
  if ($modal.length) {
    $modal.modal("hide");
  } else {
    console.error(`No se encontró ningún modal con el ID: ${idModal}`);
  }
}

function checkTags(idModal) {
  const checkboxes = document.querySelectorAll(
    '.modal-body input[type="checkbox"]'
  );

  const selectedData = [];
  closeModals(idModal);
  checkboxes.forEach((checkbox) => {
    if (checkbox.checked) {
      const id = checkbox.id;
      const labelElement = checkbox.nextElementSibling;
      if (labelElement) {
        const label = labelElement.textContent.trim();
        const selected = true;
        const item = { id, label, selected };
        selectedData.push(item);
      }
    }
  });
  const table = jQuery("#candidates").DataTable();
  if (selectedData.length !== 0 && usersToUpdate.length !== 0) {
    table.button(9).processing(true);
    jQuery
      .ajax({
        type: "POST",
        url: datatable_ajax_url.ajax_url,
        data: {
          action: "add_candidates_tags",
          users: usersToUpdate,
          tags: JSON.stringify(selectedData),
        },
      })
      .done(function (result) {
        toastr.success("La etiqueta se ha añadido con exito");
        table.clear().draw();
        table.ajax.reload();
        table.button(9).processing(false);
      });
  } else {
    toastr.error("Debes seleccionar al menos un usuario y una etiqueta");
  }
}
