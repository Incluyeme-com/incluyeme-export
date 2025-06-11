<?php

namespace common;

require_once $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php';

class Queries
{

    private $wp;
    private $dataPrefix;
    public $numbersOfItems;
    public $CUD;

    public function __construct()
    {
        global $wpdb;
        $this->wp         = $wpdb;
        $this->dataPrefix = $wpdb->prefix;
        $this->CUD        = get_option('incluyemeFiltersCV') ?? 'certificado-discapacidad';
    }

    public function getUsersInformation($limit, $offset, $search = '', $orderColumn = 'created_at', $orderDirection = 'ASC')
    {
        $filters = [];
        foreach ($_POST['columns'] as $index => $column) {
            if (!empty($column['search']['value'])) {
                $value = '%' . $column['search']['value'] . '%';
                switch ($index) {
                    case 5:
                        $filters[] = "IUI.province LIKE '$value'";
                        break;
                    case 6:
                        $filters[] = "user_meta.zone LIKE '$value'";
                        break;
                    case 7:
                        $filters[] = "IUI.genre LIKE '$value'";
                        break;
                    case 9:
                        $filters[] = "IUDS.discap_names LIKE '$value'";
                        break;
                    case 10:
                        $filters[] = "user_meta.max_education_level LIKE '$value'";
                        break;
                    case 11:
                        $filters[] = "user_meta.has_job LIKE '$value'";
                        break;
                    case 12:
                        $filters[] = "user_meta.looking_for_job LIKE '$value'";
                        break;
                    case 13:
                        $filters[] = "user_meta.name_level LIKE '$value'";
                        break;
                    case 14:
                        $filters[] = "user_meta.area_of_interest LIKE '$value'";
                        break;
                    case 15:
                        $filters[] = "TAGS.meta_value LIKE '$value'";
                        break;
                }
            }
        }

        if ($search !== '') {
            $search = '%' . $search . '%';
            $filters[] = "(user_meta.first_name LIKE '$search' OR user_meta.last_name LIKE '$search' OR U.user_email LIKE '$search')";
        }

        $searchCondition = !empty($filters) ? "AND (" . implode(' AND ', $filters) . ")" : '';

        $users = "
    SELECT 
        RS.id AS resume_id,
        RS.user_id,
        RS.phone,
        U.user_email,
        user_meta.first_name,
        user_meta.last_name,
        user_meta.user_meta_json,
        COALESCE(user_meta.zone, 'No indica') AS zone,
        IFNULL(IUI.province, RS.candidate_location) AS province,
        IUI.moreDis AS reasonable_adjustments,
        IUI.genre AS gender,
        COALESCE(TAGS.meta_value, 'No indica') AS tags,
        COALESCE(IUDS.discap_names, 'No indica') AS disability,
        COALESCE(user_meta.birth_country, 'No indica') AS birth_country,
        COALESCE(user_meta.max_education_level, 'No indica') AS max_education_level,
        COALESCE(user_meta.has_job, 'No indica') AS has_job,
        COALESCE(user_meta.looking_for_job, 'No indica') AS looking_for_job,
        COALESCE(user_meta.ccd, 'No indica') AS ccd,
        COALESCE(user_meta.name_level, 'No indica') AS name_level,
        COALESCE(user_meta.area_of_interest, 'No indica') AS area_of_interest,
        RS.created_at
    FROM wp_wpjb_resume RS
    INNER JOIN wp_users U ON RS.user_id = U.id
    LEFT JOIN wp_incluyeme_users_information IUI ON RS.id = IUI.resume_id
    LEFT JOIN (
        SELECT RSU.user_id,
            MAX(CASE WHEN RSU.meta_key = 'first_name' THEN RSU.meta_value ELSE NULL END) AS first_name,
            MAX(CASE WHEN RSU.meta_key = 'last_name' THEN RSU.meta_value ELSE NULL END) AS last_name,
            MAX(CASE WHEN RSU.meta_key = 'livingZone' THEN RSU.meta_value ELSE NULL END) AS zone,
            MAX(CASE WHEN RSU.meta_key = 'country_nac' THEN RSU.meta_value ELSE NULL END) AS birth_country,
            MAX(CASE WHEN RSU.meta_key = 'edu_levelMaxSec' THEN RSU.meta_value ELSE NULL END) AS max_education_level,
            MAX(CASE WHEN RSU.meta_key = 'workingNow' THEN RSU.meta_value ELSE NULL END) AS has_job,
            MAX(CASE WHEN RSU.meta_key = 'workingSearch' THEN RSU.meta_value ELSE NULL END) AS looking_for_job,
            MAX(CASE WHEN RSU.meta_key = 'cudOption' THEN RSU.meta_value ELSE NULL END) AS ccd,
            MAX(CASE WHEN RSU.meta_key = 'area_interes' THEN RSU.meta_value ELSE NULL END) AS area_of_interest,
            MAX(CASE WHEN RSU.meta_key = 'english_level' THEN RSU.meta_value ELSE NULL END) AS name_level,
            CONCAT(
                '{', GROUP_CONCAT('\"', RSU.meta_key, '\": \"', RSU.meta_value, '\"' SEPARATOR ', '), '}'
            ) AS user_meta_json
        FROM wp_usermeta RSU
        WHERE RSU.meta_key IN (
            'first_name', 'last_name', 'livingZone', 'country_nac', 'edu_levelMaxSec', 
            'workingNow', 'workingSearch', 'cudOption', 'area_interes', 'english_level'
        )
        GROUP BY RSU.user_id
    ) AS user_meta ON RS.user_id = user_meta.user_id
    LEFT JOIN (
        SELECT resume_id, GROUP_CONCAT(discap_name SEPARATOR ', ') AS discap_names
        FROM wp_incluyeme_users_dicapselect IUDS
        INNER JOIN wp_incluyeme_discapacities ID ON IUDS.discap_id = ID.id
        GROUP BY resume_id
    ) AS IUDS ON RS.id = IUDS.resume_id
    LEFT JOIN (
        SELECT 
            user_id, 
            REPLACE(TRIM(BOTH '[' FROM TRIM(BOTH ']' FROM GROUP_CONCAT(JSON_UNQUOTE(JSON_EXTRACT(meta_value, '$[*].label')) SEPARATOR ', '))), '\"', '') AS meta_value
        FROM wp_usermeta
        WHERE meta_key = 'tagsIncluyeme'
        GROUP BY user_id
    ) AS TAGS ON RS.user_id = TAGS.user_id
    WHERE RS.is_active = 1
    $searchCondition
    ORDER BY $orderColumn $orderDirection
    LIMIT $limit OFFSET $offset;
    ";

        $information = $this->executeSQL($this->replaceString($users));

        foreach ($information as &$user) {
            $cv = $this->getCV($user->resume_id);
            $user->cv = $cv[0];

            $user->experience = $this->executeSQL("
            SELECT * 
            FROM wp_wpjb_resume_detail 
            WHERE type = 1 AND resume_id = {$user->resume_id}
        ");

            $user->education = $this->executeSQL("
            SELECT * 
            FROM wp_wpjb_resume_detail 
            WHERE type = 2 AND resume_id = {$user->resume_id}
        ");
        }

        return $information;
    }



    function getTotalRecords()
    {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->dataPrefix}wpjb_resume WHERE is_active = 1";
        return $wpdb->get_var($sql);
    }

    /* function getTotalFilteredRecords($search = '')
	{
		global $wpdb;
		$searchQuery = '';

		if (!empty($search)) {
			$search = '%' . $wpdb->esc_like($search) . '%';
			$searchQuery = $wpdb->prepare(
				"AND (
                user_meta.first_name LIKE %s
                OR user_meta.last_name LIKE %s
                OR U.user_email LIKE %s
            )",
				$search,
				$search,
				$search
			);
		}

		$sql = "
    SELECT COUNT(*)
    FROM {$this->dataPrefix}wpjb_resume RS
    INNER JOIN wp_users U ON RS.user_id = U.id
    LEFT JOIN (
        SELECT
            RSU.user_id,
            MAX(CASE WHEN RSU.meta_key = 'first_name' THEN RSU.meta_value ELSE NULL END) AS first_name,
            MAX(CASE WHEN RSU.meta_key = 'last_name' THEN RSU.meta_value ELSE NULL END) AS last_name
        FROM wp_usermeta RSU
        GROUP BY RSU.user_id
    ) AS user_meta ON RS.user_id = user_meta.user_id
    LEFT JOIN wp_incluyeme_users_information IUI ON RS.id = IUI.resume_id
    LEFT JOIN wp_usermeta TAGS ON RS.user_id = TAGS.user_id AND TAGS.meta_key = 'tagsIncluyeme'
    WHERE RS.is_active = 1
    $searchQuery";

		return $wpdb->get_var($sql);
	} */

    function getFilteredRecordsCount($search = '')
    {
        global $wpdb;
        $searchQuery = '';
        $filters = [];

        if (!empty($search)) {
            $search = '%' . $wpdb->esc_like($search) . '%';
            $searchQuery = $wpdb->prepare(
                "AND (
                user_meta.first_name LIKE %s
                OR user_meta.last_name LIKE %s
                OR U.user_email LIKE %s
            )",
                $search,
                $search,
                $search
            );
        }

        foreach ($_POST['columns'] as $index => $column) {
            if (!empty($column['search']['value'])) {
                $value = '%' . $column['search']['value'] . '%';
                switch ($index) {
                    case 5:
                        $filters[] = "IUI.province LIKE '$value'";
                        break;
                    case 6:
                        $filters[] = "user_meta.zone LIKE '$value'";
                        break;
                    case 7:
                        $filters[] = "IUI.genre LIKE '$value'";
                        break;
                    case 9:
                        $filters[] = "IUDS.discap_names LIKE '$value'";
                        break;
                    case 10:
                        $filters[] = "user_meta.max_education_level LIKE '$value'";
                        break;
                    case 11:
                        $filters[] = "user_meta.has_job LIKE '$value'";
                        break;
                    case 12:
                        $filters[] = "user_meta.looking_for_job LIKE '$value'";
                        break;
                    case 13:
                        $filters[] = "user_meta.name_level LIKE '$value'";
                        break;
                    case 14:
                        $filters[] = "user_meta.area_of_interest LIKE '$value'";
                        break;
                    case 15:
                        $filters[] = "TAGS.meta_value LIKE '$value'";
                        break;
                }
            }
        }

        $filterQuery = !empty($filters) ? "AND (" . implode(' AND ', $filters) . ")" : '';

        $sql = "
    SELECT COUNT(DISTINCT RS.id) AS recordsFiltered
    FROM wp_wpjb_resume RS
    INNER JOIN wp_users U ON RS.user_id = U.id
    LEFT JOIN wp_incluyeme_users_information IUI ON RS.id = IUI.resume_id
    LEFT JOIN (
        SELECT resume_id, GROUP_CONCAT(discap_name SEPARATOR ', ') AS discap_names
        FROM wp_incluyeme_users_dicapselect IUDS
        INNER JOIN wp_incluyeme_discapacities ID ON IUDS.discap_id = ID.id
        GROUP BY resume_id
    ) AS IUDS ON RS.id = IUDS.resume_id
    LEFT JOIN (
        SELECT RSU.user_id,
            MAX(CASE WHEN RSU.meta_key = 'first_name' THEN RSU.meta_value ELSE NULL END) AS first_name,
            MAX(CASE WHEN RSU.meta_key = 'last_name' THEN RSU.meta_value ELSE NULL END) AS last_name,
            MAX(CASE WHEN RSU.meta_key = 'livingZone' THEN RSU.meta_value ELSE NULL END) AS zone,
            MAX(CASE WHEN RSU.meta_key = 'edu_levelMaxSec' THEN RSU.meta_value ELSE NULL END) AS max_education_level,
            MAX(CASE WHEN RSU.meta_key = 'workingNow' THEN RSU.meta_value ELSE NULL END) AS has_job,
            MAX(CASE WHEN RSU.meta_key = 'workingSearch' THEN RSU.meta_value ELSE NULL END) AS looking_for_job,
            MAX(CASE WHEN RSU.meta_key = 'area_interes' THEN RSU.meta_value ELSE NULL END) AS area_interes,
            MAX(CASE WHEN RSU.meta_key = 'english_level' THEN RSU.meta_value ELSE NULL END) AS name_level
        FROM wp_usermeta RSU
        WHERE RSU.meta_key IN (
            'first_name', 'last_name', 'livingZone', 'edu_levelMaxSec', 
            'workingNow', 'workingSearch', 'area_interes', 'english_level'
        )
        GROUP BY RSU.user_id
    ) AS user_meta ON RS.user_id = user_meta.user_id
    LEFT JOIN (
        SELECT 
            user_id, 
            REPLACE(TRIM(BOTH '[' FROM TRIM(BOTH ']' FROM GROUP_CONCAT(JSON_UNQUOTE(JSON_EXTRACT(meta_value, '$[*].label')) SEPARATOR ', '))), '\"', '') AS meta_value
        FROM wp_usermeta
        WHERE meta_key = 'tagsIncluyeme'
        GROUP BY user_id
    ) AS TAGS ON RS.user_id = TAGS.user_id
    WHERE RS.is_active = 1
    $searchQuery
    $filterQuery";

        return $wpdb->get_var($sql);
    }


    public function getDistinctOptions()
    {
        $query = "
        SELECT DISTINCT
            COALESCE(IUI.province, 'No indica') AS provincia,
            COALESCE(user_meta.zone, 'No indica') AS zona,
            IUI.genre AS genero,
            COALESCE(IUDS.discap_names, 'No indica') AS discapacidad,
            COALESCE(user_meta.max_education_level, 'No indica') AS nivel_maximo_estudio,
            COALESCE(user_meta.has_job, 'No indica') AS tiene_trabajo,
            COALESCE(user_meta.looking_for_job, 'No indica') AS busqueda_laboral,
            COALESCE(user_meta.name_level, 'No indica') AS nivel_ingles,
            COALESCE(user_meta.area_interes, 'No indica') AS area_interes,
            COALESCE(TAGS.meta_value, 'No indica') AS tags
        FROM wp_wpjb_resume RS
        LEFT JOIN wp_incluyeme_users_information IUI ON RS.id = IUI.resume_id
        LEFT JOIN (
            SELECT resume_id, GROUP_CONCAT(discap_name SEPARATOR ', ') AS discap_names
            FROM wp_incluyeme_users_dicapselect IUDS
            INNER JOIN wp_incluyeme_discapacities ID ON IUDS.discap_id = ID.id
            GROUP BY resume_id
        ) AS IUDS ON RS.id = IUDS.resume_id
        LEFT JOIN (
            SELECT RSU.user_id,
                MAX(CASE WHEN RSU.meta_key = 'livingZone' THEN RSU.meta_value ELSE NULL END) AS zone,
                MAX(CASE WHEN RSU.meta_key = 'edu_levelMaxSec' THEN RSU.meta_value ELSE NULL END) AS max_education_level,
                MAX(CASE WHEN RSU.meta_key = 'workingNow' THEN RSU.meta_value ELSE NULL END) AS has_job,
                MAX(CASE WHEN RSU.meta_key = 'workingSearch' THEN RSU.meta_value ELSE NULL END) AS looking_for_job,
                MAX(CASE WHEN RSU.meta_key = 'area_interes' THEN RSU.meta_value ELSE NULL END) AS area_interes,
                MAX(CASE WHEN RSU.meta_key = 'english_level' THEN RSU.meta_value ELSE NULL END) AS name_level
            FROM wp_usermeta RSU
            WHERE RSU.meta_key IN (
                'livingZone', 'edu_levelMaxSec', 'workingNow', 
                'workingSearch', 'area_interes', 'english_level'
            )
            GROUP BY RSU.user_id
        ) AS user_meta ON RS.user_id = user_meta.user_id
        LEFT JOIN (
            SELECT 
                user_id, 
                REPLACE(TRIM(BOTH '[' FROM TRIM(BOTH ']' FROM GROUP_CONCAT(JSON_UNQUOTE(JSON_EXTRACT(meta_value, '$[*].label')) SEPARATOR ', '))), '\"', '') AS meta_value
            FROM wp_usermeta
            WHERE meta_key = 'tagsIncluyeme'
            GROUP BY user_id
        ) AS TAGS ON RS.user_id = TAGS.user_id
        WHERE RS.is_active = 1;
    ";

        return $this->executeSQL($query);
    }


    private function getCV($id)
    {
        $path = wp_upload_dir();
        $basePath = $path['basedir'];
        $baseDir = $path['baseurl'];
        $route = $basePath . '/wpjobboard/resume/' . $id;
        $dir = $baseDir . '/wpjobboard/resume/' . $id;
        $CV = false;
        if (file_exists($route)) {
            if (file_exists($route . '/cv/')) {
                $folder = @scandir($route . '/cv/');
                if (count($folder) > 2) {
                    $search = opendir($route . '/cv/');
                    while ($file = readdir($search)) {
                        if ($file != "." and $file != ".." and $file != "index.php") {
                            $CV = $dir . '/cv/' . $file;
                            break;
                        }
                    }
                }
            }
        }
        return [$CV];
    }


    public function getFormatInformation($limit, $offset, $search = '')
    {
        return $this->formatInformation($this->getUsersInformation($limit, $offset, $search));
    }

    private function executeSQL($query)
    {
        return $this->wp->get_results($query);
    }

    public function replaceString($query): string
    {
        $patterns     = ['/export_prefix_/', '/export_incluyeme_/'];
        $replacements = [$this->dataPrefix, $this->dataPrefix . 'incluyeme_'];

        return preg_replace($patterns, $replacements, $query);
    }

    public function formatInformation($information)
    {
        $columns = [];

        foreach ($information as $info) {
            $usersInfo = str_replace("`", "\"", $info->user_meta_json);
            $userInfo  = json_decode($usersInfo, true);

            $columns[] = [
                'first_name'             => $userInfo["first_name"] ?? 'No indica',
                'user_id'                => $info->user_id ?? 'No indica',
                'last_name'              => $userInfo["last_name"] ?? 'No indica',
                'user_email'             => $info->user_email ?? 'No indica',
                'phone'                  => $info->phone ?? 'No indica',
                'province'               => $info->province ?? 'No indica',
                'zone'                   => $userInfo["livingZone"] ?? 'No indica',
                'gender'                 => $info->gender ?? 'No indica',
                'birth_country'          => $userInfo["country_nac"] ?? 'No indica',
                'disability'             => $info->disability ?? 'No indica',
                'reasonable_adjustments' => $info->reasonable_adjustments ?? 'No indica',
                'max_education_level'    => $userInfo["edu_levelMaxSec"] ?? 'No indica',
                'has_job'                => $userInfo["workingNow"] ?? 'No indica',
                'looking_for_job'        => $userInfo["workingSearch"] ?? 'No indica',
                'ccd'                    => $userInfo["cudOption"] ?? 'No indica',
                'name_level'             => $userInfo["english_level"] ?? 'No indica',
                'area_of_interest'       => $userInfo["area_interes"] ?? 'No indica',
                'tags'                   => $info->tags ? $this->formatTags($info->tags) : 'No indica',
                'created_at'             => $info->created_at ?? 'No indica',
                'education'              => $info->education ?? 'No indica',
                'experience'             => $info->experience ?? 'No indica',
                'cv'                     => !empty($variable) ?? 'No indica',
            ];
        }

        return $columns;
    }

    private function formatTags($tags): string
    {
        $tagsData = json_decode($tags);

        $labelsString = '';

        foreach ($tagsData as $tag) {
            $labelsString .= $tag->label . ', ';
        }

        return rtrim($labelsString, ', ');
    }

    public function deleteUserAllTags($users_id)
    {
        if (! is_array($users_id)) {
            return;
        }
        foreach ($users_id as $elemento) {
            $this::deleteAllTags($elemento);
        }
    }

    public function updateUserTags($users_id, $tags)
    {
        if (! is_array($users_id)) {
            return;
        }
        foreach ($users_id as $elemento) {
            $this::saveAllTags($elemento, $tags);
        }
    }

    private function deleteAllTags($users_id)
    {
        delete_user_meta($users_id, 'tagsIncluyeme');
    }

    private function saveAllTags($users_id, $tags)
    {
        update_user_meta($users_id, 'tagsIncluyeme', $tags);
    }
}
