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
        RS.id as resume_id,
        RS.user_id,
        RS.phone,
        IDUI.name_level,
        U.user_email,
        UM.meta_value AS first_name,
        UMLN.meta_value AS last_name,
        UAI.meta_value AS area_of_interest,
        UNC.meta_value AS birthday_country,
         ULZ.meta_value AS living_zone,
         ELMS.meta_value AS max_education_level,
            cudOption.meta_value AS cudOption,
         UWN.meta_value AS has_job,
           UWS.meta_value AS looking_for_job,
        IF(IUI.province IS NULL, RS.candidate_location, IUI.province) AS province,
        IUI.moreDis AS reasonable_adjustments,
        IUI.genre AS gender,
        TAGS.meta_value AS tags,
        IUDS.discap_names AS disability
    FROM
        export_prefix_wpjb_resume RS
    INNER JOIN
        export_prefix_users U ON RS.user_id = U.id
    INNER JOIN (
        SELECT
            resume_id, GROUP_CONCAT(discap_name SEPARATOR ', ') AS discap_names
        FROM
            wp_incluyeme_users_dicapselect IUDS
        INNER JOIN
            wp_incluyeme_discapacities ID ON IUDS.discap_id = ID.id
        GROUP BY
            resume_id
    ) AS IUDS ON IUDS.resume_id = RS.id
    LEFT JOIN (
        SELECT
            resume_id, IIL.name_level
        FROM
            export_prefix_incluyeme_users_idioms IUI
        INNER JOIN
            export_prefix_incluyeme_idioms II ON II.id = IUI.idioms_id
        INNER JOIN
            export_prefix_incluyeme_idioms_level IIL ON IIL.id = IUI.slevel
        WHERE
            II.name_idioms = 'Ingles'
    ) IDUI ON RS.id = IDUI.resume_id
    LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'first_name'
    ) UM ON UM.user_id = RS.user_id
    LEFT JOIN (
        SELECT
            TAGS.meta_value, TAGS.user_id
        FROM
            export_prefix_usermeta TAGS
        WHERE
            TAGS.meta_key = 'tagsIncluyeme'
    ) TAGS ON TAGS.user_id = RS.user_id
    LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'area_interes'
    ) UAI ON UAI.user_id = RS.user_id
      LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'country_nac'
    ) UNC ON UAI.user_id = RS.user_id
     LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'livingZone'
    ) ULZ ON UAI.user_id = RS.user_id
    LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'edu_levelMaxSec'
    ) ELMS ON UAI.user_id = RS.user_id
     LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'workingSearch'
    ) UWS ON UAI.user_id = RS.user_id
       LEFT JOIN (
        SELECT
            UM.meta_value, UM.user_id
        FROM
            export_prefix_usermeta UM
        WHERE
            UM.meta_key = 'workingNow'
    ) UWN ON UAI.user_id = RS.user_id
    LEFT JOIN (
        SELECT
            UMLN.meta_value, UMLN.user_id
        FROM
            export_prefix_usermeta UMLN
        WHERE
            UMLN.meta_key = 'last_name'
    ) UMLN ON UMLN.user_id = RS.user_id
     LEFT JOIN (
        SELECT
            cudOption.meta_value, cudOption.user_id
        FROM
            export_prefix_usermeta cudOption
        WHERE
            cudOption.meta_key = 'cudOption'
    ) cudOption ON cudOption.user_id = RS.user_id
    LEFT JOIN (
        SELECT * FROM export_prefix_incluyeme_users_information
    ) IUI ON IUI.resume_id = RS.id
    WHERE
        is_active = 1
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
