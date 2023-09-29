<?php

namespace common;
require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

class Queries {
	
	private $wp;
	private $dataPrefix;
	public $numbersOfItems;
	public $CUD;
	
	public function __construct() {
		global $wpdb;
		$this->wp         = $wpdb;
		$this->dataPrefix = $wpdb->prefix;
		$this->CUD        = get_option( 'incluyemeFiltersCV' ) ?? 'certificado-discapacidad';
	}
	
	public function getUsersInformation(): array {
		$users       = "
SELECT
    RS.id AS resume_id,
    RS.user_id,
    RS.phone,
    user_meta.english_level,
    U.user_email,
    user_meta.first_name,
    user_meta.last_name,
    user_meta.area_interes,
    user_meta.country_nac,
    user_meta.livingZone,
    user_meta.edu_levelMaxSec,
    user_meta.cudOption,
    user_meta.workingSearch,
    user_meta.workingNow,
    IFNULL(IUI.province, RS.candidate_location) AS province,
    IUI.moreDis AS reasonable_adjustments,
    IUI.genre AS gender,
    TAGS.meta_value AS tags,
    IUDS.discap_names AS disability
FROM wp_wpjb_resume RS
INNER JOIN wp_users U ON RS.user_id = U.id
LEFT JOIN wp_incluyeme_users_information IUI ON RS.id = IUI.resume_id
LEFT JOIN (
    SELECT
        RSU.user_id,
        MAX(CASE WHEN RSU.meta_key = 'first_name' THEN RSU.meta_value END) AS first_name,
        MAX(CASE WHEN RSU.meta_key = 'last_name' THEN RSU.meta_value END) AS last_name,
        MAX(CASE WHEN RSU.meta_key = 'area_interes' THEN RSU.meta_value END) AS area_interes,
        MAX(CASE WHEN RSU.meta_key = 'country_nac' THEN RSU.meta_value END) AS country_nac,
        MAX(CASE WHEN RSU.meta_key = 'livingZone' THEN RSU.meta_value END) AS livingZone,
        MAX(CASE WHEN RSU.meta_key = 'edu_levelMaxSec' THEN RSU.meta_value END) AS edu_levelMaxSec,
        MAX(CASE WHEN RSU.meta_key = 'cudOption' THEN RSU.meta_value END) AS cudOption,
        MAX(CASE WHEN RSU.meta_key = 'workingSearch' THEN RSU.meta_value END) AS workingSearch,
        MAX(CASE WHEN RSU.meta_key = 'workingNow' THEN RSU.meta_value END) AS workingNow,
        MAX(CASE WHEN RSU.meta_key = 'english_level' THEN RSU.meta_value END) AS english_level
    FROM wp_usermeta RSU
    WHERE RSU.meta_key IN (
        'english_level', 'first_name', 'tagsIncluyeme', 'area_interes',
        'country_nac', 'livingZone', 'edu_levelMaxSec', 'workingSearch',
        'workingNow', 'last_name', 'cudOption'
    )
    GROUP BY RSU.user_id
) AS user_meta ON RS.user_id = user_meta.user_id
LEFT JOIN (
    SELECT resume_id, GROUP_CONCAT(discap_name SEPARATOR ', ') AS discap_names
    FROM wp_incluyeme_users_dicapselect IUDS
    INNER JOIN wp_incluyeme_discapacities ID ON IUDS.discap_id = ID.id
    GROUP BY resume_id
) AS IUDS ON RS.id = IUDS.resume_id
LEFT JOIN wp_usermeta TAGS ON RS.user_id = TAGS.user_id AND TAGS.meta_key = 'tagsIncluyeme'
WHERE RS.is_active = 1;

";
		$information = $this->executeSQL( $this->replaceString( $users ) );
		
		return $information;
	}
	
	
	public function getFormatInformation(): array {
		return $this->formatInformation( $this->getUsersInformation() );
	}
	
	private function executeSQL( $query ): array {
		return $this->wp->get_results( $query );
	}
	
	public function replaceString( $query ): string {
		$patterns     = [ '/export_prefix_/', '/export_incluyeme_/' ];
		$replacements = [ $this->dataPrefix, $this->dataPrefix . 'incluyeme_' ];
		
		return preg_replace( $patterns, $replacements, $query );
	}
	
	public function formatInformation( $information ): array {
		$columns = [];
		foreach ( $information as $info ) {
			$columns[] = [
				'first_name'             => $info->first_name ?? 'NONE',
				'user_id'                => $info->user_id ?? 'NONE',
				'last_name'              => $info->last_name ?? 'NONE',
				'user_email'             => $info->user_email ?? 'NONE',
				'phone'                  => $info->phone ?? 'NONE',
				'province'               => $info->province ?? 'NONE',
				'zone'                   => $info->living_zone ?? 'NONE',
				'gender'                 => $info->gender ?? 'NONE',
				'birth_country'          => $info->birthday_country ?? 'NONE',
				'disability'             => $info->disability ?? 'NONE',
				'reasonable_adjustments' => $info->reasonable_adjustments ?? 'NONE',
				'max_education_level'    => $info->max_education_level ?? 'NONE',
				'has_job'                => $info->has_job,
				'looking_for_job'        => $info->looking_for_job ?? 'NONE',
				'ccd'                    => $info->cudOption ?? 'NONE',
				'name_level'             => $info->name_level ?? 'NONE',
				'area_of_interest'       => $info->area_of_interest ?? 'NONE',
				'tags'                   => $info->tags ? $this->formatTags( $info->tags ) : 'NONE'
			];
		}
		
		return $columns;
	}
	
	private function formatTags( $tags ): string {
		$tagsData = json_decode( $tags );
		
		$labelsString = '';
		
		foreach ( $tagsData as $tag ) {
			$labelsString .= $tag->label . ', ';
		}
		
		return rtrim( $labelsString, ', ' );
	}
	
	public function deleteUserAllTags( $users_id ) {
		if ( ! is_array( $users_id ) ) {
			return;
		}
		foreach ( $users_id as $elemento ) {
			$this::deleteAllTags( $elemento );
		}
	}
	
	public function updateUserTags( $users_id, $tags ) {
		if ( ! is_array( $users_id ) ) {
			return;
		}
		foreach ( $users_id as $elemento ) {
			$this::saveAllTags( $elemento, $tags );
		}
	}
	
	private function deleteAllTags( $users_id ) {
		delete_user_meta( $users_id, 'tagsIncluyeme' );
	}
	
	private function saveAllTags( $users_id, $tags ) {
		update_user_meta( $users_id, 'tagsIncluyeme', $tags );
	}
}
