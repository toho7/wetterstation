<?php

require_once("DatabaseObject.php");
require_once("Station.php");

class MeasurementWithStationDTO implements JsonSerializable
{
    // #### FIELDS ####

    private $id;
    private $time;
    private $temperature;
    private $rain;
    private $station_id;
    private $station;   //Datenfeld für Messstations-Objekt

    // #### CONSTRUCTOR ####

    /**
     * @param $id
     * @param $time
     * @param $temperature
     * @param $rain
     * @param $station_id
     * @param $station
     */
    public function __construct($id, $time, $temperature, $rain, $station_id, $station)
    {
        $this->id = $id;
        $this->time = $time;
        $this->temperature = $temperature;
        $this->rain = $rain;
        $this->station_id = $station_id;
        $this->station = $station;
    }

    /**
     * Hilfsmethode zum Serialisieren für JSON
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [
            "id" => intval($this->id),
            "time" => $this->time,
            "temperature" => round(doubleval($this->temperature), 2),
            "rain" => round(doubleval($this->rain), 2),
        ];

        if ($this->station_id != null && is_numeric($this->station_id)) {
            $data['station_id'] = intval($this->station_id);      // include id
        }

        if ($this->station != null && is_object($this->station)) {
            $data['station'] = $this->station;      // include object
        }

        return $data;
    }
}

