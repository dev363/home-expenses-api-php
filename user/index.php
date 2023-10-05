<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include('../controllers/User.php');
$api = new User();

$ApiCall = $_GET["api"];
$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($requestMethod) {
    case 'GET': {
            switch ($ApiCall) {
                case 'getQueryByUserId':
                    $api->getQueryByUserId();
                    break;
                case 'DeleteQuery':
                    $api->DeleteQuery();
                    break;
                default:
                    $api->getAllQuery();
                    break;
            }
        }
    case 'POST': {
            switch ($ApiCall) {
                case 'InsertQuery':
                    $api->InsertQuery();
                    break;
                case 'UpdateQuery':
                    $api->UpdateQuery();
                    break;
                case 'login':
                    $api->Login();
                    break;
                default:
                    $api->getAllQuery();
                    break;
            }
        }
    default: {
            header("HTTP/1.0 405 Method Not Allowed");
            echo json_encode(array(
                'status' => 0,
                'error' => 'Method not Allowed'
            ));
            break;
        }
}
