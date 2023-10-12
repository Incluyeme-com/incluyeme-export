<?php

// Define WordPress environment
define( 'WP_USE_THEMES', false );
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

use common\Queries;
use common\WP_Incluyeme_Login_Countries;

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

function add_new_candidates_users() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'add_new_candidates_users' ) {
		$checkData     = $_POST['data'];
		$data          = stripslashes( html_entity_decode( $checkData ) );
		$data          = json_decode( $data );
		$verifications = new WP_Incluyeme_Login_Countries();
		if ( is_array( $data ) || is_object( $data ) ) {
			foreach ( $data as $row ) {
				if ( $row->email ) {
					$password   = 123456;
					$first_name = $row->first_name;
					$last_name  = $row->last_name;
					$email      = $row->email;
					$haveDiscap = $row->haveDiscap;
					$verifications::registerUser(
						$email,
						$password,
						$first_name,
						$last_name,
						$haveDiscap
					);
					echo json_encode( [] );
					wp_die();
				}
			}
		}
	}
}


