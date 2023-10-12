<?php
include 'WP_Incluyeme_Login_Countries.php';

class WP_Incluyeme_Login_Countries /* extends WP_Incluyeme_Countries_Abs */
{


    public function json_response($code = 200, $message = null)
    {
        // clear the old headers
        header_remove();
        // set the actual code
        http_response_code($code);
        // set the header to make sure cache is forced
        header("Cache-Control: no-transform,public");
        // treat this as json
        header('Content-Type: application/json; charset=utf-8');
        $status = [
            200 => '200 OK',
            400 => '400 Bad Request',
            422 => 'Unprocessable Entity',
            500 => '500 Internal Server Error'
        ];
        // ok, validation error, or failure
        header('Status: ' . $status[$code]);
        // return the encoded json
        return json_encode([
            'status' => $code < 300, // success or not?
            'message' => $message
        ]);
    }
}
