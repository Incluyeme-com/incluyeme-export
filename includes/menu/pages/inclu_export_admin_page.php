<?php
include_once dirname( __DIR__, 3 ) . '/common/queries.php';

use common\Queries;


function inclu_export_admin_page() {
	?>
	<div class="container p-5">
		<table id="candidates" class="table display nowrap" style="width:100%; height: 100%">
			<caption class="hide">Candidates information</caption>
			<thead>
			<tr>
				<th>Nombre</th>
				<th>Apellido</th>
				<th>Email</th>
				<th>Telefono</th>
				<th>Provincia</th>
				<th>Zona</th>
				<th>Genero</th>
				<th>Pais de nacimiento</th>
				<th>Discapacidad</th>
				<th>Nivel Maximo de Estudio</th>
				<th>¿Tiene trabajo?</th>
				<th>¿En busqueda laboral?</th>
				<th>CCD</th>
				<th>Nivel de Ingles</th>
				<th>Area de Interes</th>
				<th>Etiquetas</th>
				<th>Ajustes Razonables</th>
			</tr>
			</thead>
			<tbody>
			<tr>
			</tr>
			</tbody>
		</table>
	</div>
	<?php
}

function inclu_export_styles( $hook ) {
	$currentScreen = get_current_screen();
	$version       = '1.0.0';
	if ( strpos( $currentScreen->base, 'incluyemeexport' ) ) {
		wp_register_script( 'getbootstrap-export-admin', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js', [], $version, false );
		wp_enqueue_script( 'getbootstrap-export-admin' );
		
		wp_register_style( 'gettables-export-style', 'https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-1.13.6/b-2.4.1/b-colvis-2.4.1/b-html5-2.4.1/b-print-2.4.1/cr-1.7.0/fc-4.3.0/fh-3.4.0/kt-2.10.0/r-2.5.0/rg-1.4.0/rr-1.4.1/sc-2.2.0/sb-1.5.0/sp-2.2.0/sl-1.7.0/sr-1.3.0/datatables.min.css', [], $version, false );
		wp_enqueue_style( 'gettables-export-style' );
		
		wp_register_style( 'getbootstrap-export-style', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css', [], $version, false );
		wp_enqueue_style( 'getbootstrap-export-style' );
		
		wp_register_script( 'getpdf-export-admin',
			'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', [], $version, false );
		wp_enqueue_script( 'getpdf-export-admin' );
		
		wp_register_script( 'getvfs-export-admin',
			'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', [], $version, false );
		wp_enqueue_script( 'getvfs-export-admin' );
		
		wp_register_script( 'gettables-export-admin',
			'https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-1.13.6/b-2.4.1/b-colvis-2.4.1/b-html5-2.4.1/b-print-2.4.1/cr-1.7.0/fc-4.3.0/fh-3.4.0/kt-2.10.0/r-2.5.0/rg-1.4.0/rr-1.4.1/sc-2.2.0/sb-1.5.0/sp-2.2.0/sl-1.7.0/sr-1.3.0/datatables.min.js', [], $version, false );
		wp_enqueue_script( 'gettables-export-admin' );
		
		wp_enqueue_script( 'datatables-config', plugin_dir_url( __FILE__ ) . '/js/datatables-ajax-config.js', [ 'jquery' ], '1.0', true );
		
		wp_localize_script( 'datatables-config', 'datatable_ajax_url', [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ] );
	}
}
