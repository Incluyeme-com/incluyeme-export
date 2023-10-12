<?php
include_once './lib/WP_Incluyeme_Login_Countries.php';
require 'vendor/autoload.php';

class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function readCell($column, $row, $worksheetName = '')
    {
        if ($row > 1) {
            return true;
        }
        return false;
    }
}
$verifications = new WP_Incluyeme_Login_Countries();
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
$inputFile = $_FILES['excel']['tmp_name'];

$inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFile);
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
$reader->setReadFilter(new MyReadFilter());
$spreadsheet = $reader->load($inputFile);
$userInfo = $spreadsheet->getActiveSheet()->toArray();
foreach ($userInfo as $row) {

    $email = $row[0];
    $password = $row[1];
    $first_name = $row[2];
    $last_name = $row[3];
    $haveDiscap = $row[4];

    if ($row[0] != '') {
        $response   = $verifications::registerUser(
            $email,
            $password,
            $first_name,
            $last_name,
            $haveDiscap
        );
        echo $verifications->json_response(200, $response);
    }
}
