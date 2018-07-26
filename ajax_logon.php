<?php
define('AJAX', true);

require_once('header.php');

require_once('lib/auth.php');

header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");
//print_r($_REQUEST);

$User = libAuth::auth();

if ($User->type['User']){
    $result['status'] = "AUTH_SUCCESSFUL";
    echo json_encode($result);
}
