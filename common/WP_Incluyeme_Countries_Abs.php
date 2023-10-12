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
    private static $discapMore = 'IncluyemeDiscapMore';

    public function __construct()
    {
        global $wpdb;
        self::$wp = $wpdb;
        self::$dataPrefix = $wpdb->prefix;
        self::$usersDiscapTable = $wpdb->prefix . 'incluyeme_users_dicapselect';
        self::$incluyemeUsersInformation = $wpdb->prefix . 'incluyeme_users_information';
        self::$discapMore = get_option(self::$discapMore) ? get_option(self::$discapMore) : 'detalle';
    }

    /**
     * @param mixed $userID
     */

    public static function registerUser($email, $password, $first_name, $last_name, $haveDiscap = false)
    {
        $user = email_exists($email);
        	error_log( print_r( $user, true ) );
        if ($user) {
            return;
        }

        self::$userName = $first_name;
        self::$userLastName = $last_name;
        self::$userID = wp_insert_user([
            "user_login" => $email,
            "user_email" => $email,
            "user_pass" => $password,
            "role" => "subscriber"
        ]);

        update_user_meta(self::$userID, 'first_name', $first_name);
        update_user_meta(self::$userID, 'last_name', $last_name);
        self::$userSlug = Wpjb_Utility_Slug::generate(Wpjb_Utility_Slug::MODEL_RESUME, $first_name . ' ' . $last_name);
        $temp = wpjb_upload_dir("resume", "", null, "basedir");
        $finl = dirname($temp) . "/" . self::$userID;
        wpjb_rename_dir($temp, $finl);
        self::userRegisterWPBJ($haveDiscap == 'noDIS');
		error_log( print_r( self::$userID, true ) );
        return;
    }

    private static function userRegisterWPBJ($haveDiscap)
    {
        global $wpdb;
        $registerTime = current_time('mysql');
        $post_id = wp_insert_post([
            "post_title" => trim(self::$userName . " " . self::$userLastName),
            "post_name" => self::$userSlug,
            "post_type" => "resume",
            "post_status" => 'publish',
            "comment_status" => "closed"
        ]);
        $wpdb->insert($wpdb->prefix . 'wpjb_resume', [
            'post_id' => $post_id,
            'user_id' => self::$userID,
            'candidate_slug' => self::$userSlug,
            'created_at' => $registerTime,
            'modified_at' => $registerTime
        ]);
        $id = $wpdb->insert_id;
        self::$wp->insert(self::$wp->prefix . 'wpjb_resume_search', [
            'fullname' => self::$userName . ' ' . self::$userLastName,
            'location' => '',
            'details' => '',
            'details_all' => '',
            'resume_id' => $id,
        ]);
        if ($haveDiscap == true) {
            self::updateDiscapacidades($id, ['Ninguna'], 'Ninguna');
        }

        return $wpdb->insert_id;
    }

    public static function updateDiscapacidades($userID, $discaps, $moreDis)
    {
        $result2 = self::$wp->get_results("SELECT * from " . self::$dataPrefix . "wpjb_meta where 	meta_type = 3 and name = '" . self::$discap . "'");
        if (count($result2) > 0) {
            self::$wp->get_results('DELETE from ' . self::$dataPrefix . 'wpjb_meta_value WHERE object_id = ' . $userID . '  AND meta_id = ' . $result2[0]->id);
        }

        for ($i = 0; $i < count($discaps); $i++) {
            if ($discaps[$i] === 'Ninguna') {

                if (count($result2) > 0) {
                    self::$wp->insert(self::$dataPrefix . "wpjb_meta_value", [
                        'value' => 'Ninguna',
                        'object_id' => $userID,
                        'meta_id' => $result2[0]->id
                    ]);
                }

                return true;
            }
            $result = self::$wp->get_results('SELECT * from ' . self::$usersDiscapTable . ' where resume_id = ' . $userID . '  AND discap_id = ' . $discaps[$i]);
            $disca = null;
            switch ($discaps[$i]) {
                case 1:
                    $disca = 'Motriz';
                    break;
                case 2:
                    $disca = 'Auditiva';
                    break;
                case 3:
                    $disca = 'Visual';
                    break;
                case 4:
                    $disca = 'Visceral';
                    break;
                case 5:
                    $disca = 'Intelectual';
                    break;
                case 6:
                    $disca = 'Psíquica';
                    break;
                case 7:
                    $disca = 'Lenguaje';
                    break;
                default:
                    $disca = null;
                    break;
            }
            if ($disca != null) {
                if (count($result2) > 0) {
                    self::$wp->insert(self::$dataPrefix . "wpjb_meta_value", [
                        'value' => $disca,
                        'object_id' => $userID,
                        'meta_id' => $result2[0]->id
                    ]);
                }
            }
            if (count($result) <= 0) {
                self::$wp->insert(self::$usersDiscapTable, [
                    'discap_id' => $discaps[$i],
                    'resume_id' => $userID
                ]);
            }
        }

        self::$wp->get_results('UPDATE ' . self::$incluyemeUsersInformation . ' SET  	moreDis  = "' . $moreDis . '" WHERE resume_id = ' . $userID);
        if (count($discaps) !== 0) {
            self::$wp->get_results('DELETE from ' . self::$usersDiscapTable . ' WHERE resume_id = ' . $userID . '  AND discap_id NOT IN (' . implode(',', $discaps) . ')');
        }


        if ($moreDis !== null) {
            $result = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta where 	meta_type = 3 and name =  ' . "'" . self::$discapMore . "'");
            if (count($result) > 0) {
                $search = self::$wp->get_results('SELECT * from ' . self::$dataPrefix . 'wpjb_meta_value where meta_id  = ' . $result[0]->id . ' and object_id = ' . $userID);

                if (count($search) > 0) {
                    self::$wp->update(self::$dataPrefix . 'wpjb_meta_value', [
                        'value' => $moreDis,
                        'meta_id' =>
                        $result[0]->id,
                        'object_id' => $userID
                    ], [
                        'meta_id' =>
                        $result[0]->id,
                        'object_id' => $userID
                    ]);
                } else if (count($result) > 0) {
                    self::$wp->insert(self::$dataPrefix . 'wpjb_meta_value', [
                        'value' => $moreDis,
                        'meta_id' =>
                        $result[0]->id,
                        'object_id' => $userID
                    ]);
                }
            }
        }

        return true;
    }
}
