<?php
require_once './lib/WP_Incluyeme_Login_Countries.php';
require_once 'vendor/autoload.php';

header('Content-type: application/json');
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode($_POST['data']);
    error_log(print_r($data, true));
    $verifications = new WP_Incluyeme_Login_Countries();
    if (is_array($data) || is_object($data)) {
        foreach ($data as $row) {
            $email = $row[0];
            $password = $row[1];
            $first_name = $row[2];
            $last_name = $row[3];
            $haveDiscap = $row[4];
            
            if ($row[0] != '') {
                echo $email,
                $password,
                $first_name,
                $last_name,
                $haveDiscap;
                $response = $verifications::registerUser(
                    $email,
                    $password,
                    $first_name,
                    $last_name,
                    $haveDiscap
                );
                echo $verifications->json_response(200, $response);
            }
        }
    }
}
