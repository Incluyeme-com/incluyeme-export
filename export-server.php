<?php

// Define WordPress environment
define('WP_USE_THEMES', false);
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

use common\Queries;
use common\WP_Incluyeme_Login_Countries;

function get_candidates()
{
	if (isset($_POST['action']) && $_POST['action'] === 'get_candidates') {
		$limit = isset($_POST['length']) ? intval($_POST['length']) : 10;
		$offset = isset($_POST['start']) ? intval($_POST['start']) : 0;
		$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
		$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
		$orderDirection = (isset($_POST['order'][0]['dir']) && $_POST['order'][0]['dir'] === 'desc') ? 'DESC' : 'ASC';

		$columns = [
			'created_at',
			'first_name',
			'last_name',
			'education'
		];

		$orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

		$queries = new Queries();
		$information = $queries->getUsersInformation($limit, $offset, $search, $orderColumn, $orderDirection);

		$response = [
			"draw" => intval($_POST['draw']),
			"recordsTotal" => $queries->getTotalRecords(),
			"recordsFiltered" => $queries->getFilteredRecordsCount($search),
			"data" => $information
		];

		echo json_encode($response);
		wp_die();
	}
}

function get_filter_options()
{
	if (isset($_POST['action']) && $_POST['action'] === 'get_filter_options') {
		$queries = new Queries();
		$options = $queries->getDistinctOptions();
		$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
		$columns = [
			'created_at',
			'first_name',
			'last_name',
			'education'
		];

		$orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

		$response = [
			"provincia" => array_unique(array_column($options, 'provincia')),
			"zona" => array_unique(array_column($options, 'zona')),
			"genero" => array_unique(array_column($options, 'genero')),
			"discapacidad" => array_unique(array_column($options, 'discapacidad')),
			"Nivel Maximo de Estudio" => array_unique(array_column($options, 'nivel_maximo_estudio')),
			"¿Tiene trabajo?" => array_unique(array_column($options, 'tiene_trabajo')),
			"¿Busqueda laboral?" => array_unique(array_column($options, 'busqueda_laboral')),
			"Nivel de Ingles" => array_unique(array_column($options, 'nivel_ingles')),
			"Area de Interes" => array_unique(array_column($options, 'area_interes')),
			"Etiquetas" => array_unique(array_column($options, 'tags'))
		];

		$responseData = [
			"recordsTotal" => $queries->getTotalRecords(),
			"recordsFiltered" => $queries->getFilteredRecordsCount('', $orderColumn),
			"data" => $response
		];

		echo json_encode($responseData);
		wp_die();
	}
}

function get_TotalRecords()
{
	$queries = new Queries();
	return $queries->getTotalRecords();
}

function get_TotalFilteredRecords($search = '', $filters = [])
{
	$queries = new Queries();
	return $queries->getFilteredRecordsCount($search, $filters);
}

function add_candidates_tags()
{
	if (isset($_POST['action']) && $_POST['action'] === 'add_candidates_tags') {

		$queries = new Queries();
		$users   = $_POST['users'];
		$tags    = $_POST['tags'];
		$queries->updateUserTags($users, $tags);


		echo json_encode([]);

		wp_die();
	}
}

function update_candidates_tags()
{
	error_log(print_r($_POST, true));
	if (isset($_POST['action']) && $_POST['action'] === 'delete_candidates_tags') {

		$queries = new Queries();
		$users   = $_POST['users'];
		$queries->deleteUserAllTags($users);


		echo json_encode([]);

		wp_die();
	}
}

function add_new_candidates()
{
	if (isset($_POST['action']) && $_POST['action'] === 'add_new_candidates') {

		$queries = new Queries();
		$users   = $_POST['users'];
		$tags    = $_POST['tags'];
		$queries->updateUserTags($users, $tags);

		echo json_encode([]);

		wp_die();
	}
}

function add_new_candidates_users()
{
	if (isset($_POST['action']) && $_POST['action'] === 'add_new_candidates_users') {
		$checkData     = $_POST['data'];
		$data          = stripslashes(html_entity_decode($checkData));
		$data          = json_decode($data);
		$verifications = new WP_Incluyeme_Login_Countries();
		if (is_array($data) || is_object($data)) {
			foreach ($data as $row) {
				if ($row->candidate_email_address) {
					$password      = 123456;
					$first_name    = $row->candidate_name;
					$last_name     = $row->candidate_lastname;
					$email         = $row->candidate_email_address;
					$haveDiscap    = "siDIS";
					$nivel_ingles  = $row->nivel_de_ingles;
					$country_nac   = $row->pais_de_nacimiento;
					$living_zone   = $row->Vivienda;
					$job_now       = $row->Empleo;
					$job_search    = $row->busqueda_laboral;
					$max_level_edu = $row->maximo_nivel_de_estudios_alcanzado;
					$dateBirthDay  = $row->Birthdate;
					$genre         = $row->Gender;
					$moreDis       = $row->detalle_discapacidad;
					$discaps       = $row->Discapacidad;
					$preferJobs    = $row->area_de_interes;
					$verifications::registerUser(
						$email,
						$password,
						$first_name,
						$last_name,
						$haveDiscap
					);
					$user   = get_user_by('email', $row->candidate_email_address);
					$userID = $user->ID;
					$verifications::updateUsersInformation(
						$userID,
						$dateBirthDay,
						$genre,
					);
					$resumeId = $verifications->getResumeId($userID);
					$verifications::updateIdioms(
						$resumeId,
						[1],
						[$nivel_ingles],
						$userID
					);
					$verifications::updateDiscapacidades(
						$resumeId,
						$discaps,
						$moreDis
					);
					$verifications::updatePrefersJobs(
						$userID,
						$preferJobs
					);
					update_user_meta($userID, 'country_nac', $country_nac);
					update_user_meta($userID, 'livingZone', $living_zone);
					update_user_meta($userID, 'workingSearch', $job_search);
					update_user_meta($userID, 'workingNow', $job_now);
					update_user_meta($userID, 'edu_levelMaxSec', $max_level_edu);
					echo json_encode([]);
				}
			}
		}
	}
}
