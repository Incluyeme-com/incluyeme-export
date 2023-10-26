<?php

/*
Plugin Name: Incluyeme Export
Plugin URI: https://github.com/Cro22
Description: Panel de control de candidatos
Version: 1.1.4
Author: Jesus Nuñez
Author URI: https://github.com/Cro22
License: A "Slug" license name e.g. GPL2
*/

defined('ABSPATH') || exit;
require_once plugin_dir_path(__FILE__) . 'includes/menu/inclu_candidates_export_menu.php';
require_once plugin_dir_path(__FILE__) . 'export-server.php';
add_action('admin_init', 'incluPluginRequirementsExport' );
add_action('wp_enqueue_scripts', 'enqueue_plugin_scripts');
add_action( 'wp_ajax_get_candidates', 'get_candidates' );
add_action( 'wp_ajax_delete_candidates_tags', 'update_candidates_tags' );
add_action( 'wp_ajax_add_candidates_tags', 'add_candidates_tags' );
add_action( 'wp_ajax_new_candidates_tags', 'add_new_candidates' );
add_action( 'wp_ajax_add_new_candidates_users', 'add_new_candidates_users' );
function incluPluginRequirementsExport()
{
    if (is_admin() && current_user_can('activate_plugins') && !is_plugin_active('wpjobboard/index.php')) {
        add_action('admin_notices', 'inlcuRequirementsActivateExport');
        deactivate_plugins(plugin_basename(__FILE__));
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
    
}
function enqueue_plugin_scripts() {
    wp_enqueue_script('datatables-config', plugin_dir_url(__FILE__) . '/includes/menu/pages/js/datatables-ajax-config.js', array('jquery'), '1.0', true);

    
    wp_localize_script('datatables-config', 'datatable_ajax_url', array('ajax_url' => admin_url('admin-ajax.php')));
}


function inlcuRequirementsActivateExport()
{
    ?>
	<div class="error"><p> <?php echo __('Sorry, but Incluyeme plugin requires the WPJob Board plugin to be installed and
	                      active.', 'incluyeme'); ?> </p></div>
    <?php
}

require_once 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Incluyeme-com/incluyeme-export',
    __FILE__,
    'incluyeme-export'
);
