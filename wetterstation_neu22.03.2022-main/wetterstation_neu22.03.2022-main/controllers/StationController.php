<?php

require_once('Controller.php');
require_once('models/Measurement.php');
require_once('models/Station.php');

class StationController extends Controller
{
    /**
     * @param $route array, e.g. [station, view]
     */
    public function handleRequest($route)
    {
        $operation = sizeof($route) > 1 ? $route[1] : 'index';
        $id = isset($_GET['id']) ? $_GET['id'] : 0;

        if($operation == "index"){
            $this->actionIndex();
        } elseif ($operation == "create"){
            $this->actionCreate();
        } elseif ($operation == 'view') {
            $this->actionView($id);
        } elseif ($operation == 'update') {
            $this->actionUpdate($id);
        } elseif ($operation == 'delete') {
            $this->actionDelete($id);
        } else {
            Controller::showError("Page not found", "Page for operation " . $operation . " was not found!");
        }
    }

    /**
     * Controller-Methode zum Anzeigen der Index-Seite von Messstation und anzeigen aller Messstationen.
     */
    public function actionIndex()
    {
        $model = Station::getAll();
        $this->render("station/index", $model); //Anzeigen der index-Seite von Messstation
    }

    /**
     * Controller Methode zum Anzeigen einer Messstation in View-Seite von Messstation
     * @param $id
     */
    public function actionView($id)
    {
        $model = Station::get($id);

        if($model == null){
            Controller::showError("Page not found", "Data not found", 404);
        }else{
            $this->render("station/view", $model);
        }
    }

    /**
     * Controller-Methode zum Erstellen einer neuen Messstation.
     */
    public function actionCreate()
    {
        $model = new Station();

        if(!empty($_POST)){
            $model->setName($this->getDataOrNull("name"));  //setze Datenfeld name -> Prüfe ob $_POST['name'] gesetzt ist, wenn ja schreibe $_POST['name'] in Datenfeld, sonst null
            $model->setLocation($this->getDataOrNull("location"));
            $model->setAltitude($this->getDataOrNull("altitude"));

            if($model->save()){
                $this->redirect("station/index");   //Wenn Station-Objekt gespeichert -> weiterleiten auf index-Seite von Messstation
                return; //render()-Methode überspringen.
            }
        }
        $this->render("station/create", $model);
    }

    /**
     * Controller Methode zum Aktualisieren eines Messtation-Objekts aus der Datenbank.
     * @param $id
     */
    public function actionUpdate($id)
    {
        $model = Station::get($id);

        if($model == null){
            Controller::showError("Page not found", "Data not found", 404);
        }else{
            if(!empty($_POST)){
                $model->setName($this->getDataOrNull("name"));  //setze Datenfeld name -> Prüfe ob $_POST['name'] gesetzt ist, wenn ja schreibe $_POST['name'] in Datenfeld, sonst null
                $model->setLocation($this->getDataOrNull("location"));
                $model->setAltitude($this->getDataOrNull("altitude"));

                if($model->save()){
                    $this->redirect("station/index");  //Wenn Station-Objekt gespeichert -> weiterleiten auf index-Seite von Messstation
                    return; //render()-Methode überspringen.
                }
            }
            $this->render("station/update", $model);
        }
    }

    /**
     * Controller Methode zum Löschen einer Messstation aus der Datenbank.
     * @param $id
     */
    public function actionDelete($id)
    {
        $model = Station::get($id);

        if($model == null){
            Controller::showError("Page not found", "Data not found", 404);
        }else{
            if(!empty($_POST)){ //Prüfe, ob löschen-Formular abgeschickt wurde
                Station::delete($id);   //lösche Messtation-Objekt aus Datenbank
                $this->redirect("station/index");   //weiterleiten auf index-Seite von Messstation
                return;
            }
            $this->render("station/delete", $model);
        }
    }
}
