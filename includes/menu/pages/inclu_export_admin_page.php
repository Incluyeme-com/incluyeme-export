<?php
include_once dirname(__DIR__, 3) . '/common/queries.php';

use common\Queries;


function inclu_export_admin_page()
{
?>
	<style>
		.dt-scroll-body::-webkit-scrollbar {
			width: 8px;
			height: 8px;

		}

		.dt-scroll-body::-webkit-scrollbar-track {
			background: #f1f1f1;
		}

		.dt-scroll-body::-webkit-scrollbar-thumb {
			background: #888;
			border-radius: 8px;
		}

		.checkbox-container label {
			display: block;
			margin: 5px;
		}

		.checkbox-container {
			max-height: 300px;
			overflow-y: auto;
		}

		.dt-buttons .btn {
			background-color: transparent !important;
			border-color: #8C8C8C !important;
			color: #000 !important;
			border-radius: 10px !important;
			margin-right: 10px !important;
			height: 40px !important;
		}

		.dt-buttons .btn:hover {
			background-color: #278eff !important;
			color: #fff !important;
			border-color: #278eff !important;
		}

		.btn-group {
			margin-bottom: 10px;
		}

		.dt-search input {
			background-image: url(https://img.icons8.com/?size=24&id=132&format=png&color=3C3C3C);
			background-repeat: no-repeat;
			background-color: #fff;
			background-position: 10px 50% !important;
			border-color: #8C8C8C !important;
			color: #8F8F8F !important;
			border-radius: 10px !important;
			height: 40px !important;
			width: 400px !important;
			margin: 0px !important;
			padding-left: 42px !important;
		}

		.page-link {
			border-color: #278eff !important;
			color: #278eff !important;
			height: 40px !important;
		}

		.active>.page-link {
			background-color: #278eff !important;
			color: #fff !important;
		}

		.disabled>.page-link {
			background-color: #278eff !important;
			color: #fff !important;
			border-color: #fff !important;
		}

		.confirmTableHeader {
			--bs-table-bg: #CBCBCB !important;
		}

		.btn-export {
			height: 40px;
			padding: 14px;
			background-color: #278eff;
			text-decoration: none;
			display: flex;
			align-items: center;
			border: none;
			font-size: 1rem;
			color: white;
		}

		.btn-export:hover {
			background-color: rgb(99, 174, 255);
		}

		select.form-select {
			border-radius: 10px;
		}

		.toast-top-right {
			top: 54px !important;
		}

		.button-container {
			display: flex;
			justify-content: flex-end;
			margin-top: 20px;
		}
	</style>


	<div class="container p-3 m-3 overflow-auto">
		<div class="col-md-6">
			<div class="mb-2">
				<button class="rounded-pill btn-export" onclick="formToggle('importFrm');" role="button">Importar usuarios desde Excel
				</button>
			</div>
		</div>
		<div class="col-md-12" id="importFrm" style="display: none;">
			<div class="row mb-3">
				<div class="col-auto">
					<label for="fileInput" class="visually-hidden">File</label>
					<input type="file" class="form-control p-1" name="file-excel" id="fileInput" />
				</div>
			</div>
		</div>
		<table id="candidates" class="table nowrap rounded-3" style="width:100%;">
			<caption class="hide" style="display: none">Candidates information</caption>
			<thead>
				<tr id="tableHeader" class="confirmTableHeader"></tr>
			</thead>
			<tbody>
				<tr>
				</tr>
			</tbody>
		</table>
		<div class="modal fade" id="tagsModal" tabindex="-1" role="dialog" aria-labelledby="tagsModal"
			aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="tagsModalLabel">Añadir Etiquetas</h5>
					</div>
					<div class="modal-body">
						<div class="container">
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="DTF">
								<label class="form-check-label" for="DTF">
									#DTF
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="TopList">
								<label class="form-check-label" for="TopList">
									#TopList
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="Academias">
								<label class="form-check-label" for="Academias">
									#Academias
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="SoftSkills">
								<label class="form-check-label" for="SoftSkills">
									#SoftSkills
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="Laboratorios">
								<label class="form-check-label" for="Laboratorios">
									#Laboratorios
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="Migrante">
								<label class="form-check-label" for="Migrante">
									#Migrante
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="Pasantia">
								<label class="form-check-label" for="Pasantia">
									#Pasantia
								</label>
							</div>
							<div class="form-check">
								<input class="form-check-input float-none" type="checkbox" id="Tech">
								<label class="form-check-label" for="Tech">
									#Tech
								</label>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModals('tagsModal')">
							Cerrar
						</button>
						<button type="button" class="btn btn-primary" onclick="checkTags('tagsModal')">Guardar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal CV -->
		<div class="modal fade" id="myModalCV" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">Detalle del CV</h5>
					</div>
					<div class="modal-body">
						<div id="loading-indicator" style="display: none; text-align: center;">
							<p>Cargando...</p>
						</div>
						<iframe id="document-viewer" width="100%" height="400px" style="display:none;"></iframe>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModals('myModalCV')">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Modal Columns -->
		<div class="modal fade" id="columnModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">Seleccionar Columnas</h5>
					</div>
					<div class="modal-body">
						<div class="checkbox-container">
							<div id="checkboxContainer"></div>
						</div>
						<div class="button-container">
							<button id="resetButton" type="button" class="btn btn-danger btn-sm">Resetear</button>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="closeModals('columnModal')">Cerrar</button>
					</div>
				</div>
			</div>
		</div>

	</div>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const columnsNames = [
				"Creación", "Nombre", "Apellido", "Email", "Teléfono", "",
				"", "", "País de nacimiento", "", "", "", "", "", "", "", "Ajustes Razonables",
				"Estudios", "Experiencia", "CV Adjunto", "User Id", "Contacto",
			];

			const tableHeader = document.getElementById('tableHeader');
			const resetButton = document.getElementById('resetButton');

			columnsNames.forEach((name, index) => {
				const thHeader = document.createElement('th');
				tableHeader.classList.add('align-middle');
				thHeader.textContent = name;
				thHeader.setAttribute('data-dt-column', index);
				tableHeader.appendChild(thHeader);
			});

			columnsNames.forEach((name, index) => {
				const label = document.createElement('label');
				if (name === '') {
					label.classList.add('d-none');
				}
				label.innerHTML = `<input type="checkbox" class="toggle-column" data-column="${index}" checked> ${name}`;
				checkboxContainer.appendChild(label);
			});

			const checkboxes = document.querySelectorAll('.toggle-column');
			checkboxes.forEach(checkbox => {
				checkbox.addEventListener('change', function() {
					const columnIdx = this.getAttribute('data-column');
					const column = window.candidatesTable.column(columnIdx);

					column.visible(!column.visible());
				});
			});

			resetButton.addEventListener('click', function() {
				checkboxes.forEach(checkbox => {
					checkbox.checked = true;
					const columnIdx = checkbox.getAttribute('data-column');
					const column = window.candidatesTable.column(columnIdx);
					column.visible(true);
				});
			});
		});
	</script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			var table = document.getElementById('candidates').getElementsByTagName('tbody')[0];

			table.addEventListener('click', function(event) {
				var target = event.target;
				if (target.classList.contains('no-click')) {
					event.stopPropagation();
					var content = target.getAttribute('data-content');
					var modal = document.getElementById('myModalCV');
					var viewer = document.getElementById('document-viewer');
					var loadingIndicator = document.getElementById('loading-indicator');

					loadingIndicator.style.display = 'block';
					viewer.style.display = 'none';
					viewer.src = '';

					setTimeout(function() {
						try {
							viewer.src = 'https://docs.google.com/viewer?url=' + encodeURIComponent(content) + '&embedded=true';
							viewer.onload = function() {
								loadingIndicator.style.display = 'none';
								viewer.style.display = 'block';
							};
						} catch (error) {
							console.error("Error loading document:", error);
							loadingIndicator.style.display = 'none';
							alert('Error al cargar el documento. Por favor, inténtelo de nuevo más tarde.');
						}
					}, 100);

					$(`#${modal.id}`).modal('show');

					return false;
				}
			});
		});
	</script>
	<script>
		function formToggle(ID) {
			var element = document.getElementById(ID);
			if (element.style.display === "none") {
				element.style.display = "block";
			} else {
				element.style.display = "none";
			}
		}

		document.getElementById('fileInput').addEventListener('change', function() {
			var form = document.getElementById('fileInput').value;
			var idxDot = form.lastIndexOf(".") + 1;
			var extFile = form.substr(idxDot, form.length).toLowerCase();
			if (extFile !== "csv") {
				toastr.error('Sólo está permitido cargar archivos en formato <b>.CSV</b>', 'Oops!')
				document.getElementById('fileInput').value = "";
				return false;
			}
		})

		function readFile(evt) {
			let file = evt.target.files[0];
			let reader = new FileReader();
			reader.onload = (e) => {
				let parse = parseCsv(e.target.result)
				let formData = new FormData();
				let excelFile = parse;

				if (!excelFile || excelFile.length === 0) {
					toastr.error('El archivo CSV está vacío o no se pudo procesar.', 'Hemos tenido problemas...');
					document.getElementById('fileInput').value = "";
					return;
				}

				formData.append('data', JSON.stringify(excelFile));
				formData.append('action', 'add_new_candidates_users');
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: formData,
					contentType: false,
					processData: false,
					beforeSend: function() {
						toastr.info('Procesando...')
					},
					success: function(data) {
						if (data == 0) {
							toastr.error('Hubo un error al procesar su archivo. Por favor, asegúrese de que su archivo tenga el formato correcto.', 'Hemos tenido problemas...')
						} else {
							toastr.success('Los datos se han cargado correctamente.', 'Excel cargado con exito!')
							if (window.candidatesTable) {
								window.candidatesTable.ajax.reload(null, false);
							}
						}
						document.getElementById('fileInput').value = "";
					},
					error: function(xhr, status, error) {
						console.error('Error AJAX:', status, error, xhr);
						document.getElementById('fileInput').value = "";
					}
				});
			};
			reader.readAsText(file);
		}

		document.querySelector('#fileInput').addEventListener('change', readFile, false);

		function parseCsv(data) {
			const COLUMNS_NAMES = {
				'Nombre': 'candidate_name',
				'Apellido': 'candidate_lastname',
				'Correo': 'candidate_email_address',
				'Inglés': 'nivel_de_ingles',
				'Nacimiento': 'pais_de_nacimiento',
				'Vivienda': 'Vivienda',
				'Empleo': 'Empleo',
				'Búsqueda laboral': 'busqueda_laboral',
				'Estudios': 'maximo_nivel_de_estudios_alcanzado',
				'Fecha Nacimiento': 'Birthdate',
				'Género': 'Gender',
				'Tipo Ajuste': 'detalle_discapacidad',
				'Discapacidad': 'Discapacidad',
				'Área Interés': 'area_de_interes',
			};
			var regex = /,(?=(?:[^"]*"[^"]*")*[^"]*$)/g;
			const lines = data.trim().split('\r\n');
			const columnNames = lines[0].split(',');
			const objectsArray = [];
			for (let i = 1; i < lines.length; i++) {
				const line = lines[i].split(regex);
				const obj = {};
				for (let j = 0; j < columnNames.length; j++) {
					const columnName = columnNames[j];
					if (COLUMNS_NAMES.hasOwnProperty(columnName)) {
						obj[COLUMNS_NAMES[columnName]] = line[j];
					}
				}
				objectsArray.push(obj);
			}
			return objectsArray;
		}
	</script>
<?php
}

function inclu_export_styles($hook)
{
	$currentScreen = get_current_screen();
	$version = '1.0.0';
	if (strpos($currentScreen->base, 'incluyemeexport')) {
		wp_register_script('getbootstrap-export-admin', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js', [], $version, false);
		wp_enqueue_script('getbootstrap-export-admin');
		wp_register_style('getbootstrap-export-style', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css', [], $version, false);
		wp_enqueue_style('getbootstrap-export-style');

		wp_register_style('gettables-export-style', 'https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.2.1/b-3.2.0/b-html5-3.2.0/cr-2.0.4/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.3/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.1/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css', [], $version, false);
		wp_enqueue_style('gettables-export-style');
		wp_register_script('gettables-export-admin', 'https://cdn.datatables.net/v/bs5/jq-3.7.0/jszip-3.10.1/dt-2.2.1/b-3.2.0/b-html5-3.2.0/cr-2.0.4/fc-5.0.4/fh-4.0.1/kt-2.12.1/r-3.0.3/rg-1.5.1/rr-1.5.0/sc-2.4.3/sb-1.8.1/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.js', [], $version, false);
		wp_enqueue_script('gettables-export-admin');

		wp_register_script('getpdf-export-admin', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', [], $version, false);
		wp_enqueue_script('getpdf-export-admin');
		wp_register_script('getvfs-export-admin', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', [], $version, false);
		wp_enqueue_script('getvfs-export-admin');

		wp_enqueue_script('datatables-config', plugin_dir_url(__FILE__) . '/js/datatables-ajax-config.1.2.js', ['jquery'], date("h:i:s"), true);
		wp_localize_script('datatables-config', 'datatable_ajax_url', ['ajax_url' => admin_url('admin-ajax.php')]);
	}
}
