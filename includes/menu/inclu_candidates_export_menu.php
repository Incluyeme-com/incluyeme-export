<?php

require_once plugin_dir_path(__FILE__) . 'pages/inclu_export_admin_page.php';
add_action('admin_menu', 'inclu_export_menus');
add_action('admin_enqueue_scripts', 'inclu_export_styles');
function inclu_export_menus()
{
	global $wpdb;
	$current_user = wp_get_current_user();
	$table_name = $wpdb->prefix . 'authorized_emails_export';

	$authorized_emails = $wpdb->get_col("SELECT email FROM $table_name");

	if (!in_array($current_user->user_email, $authorized_emails)) {
		return;
	}

	add_menu_page(
		'Incluyeme - Export',
		'Incluyeme - Export',
		'manage_options',
		'incluyemeexport',
		'inclu_export_admin_page'
	);
}
