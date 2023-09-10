<?php

// Define WordPress environment
define( 'WP_USE_THEMES', false );
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

use common\Queries;

function get_candidates() {
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'get_candidates' ) {
		
		$queries     = new Queries();
		$information = $queries->getFormatInformation( );

		$response = [
			"draw"         => intval( $_POST['draw'] ),
			"recordsTotal" => $queries->numbersOfItems,
			"data"         => $information
		];
		
		echo json_encode( $response );
		
		wp_die();
	}
}