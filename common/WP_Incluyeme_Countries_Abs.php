<?php

namespace common;

use Wpjb_Utility_Slug;

require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

abstract class WP_Incluyeme_Countries_Abs
{
	protected static $userName;
	protected static $userLastName;
	protected static $userSlug;
	protected static $userID;
	protected static $wp;
	protected static $usersDiscapTable;
	protected static $incluyemeUsersInformation;
	protected static $dataPrefix;
	protected static $incluyemeLoginCountry;
	protected static $usersIdioms;
	protected static $idioms;
	private static $idioma_ingles = 'Incluyemeidioma_ingles';
	private static $idioma_frances = 'Incluyemeidioma_frances';
	private static $idioma_portugues = 'Incluyemeidioma_portugues';
	private static $idioma_aleman = 'Incluyemeidioma_aleman';
	private static $discapMore = 'IncluyemeDiscapMore';
	private static $discap = 'IncluyemeDiscap';

	public function __construct()
	{
		global $wpdb;
		self::$wp                        = $wpdb;
		self::$dataPrefix                = $wpdb->prefix;
		self::$usersDiscapTable          = $wpdb->prefix . 'incluyeme_users_dicapselect';
		self::$incluyemeUsersInformation = $wpdb->prefix . 'incluyeme_users_information';
		self::$incluyemeLoginCountry     = 'incluyemeLoginCountry';
		self::$usersIdioms               = $wpdb->prefix . 'incluyeme_users_idioms';
		self::$idioms                    = $wpdb->prefix . 'incluyeme_idioms';
		self::$idioma_ingles             = get_option(self::$idioma_ingles) ? get_option(self::$idioma_ingles) : 'idioma_ingles';
		self::$idioma_frances            = get_option(self::$idioma_frances) ? get_option(self::$idioma_frances) : 'idioma_frances';
		self::$idioma_portugues          = get_option(self::$idioma_portugues) ? get_option(self::$idioma_portugues) : 'idioma_portugues';
		self::$idioma_aleman             = get_option(self::$idioma_aleman) ? get_option(self::$idioma_aleman) : 'idioma_aleman';
		self::$discap                    = get_option(self::$discap) ? get_option(self::$discap) : 'tipo_discapacidad';
		self::$discapMore                = get_option(self::$discapMore) ? get_option(self::$discapMore) : 'detalle';
	}

	/**
	 * @param mixed $userID
	 */

	public static function registerUser($email, $password, $first_name, $last_name, $haveDiscap = false)
	{
		$user = email_exists($email);
		error_log(print_r($user, true));
		if ($user) {
			return;
		}

		self::$userName     = $first_name;
		self::$userLastName = $last_name;
		self::$userID       = wp_insert_user([
			"user_login" => $email,
			"user_email" => $email,
			"user_pass"  => $password,
			"role"       => "subscriber"
		]);

		update_user_meta(self::$userID, 'first_name', $first_name);
		update_user_meta(self::$userID, 'last_name', $last_name);
		self::$userSlug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $first_name . ' ' . $last_name);
		$temp           = wpjb_upload_dir("resume", "", null, "basedir");
		$finl           = dirname($temp) . "/" . self::$userID;
		wpjb_rename_dir($temp, $finl);
		self::userRegisterWPBJ($haveDiscap == 'noDIS');
		error_log(print_r(self::$userID, true));

		return;
	}

	private static function userRegisterWPBJ($haveDiscap)
	{
		global $wpdb;
		$registerTime = current_time('mysql');
		$post_id      = wp_insert_post([
			"post_title"     => trim(self::$userName . " " . self::$userLastName),
			"post_name"      => self::$userSlug,
			"post_type"      => "resume",
			"post_status"    => 'publish',
			"comment_status" => "closed"
		]);
		$wpdb->insert($wpdb->prefix . 'wpjb_resume', [
			'post_id'        => $post_id,
			'user_id'        => self::$userID,
			'candidate_slug' => self::$userSlug,
			'created_at'     => $registerTime,
			'modified_at'    => $registerTime
		]);
		$id = $wpdb->insert_id;
		self::$wp->insert(self::$wp->prefix . 'wpjb_resume_search', [
			'fullname'    => self::$userName . ' ' . self::$userLastName,
			'location'    => '',
			'details'     => '',
			'details_all' => '',
			'resume_id'   => $id,
		]);
		if ($haveDiscap == true) {
			self::updateDiscapacidades($id, ['Ninguna'], 'Ninguna');
		}

		return $wpdb->insert_id;
	}

	public static function updateDiscapacidades($userID, $discaps, $moreDis)
	{
		$arrayDiscaps = explode(",", $discaps);
		foreach ($arrayDiscaps as $key => $value) {
			$arrayDiscaps[$key] = trim($value, ' "');
		}
		$result2 = self::$wp->get_results("SELECT * from " . self::$dataPrefix . "wpjb_meta where 	meta_type = 3 and name = '" . self::$discap . "'");
		if (count($result2) > 0) {
			self::$wp->get_results('DELETE from ' . self::$dataPrefix . 'wpjb_meta_value WHERE object_id = ' . $userID . '  AND meta_id = ' . $result2[0]->id);
		}
		for ($i = 0; $i < count($arrayDiscaps); $i++) {
			if ($arrayDiscaps[$i] === 'Ninguna') {

				if (count($result2) > 0) {
					self::$wp->insert(self::$dataPrefix . "wpjb_meta_value", [
						'value'     => 'Ninguna',
						'object_id' => $userID,
						'meta_id'   => $result2[0]->id
					]);
				}

				return true;
			}

			$disca  = null;
			switch ($arrayDiscaps[$i]) {
				case 'Motriz':
					$disca = 1;
					break;
				case 'Auditiva':
					$disca = 2;
					break;
				case 'Visual':
					$disca = 3;
					break;
				case 'Visceral':
					$disca = 4;
					break;
				case 'Intelectual':
					$disca = 5;
					break;
				case 'Psíquica':
					$disca = 6;
					break;
				case 'Lenguaje':
					$disca = 7;
					break;
				default:
					$disca = null;
					break;
			}

			$result = self::$wp->get_results('SELECT * from ' . self::$usersDiscapTable . ' where resume_id = ' . $userID . '  AND discap_id = ' . $disca);

			if (count($result2) > 0) {
				self::$wp->insert(self::$dataPrefix . "wpjb_meta_value", [
					'value'     => $arrayDiscaps[$i],
					'object_id' => $userID,
					'meta_id'   => $result2[0]->id
				]);
			}

			if ($disca != null) {
				if (count($result) <= 0) {
					self::$wp->insert(self::$usersDiscapTable, [
						'discap_id' => $disca,
						'resume_id' => $userID
					]);
				}
			}

			/* if ($disca != null) {
				self::$wp->get_results("DELETE from " . self::$usersDiscapTable . " WHERE resume_id = " . $userID . " AND discap_id = " . $disca);
			} */
		}

		self::$wp->get_results('UPDATE ' . self::$incluyemeUsersInformation . ' SET moreDis = "' . $moreDis . '" WHERE resume_id = ' . $userID);

		if ($moreDis !== null) {
			$result = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta where 	meta_type = 3 and name =  ' . "'" . self::$discapMore . "'");
			if (count($result) > 0) {
				$search = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta_value where meta_id  = ' . $result[0]->id . ' and object_id = ' . $userID);

				if (count($search) > 0) {
					self::$wp->update(self::$dataPrefix . 'wpjb_meta_value', [
						'value'     => $moreDis,
						'meta_id'   =>
						$result[0]->id,
						'object_id' => $userID
					], [
						'meta_id'   =>
						$result[0]->id,
						'object_id' => $userID
					]);
				} else if (count($result) > 0) {
					self::$wp->insert(self::$dataPrefix . 'wpjb_meta_value', [
						'value'     => $moreDis,
						'meta_id'   =>
						$result[0]->id,
						'object_id' => $userID
					]);
				}
			}
		}

		return true;
	}

	public static function updateIdioms($userID, $idioms, $oLevel, $id)
	{

		for ($i = 0; $i < count($idioms); $i++) {

			$result = self::$wp->get_results('SELECT * from ' . self::$usersIdioms . ' where resume_id = ' . $userID . '  AND idioms_id = ' . $idioms[$i]);

			$idiomsName     = null;
			$idiomsName     = self::$idioma_ingles;
			$level          = 'No hablo';
			$allowed_levels = ['Básico', 'Intermedio', 'Avanzado', 'Bilingüe'];
			$index          = array_search($oLevel[$i], $allowed_levels);
			if ($index !== false) {
				$level = $allowed_levels[$index];
			} else {
				return;
			}
			update_user_meta($id, 'english_level', $level);
			$index = +1;
			if (count($result) > 0) {
				self::$wp->update(self::$usersIdioms, [
					'idioms_id' => $idioms[$i],
					'slevel'    => $index,
					'olevel'    => $index,
					'wlevel'    => $index,

				], ['resume_id' => $userID, 'idioms_id' => $idioms[$i]]);
			} else {
				self::$wp->insert(self::$usersIdioms, [
					'idioms_id' => $idioms[$i],
					'slevel'    => $index,
					'olevel'    => $index,
					'wlevel'    => $index,
					'resume_id' => $userID,
					'idioms_id' => $idioms[$i]
				]);
			}
			if ($idiomsName !== null) {
				$result = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta where 	meta_type = 3 and name = ' . "'" . $idiomsName . "'");
				if (count($result) > 0) {
					$search = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta_value where meta_id  = ' . $result[0]->id . ' and object_id = ' . $userID);
					if (count($search) > 0) {
						self::$wp->update(self::$dataPrefix . 'wpjb_meta_value', [
							'value'     => $level,
							'meta_id'   =>
							$result[0]->id,
							'object_id' => $userID
						], [
							'meta_id'   =>
							$result[0]->id,
							'object_id' => $userID
						]);
					} else if (count($result) > 0) {
						self::$wp->insert(self::$dataPrefix . 'wpjb_meta_value', [
							'value'     => $level,
							'meta_id'   =>
							$result[0]->id,
							'object_id' => $userID
						]);
					}
				}
			}
		}

		self::$wp->get_results('DELETE from ' . self::$usersIdioms . ' WHERE resume_id = ' . $userID . '  AND idioms_id NOT IN (' . implode(',', $idioms) . ')');
	}

	public static function updateUsersInformation($userID, $dateBirthDay, $genre)
	{

		add_user_meta($userID, 'genre', $genre);
		$verifications = self::$wp->get_results('SELECT * FROM ' . self::$dataPrefix . 'wpjb_resume
										WHERE ' . self::$dataPrefix . 'wpjb_resume.user_id = ' . $userID . ' LIMIT 1 ');
		if (count($verifications) <= 0) {
			return true;
		}
		$userID       = $verifications[0]->id;
		$verification = self::$wp->get_results('SELECT * from ' . self::$wp->prefix . 'incluyeme_users_information where resume_id = ' . $userID);
		if (count($verification) > 0) {
			self::$wp->update(self::$incluyemeUsersInformation, [
				'genre'    => $genre ?? '',
				'birthday' => $dateBirthDay ?? '',
			], ['resume_id' => $userID]);
		} else {
			self::$wp->insert(self::$incluyemeUsersInformation, [
				'genre'     => $genre ?? '',
				'birthday'  => $dateBirthDay ?? '',
				'resume_id' => $userID,
			]);
		}

		return $userID;
	}

	public function getResumeId($userID)
	{

		$verifications = self::$wp->get_results('SELECT * FROM ' . self::$dataPrefix . 'wpjb_resume
										WHERE ' . self::$dataPrefix . 'wpjb_resume.user_id = ' . $userID . ' LIMIT 1 ');
		if (count($verifications) <= 0) {
			return false;
		}

		return $verifications[0]->id;
	}

	public static function updatePrefersJobs($userID, $preferJobs)
	{
		$table_name = self::$dataPrefix . "incluyeme_prefersjobs";
		$preferJobs_id = array(
			"Informática" => 1,
			"Ventas" => 2,
			"Tecnología" => 3,
			"Atención al cliente" => 4,
			"Contabilidad/finanzas" => 5,
			"Call Center" => 6,
			"Marketing" => 7,
			"Administracion" => 8,
			"Recursos humanos" => 9,
			"Comunicación" => 10,
			"Logistica" => 11,
			"Comercio exterior" => 12,
			"Legales" => 13,
			"Seguridad" => 14,
			"Ingeniería" => 15,
			"Gastronomía" => 16,
			"Otros" => 	17
		);

		$convertArrayPreferJob = explode(",", $preferJobs);
		$preferJobsConvert = trim($convertArrayPreferJob[0], ' "');
		if (array_key_exists($preferJobsConvert, $preferJobs_id)) {
			$preferJobsConvert = $preferJobs_id[$preferJobsConvert];
		} else {
			return;
		}
		$myrows     = self::$wp->get_results("SELECT * FROM " . $table_name . " where id=" . $preferJobsConvert . "");
		foreach ($myrows as $details) {
			add_user_meta($userID, 'area_interes', $details->jobs_prefers);
			update_user_meta($userID, 'area', $details->jobs_prefers);
		}
		self::$wp->update(self::$incluyemeUsersInformation, [
			'preferjob_id' => $preferJobsConvert,

		], ['resume_id' => $userID]);
	}
}
