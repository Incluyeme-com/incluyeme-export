<?php

require_once plugin_dir_path( __FILE__ ) . 'pages/inclu_export_admin_page.php';
add_action( 'admin_menu', 'inclu_export_menus' );
add_action('admin_enqueue_scripts', 'inclu_export_styles');
function inclu_export_menus() {
	add_menu_page(
		'Incluyeme - Export',
		'Incluyeme - Export',
		'manage_options',
		'incluyemeexport',
		'inclu_export_admin_page'
	);
}
