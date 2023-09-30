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
	
	public function getUsersInformation() {
		$users = "
SELECT
    RS.id AS resume_id,
    RS.user_id,
    RS.phone,
    U.user_email,
    user_meta.user_meta_json,
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
    CONCAT(
    '{',
    GROUP_CONCAT(
        '`', RSU.meta_key, '`: `', RSU.meta_value, '`'
        SEPARATOR ', '
    ),
    '}'
) AS user_meta_json
FROM wp_usermeta RSU
WHERE RSU.meta_key IN (
    'english_level', 'first_name', 'area_interes',
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
WHERE RS.is_active = 1;";
		
		
		$information = $this->executeSQL( $this->replaceString( $users ) );
		
		return $information;
	}
	
	
	public function getFormatInformation() {
		return $this->formatInformation( $this->getUsersInformation() );
	}
	
	private function executeSQL( $query ) {
		return $this->wp->get_results( $query );
	}
	
	public function replaceString( $query ): string {
		$patterns     = [ '/export_prefix_/', '/export_incluyeme_/' ];
		$replacements = [ $this->dataPrefix, $this->dataPrefix . 'incluyeme_' ];
		
		return preg_replace( $patterns, $replacements, $query );
	}
	
	public function formatInformation( $information ) {
		$columns = [];
		foreach ( $information as $info ) {
			$usersInfo = str_replace( "`", "\"", $info->user_meta_json );
			$userInfo  = json_decode( $usersInfo, true );

			$columns[] = [
				'first_name'             => $userInfo["first_name"] ?? 'NONE',
				'user_id'                => $info->user_id ?? 'NONE',
				'last_name'              => $userInfo["last_name"] ?? 'NONE',
				'user_email'             => $info->user_email ?? 'NONE',
				'phone'                  => $info->phone ?? 'NONE',
				'province'               => $info->province ?? 'NONE',
				'zone'                   => $userInfo["livingZone"] ?? 'NONE',
				'gender'                 => $info->gender ?? 'NONE',
				'birth_country'          => $userInfo["country_nac"] ?? 'NONE',
				'disability'             => $info->disability ?? 'NONE',
				'reasonable_adjustments' => $info->reasonable_adjustments ?? 'NONE',
				'max_education_level'    => $userInfo["edu_levelMaxSec"] ?? 'NONE',
				'has_job'                => $userInfo["workingNow"] ?? 'NONE',
				'looking_for_job'        => $userInfo["workingSearch"] ?? 'NONE',
				'ccd'                    => $userInfo["cudOption"] ?? 'NONE',
				'name_level'             => $userInfo["english_level"] ?? 'NONE',
				'area_of_interest'       => $userInfo["area_interes"] ?? 'NONE',
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
