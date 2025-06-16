<?php
namespace common;
include_once dirname(__DIR__) . '/common/WP_Incluyeme_Countries_Abs.php';

class WP_Incluyeme_Login_Countries extends WP_Incluyeme_Countries_Abs
{
    
    
    public function json_response($code = 200, $message = null)
    {
   
        header_remove();
     
        http_response_code($code);
    
        header("Cache-Control: no-transform,public");
 
        header('Content-Type: application/json; charset=utf-8');
        $status = [
            200 => '200 OK',
            400 => '400 Bad Request',
            422 => 'Unprocessable Entity',
            500 => '500 Internal Server Error'
        ];

        header('Status: ' . $status[$code]);

        return json_encode([
            'status' => $code < 300,
            'message' => $message
        ]);
    }
}
