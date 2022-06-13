<?php

require_once("DatabaseObject.php");
require_once("Station.php");
require_once("MeasurementWithStationDTO.php");

class Measurement implements DatabaseObject, JsonSerializable
{
    // #### FIELDS ####

    private $id;
    private $time;
    private $temperature;
    private $rain;
    private $station_id;

    private $errors = [];

    // #### CONSTRUCTOR ####

    /**
     * @param $id
     * @param $time
     * @param $temperature
     * @param $rain
     * @param $station_id
     */
    public function __construct(){
    }

    /**
     * Eine statische Methode zum erzeugen eines Messwert-Objektes
     * (aus der Datenbank)
     * @param $id
     * @param $time
     * @param $temperature
     * @param $rain
     * @param $station_id
     * @return Measurement
     */
    public static function createMeasurement($id, $time, $temperature, $rain, $station_id){
        $measurement = new Measurement();
        $measurement->id = $id;
        $measurement->time = $time;
        $measurement->temperature = $temperature;
        $measurement->rain = $rain;
        $measurement->station_id = $station_id;

        return $measurement;
    }

    // #### CRUD ####

    /**
     * Creates a new object in the database
     * @return integer ID of the newly created object (lastInsertId)
     */
    public function create()
    {
        $db = Database::connect();
        $sql = "INSERT INTO measurement (time, temperature, rain, station_id) values(?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->time, $this->temperature, $this->rain, $this->station_id));
        $lastId = $db->lastInsertId();
        Database::disconnect();
        return $lastId;
    }

    /**
     * Saves the object to the database
     */
    public function update()
    {
        //TODO: kopiert und angepasst von Station, muss noch kontrolliert werden
        $db = Database::connect();
        $sql = "UPDATE measurement set time = ?, temperature = ?, rain = ?, station_id = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->time, $this->temperature, $this->rain, $this->station_id, $this->id));
        Database::disconnect();
    }

    /**
     * Erhalte einen Messwert.
     * @param integer $id
     * @return object single object or null
     */
    public static function get($id)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM measurement WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($id));
        $measurementDB = $stmt->fetch();
        Database::disconnect();

        //ORM-Mapping durchführen
        $measurement = Measurement::createMeasurement($measurementDB['id'], $measurementDB['time'], $measurementDB['temperature'], $measurementDB['rain'], $measurementDB['station_id']);

        return $measurementDB !== false ? $measurement : null;
    }

    /**
     * Erhalte einen Messwert mit dazugehöriger Messstation als Objekt im Datenfeld.
     * @param integer $id
     * @return object single object or null
     */
    public static function getWithStation($id)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM v_measurementWithStation WHERE m_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($id));
        $measurementWithStationDB = $stmt->fetch();
        Database::disconnect();

        //ORM-Mapping auf MeasurementWithStationDTO durchführen
        $station = Station::createStation($measurementWithStationDB['s_id'], $measurementWithStationDB['name'], $measurementWithStationDB['altitude'], $measurementWithStationDB['location']);
        $measurementWithStationDTO = new MeasurementWithStationDTO($measurementWithStationDB['m_id'], $measurementWithStationDB['time'], $measurementWithStationDB['temperature'], $measurementWithStationDB['rain'], $measurementWithStationDB['station_id'], $station);

        return $measurementWithStationDB !== false ? $measurementWithStationDTO : null;
    }

    /**
     * Erhalte alle Messwerte, sortiert nach Datum (neueste Messwerte zuerst), aus der Datenbank
     * @return array Messwerte
     */
    public static function getAll() {
        $db = Database::connect();
        $sql = "SELECT * FROM measurement ORDER BY time DESC";  //nach neuesten Datum zuerst sortieren
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $allMeasurementsDB = $stmt->fetchAll();
        Database::disconnect();

        $allMeasurements = [];

        //ORM-Mapping für alle Messwerte durchführen
        foreach ($allMeasurementsDB as $measurementDB){
            $allMeasurements[] = Measurement::createMeasurement($measurementDB['id'], $measurementDB['time'], $measurementDB['temperature'], $measurementDB['rain'], $measurementDB['station_id']);
        }
        return $allMeasurements;
    }

    /**
     * Erhalte alle Messwerte einer Messstation, sortiert nach Datum absteigend. (neueste Werte zuerst) Es werden nur 10 Werte geladen, damit der Graph mittels chart.js
     * noch vernünftig dargestellt werden kann.
     * @param int $station_id
     * @return array array of objects or empty array
     */
    public static function getAllByStation($stationId)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM measurement WHERE station_id = ? ORDER BY time LIMIT 10";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($stationId));

        $allMeasurementsByStationDB = $stmt->fetchAll();
        Database::disconnect();

        $allMeasurementsByStation = [];

        //ORM-Mapping für alle Messwerte durchführen
        foreach ($allMeasurementsByStationDB as $measurementByStationDB){
            $allMeasurementsByStation[] = Measurement::createMeasurement($measurementByStationDB['id'], $measurementByStationDB['time'], $measurementByStationDB['temperature'], $measurementByStationDB['rain'], $measurementByStationDB['station_id']);
        }

        return $allMeasurementsByStation;
    }

    /**
     * Deletes the object from the database
     * @param integer $id
     */
    public static function delete($id)
    {
        try {
            $db = Database::connect();
            $sql = "DELETE FROM measurement WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute(array($id));
            Database::disconnect();
            return true;    // success
        }catch (Exception $e) {
            Database::disconnect();
            return false;   // error
        }
    }

    /**
     * create or update an object
     * @return boolean true on success
     */
    public function save()
    {
        if ($this->validate()) {
            if ($this->id != null && $this->id > 0) {
                $this->update();
            } else {
                $this->id = $this->create();
            }
            return true;
        }
        return false;
    }

    // #### VALIDATION ####

    public function validate()
    {
        return $this->validateTime()
            & $this->validateTemperature()
            & $this->validateRain()
            & $this->validateStationId();
    }

    private function validateTime()
    {
        try {
            if ($this->time == '') {
                $this->errors['time'] = "Messzeitpunkt darf nicht leer sein";
                return false;
            } else if (new DateTime($this->time) > new DateTime()) {
                $this->errors['time'] = "Messzeitpunkt " . htmlspecialchars($this->time) . " darf nicht in der Zukunft liegen";
                return false;
            } else {
                unset($this->errors['time']);
                return true;
            }
        }catch (Exception $e) {
            $this->errors['time'] = "Messzeitpunkt " . htmlspecialchars($this->time) . " ungültig";
            return false;
        }
    }

    private function validateTemperature()
    {
        if ((!is_numeric($this->temperature) && !is_double($this->temperature)) || $this->temperature < -50 || $this->temperature > 150) {
            $this->errors['temperature'] = "Temperatur ungueltig";
            return false;
        } else {
            unset($this->errors['temperature']);
            return true;
        }
    }

    private function validateRain()
    {
        if ((!is_numeric($this->rain) && !is_double($this->rain))  || $this->rain < 0 || $this->rain > 10000) {
            $this->errors['rain'] = "Regenmenge ungueltig";
            return false;
        } else {
            unset($this->errors['rain']);
            return true;
        }
    }

    private function validateStationId()
    {
        if (!is_numeric($this->station_id) && $this->station_id <= 0) {
            $this->errors['station_id'] = "StationID ungueltig";
            return false;
        } else {
            unset($this->errors['station_id']);
            return true;
        }
    }

    // #### JSON SERIALIZE ####

    /**
     * Hilfsmethode zum Serialisieren für JSON
     * @return array
     * @throws Exception
     */
    public function jsonSerialize()
    {
        $dateObj = new DateTime($this->time);

        $data = [
            "id" => intval($this->id),
            "time" => $dateObj->format("d.m.Y H:i:s"),
            "temperature" => round(doubleval($this->temperature), 2),
            "rain" => round(doubleval($this->rain), 2),
        ];

        if ($this->station_id != null && is_numeric($this->station_id)) {
            $data['station_id'] = intval($this->station_id);      // include id
        }

        return $data;
    }

    // #### GETTER UND SETTERS ####

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    public function getTimeFormatted(){
        $dateObj = new DateTime($this->time);
        return $dateObj->format("d.m.Y H:i:s");
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * @return mixed
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    public function getTemperatureFormatted()
    {
        //return number_format((float)$this->temperature,2, ",", "");
        return round(doubleval($this->temperature), 2);
    }

    /**
     * @param mixed $temperature
     */
    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;
    }

    /**
     * @return mixed
     */
    public function getRain()
    {
        return $this->rain;
    }

    public function getRainFormatted()
    {
        //return number_format((float)$this->rain,2, ",", "");
        return round(doubleval($this->rain), 2);
    }

    /**
     * @param mixed $rain
     */
    public function setRain($rain)
    {
        $this->rain = $rain;
    }

    /**
     * @return mixed
     */
    public function getStationId()
    {
        return $this->station_id;
    }

    /**
     * @param mixed $station_id
     */
    public function setStationId($station_id)
    {
        $this->station_id = $station_id;
    }

    /**
     * @return mixed
     */
    public function getStation()
    {
        return $this->station;
    }

    /**
     * @param mixed $station
     */
    public function setStation($station)
    {
        $this->station = $station;
    }

    /**
     * @return boolean
     */
    public function hasError($field)
    {
        return !empty($this->errors[$field]);
    }

    /**
     * @return array
     */
    public function getError($field)
    {
        return $this->errors[$field];
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
