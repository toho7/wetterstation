<?php

require_once("DatabaseObject.php");

class Station implements DatabaseObject, JsonSerializable
{
    // #### FIELDS ####

    private $id;
    private $name;
    private $altitude;
    private $location;

    private $errors = [];

    // #### CONSTRUCTOR ####

    /**
     * @param $id
     * @param $name
     * @param $altitude
     * @param $location
     */
    public function __construct(){
    }

    /**
     * Eine statische Methode zum erzeugen eines Station-Objektes
     * (aus der Datenbank)
     * @param $id
     * @param $name
     * @param $altitude
     * @param $location
     * @return Station
     */
    public static function createStation($id, $name, $altitude, $location){
        $station = new Station();
        $station->id = $id;
        $station->name = $name;
        $station->altitude = $altitude;
        $station->location = $location;

        return $station;
    }

    // #### CRUD ####

    /**
     * Creates a new object in the database
     * @return integer ID of the newly created object (lastInsertId)
     */
    public function create()
    {
        $db = Database::connect();
        $sql = "INSERT INTO station (name, altitude, location) values(?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->name, $this->altitude, $this->location));
        $lastId = $db->lastInsertId();
        Database::disconnect();
        return $lastId;
    }

    /**
     * Saves the object to the database
     */
    public function update()
    {
        $db = Database::connect();
        $sql = "UPDATE station set name = ?, altitude = ?, location = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($this->name, $this->altitude, $this->location, $this->id));
        Database::disconnect();
    }

    /**
     * Get an object from database
     * @param integer $id
     * @return object single object or null
     */
    public static function get($id)
    {
        $db = Database::connect();
        $sql = "SELECT * FROM station where id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($id));
        $stationDB = $stmt->fetch();
        Database::disconnect();

        //ORM-Mapping durchführen

        $station = Station::createStation($stationDB['id'], $stationDB['name'], $stationDB['altitude'], $stationDB['location']);

        return $stationDB !== false ? $station : null;
    }

    /**
     * Get an array of objects from database
     * @return array array of objects or empty array
     */
    public static function getAll()
    {
        $db = Database::connect();

        $sql = "SELECT * FROM station ORDER BY name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $allStationsDB = $stmt->fetchAll();
        Database::disconnect();

        $allStations = [];
        //ORM-Mapping durchführen
        foreach ($allStationsDB as $stationDB){
            $allStations[] = Station::createStation($stationDB['id'], $stationDB['name'], $stationDB['altitude'], $stationDB['location']);
        }
        return $allStationsDB !== false ? $allStations : null;

    }

    /**
     * Deletes the object from the database
     * @param integer $id
     * @return bool true on success
     */
    public static function delete($id)
    {
        try {
            $db = Database::connect();
            $sql = "DELETE FROM station WHERE id = ?";
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
        return $this->validateName()
            & $this->validateAltitude()
            & $this->validateLocation();
    }

    private function validateName()
    {
        if ($this->name == '') {
            $this->errors['name'] = "Name darf nicht leer sein";
            return false;
        } else if (strlen($this->name) > 64) {
            $this->errors['name'] = "Name zu lang";
            return false;
        } else {
            unset($this->errors['name']);
            return true;
        }
    }

    private function validateAltitude()
    {
        if (!is_numeric($this->altitude) || $this->altitude < 0 || $this->altitude > 4000) {
            $this->errors['altitude'] = "Höhe ungültig";
            return false;
        } else {
            unset($this->errors['altitude']);
            return true;
        }
    }

    private function validateLocation()
    {
        if ($this->location == '') {
            $this->errors['location'] = "Ort darf nicht leer sein";
            return false;
        } else if (strlen($this->location) > 255) {
            $this->errors['location'] = "Ort zu lang";
            return false;
        } else {
            unset($this->errors['location']);
            return true;
        }
    }

    // #### JSON SERIALIZE ####

    /**
     * Hilfsmethode zum Serialisieren für JSON
     * @return array
     */
    public function jsonSerialize()
    {
        //return get_object_vars($this);
        return [
            "id" => intval($this->id),
            "name" => $this->name,
            "altitude" => intval($this->altitude),
            "location" => $this->location,
        ];
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAltitude()
    {
        return $this->altitude;
    }

    /**
     * @param mixed $altitude
     */
    public function setAltitude($altitude)
    {
        $this->altitude = $altitude;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param mixed $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
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
    public function getErrors($field)
    {
        return $this->errors[$field];
    }
}
