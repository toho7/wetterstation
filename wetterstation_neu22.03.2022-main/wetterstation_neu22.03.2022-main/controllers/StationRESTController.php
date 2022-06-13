<?php

require_once('RESTController.php');
require_once('models/Station.php');
require_once('models/Measurement.php');

class StationRESTController extends RESTController
{
    public function handleRequest()
    {
        switch ($this->method) {
            case 'GET':
                $this->handleGETRequest();
                break;
            case 'POST':
                $this->handlePOSTRequest();
                break;
            case 'PUT':
                $this->handlePUTRequest();
                break;
            case 'DELETE':
                $this->handleDELETERequest();
                break;
            default:
                $this->response('Method Not Allowed', 405);
                break;
        }
    }

    /**
     * get single/all stations
     * single station: GET api.php?r=/station/25 -> args[0] = 25
     * all stations: GET api.php?r=station
     * all measurements of single station: GET api.php?r=/station/2/measurement -> arg[0] = 2, args[1] = measurement
     */
    private function handleGETRequest()
    {
        if ($this->verb == null && sizeof($this->args) == 1) {
            $model = Station::get($this->args[0]);  // erhalte einzelne Messstation
            $this->response($model);
        } else if ($this->verb == null && empty($this->args)) {
            $model = Station::getAll();             // erhalte alle Messtationen
            $this->response($model);
        } else if($this->verb == null && sizeof($this->args) == 2){ //erhalte alle Messwerte einer Messstation:  $args->Array Länge 2: args[0] = "1" (Messstations-Id), args[1] = "measurement"
            $model = Measurement::getAllByStation($this->args[0]);
            $this->response($model);
        }
        else {
            $this->response("Bad request", 400);
        }
    }

    /**
     * create station: POST api.php?r=station
     */
    private function handlePOSTRequest()
    {
        $model = new Station();
        $model->setName($this->getDataOrNull("name"));  //setze Datenfeld name -> Prüfe ob $_POST['name'] gesetzt ist, wenn ja schreibe $_POST['name'] in Datenfeld, sonst null
        $model->setLocation($this->getDataOrNull("location"));
        $model->setAltitude($this->getDataOrNull("altitude"));

        if ($model->save()) {
            $this->response("OK", 201);
        } else {
            $this->response($model->getErrors(), 400);
        }
    }

    /**
     * update station: PUT api.php?r=station/25 -> args[0] = 25
     */
    private function handlePUTRequest()
    {
        if ($this->verb == null && sizeof($this->args) == 1) {

            $model = Station::get($this->args[0]);
            $model->setName($this->getDataOrNull("name"));  //setze Datenfeld name -> Prüfe ob $_POST['name'] gesetzt ist, wenn ja schreibe $_POST['name'] in Datenfeld, sonst null
            $model->setLocation($this->getDataOrNull("location"));
            $model->setAltitude($this->getDataOrNull("altitude"));

            if ($model->save()) {
                $this->response("OK");
            } else {
                $this->response($model->getErrors(), 400);
            }

        } else {
            $this->response("Not Found", 404);
        }
    }

    /**
     * delete station: DELETE api.php?r=station/25 -> args[0] = 25
     */
    private function handleDELETERequest()
    {
        if ($this->verb == null && sizeof($this->args) == 1) {
            Station::delete($this->args[0]);
            $this->response("OK", 200);
        } else {
            $this->response("Not Found", 404);
        }
    }
}
