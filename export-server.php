<?php

// Define WordPress environment
define( 'WP_USE_THEMES', false );
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

use common\Queries;

function get_candidates() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'get_candidates' ) {
		
		$queries     = new Queries();
		$information = $queries->getFormatInformation();
		
		$response = [
			"draw"         => 0,
			"recordsTotal" => $queries->numbersOfItems,
			"data"         => $information
		];
		
		echo json_encode( $response );
		
		wp_die();
	}
}

function update_candidates_tags() {
	error_log( print_r( $_POST, true ) );
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'delete_candidates_tags' ) {
		
		$queries = new Queries();
		$users   = $_POST['users'];
		$queries->deleteUserAllTags( $users );
		
		
		echo json_encode( [] );
		
		wp_die();
	}
}

function add_candidates_tags() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'add_candidates_tags' ) {
		
		$queries = new Queries();
		$users   = $_POST['users'];
		$tags    = $_POST['tags'];
		$queries->updateUserTags( $users, $tags );
		
		
		echo json_encode( [] );
		
		wp_die();
	}
}
function add_new_candidates() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'add_new_candidates' ) {
		
		$queries = new Queries();
		$users   = $_POST['users'];
		$tags    = $_POST['tags'];
		$queries->updateUserTags( $users, $tags );
		
		echo json_encode( [] );
		
		wp_die();
	}
}
