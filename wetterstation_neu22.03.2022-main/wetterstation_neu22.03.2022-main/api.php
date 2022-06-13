<?php

require_once("controllers/RESTController.php");


$route = isset($_GET['r']) ? explode("/", trim($_GET['r'], "/")) : ['measurement'];
$controller = sizeof($route) > 0 ? $route[0] : "measurement";

if($controller == "measurement"){

    require_once("controllers/MeasurementRESTController.php");

    try {
        (new MeasurementRESTController())->handleRequest();
    } catch(Exception $e) {
        RESTController::responseHelper($e->getMessage(), $e->getCode());
    }

}elseif ($controller == "station"){

    require_once("controllers/StationRESTController.php");

    try {
        (new StationRESTController())->handleRequest();
    } catch(Exception $e) {
        RESTController::responseHelper($e->getMessage(), $e->getCode());
    }
}else{
    RESTController::responseHelper("REST-Controller: ".$controller." not found ", "404");
}


