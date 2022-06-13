let allMeasurement = []; //globale Variable für alle Messwerte (einer Station) erstellen

let dateTimeValues = [];

let temperatureValues = [];

let rainValues = [];

//Funktion aufrufen, wenn DOM (Document-Object-Model / HTML-Gerüst) fertig geladen ist
$(document).ready(function (){
    console.log("DOM-Content loaded...");

    $('#btnSearch').click(function (){

        resetChart();
        let stationId = $('#selStation').val();  //erhalte station-Id vom select-html-Element
        console.log("Station-Id: "+stationId);

        deleteAllData();

        getAllMeasurementsByStation(stationId);
    });
});

//Ajax-Get-Methode zum Erhalten aller Messwerte einer Wetterstation über die API
function getAllMeasurementsByStation(stationId){
    $.get("api/station/"+stationId+"/measurement", function (data) {
        allMeasurement = data;

        displayAllMeasurements();
    });
}

function displayAllMeasurements(){
    let content = "";   //Variable content erstellen, in der alle Tabellen-Zeilen geschrieben werden

    //durchlaufe das gesamte allMeasurement-Array und füge jede Zeile der Variable content hinzu
    allMeasurement.forEach(function (measurement){
        content += "" +
            "<tr>" +
            "<td>"+measurement.time+"</td>" +
            "<td>"+measurement.temperature+"</td>" +
            "<td>"+measurement.rain+"</td>" +
            "<td>" +
            "<a class='btn btn-info' href='index.php?r=measurement/view&id="+measurement.station_id+"'><span class='glyphicon glyphicon-eye-open'></span></a>" +
            "<a class='btn btn-primary' href='index.php?r=measurement/update&id="+measurement.id+"'><span class='glyphicon glyphicon-pencil'></span></a>" +
            "<a class='btn btn-danger' href='index.php?r=measurement/delete&id="+measurement.id+"'><span class='glyphicon glyphicon-remove'></span></a>" +
            "</td>" +
            "</tr>";
    });

    $('#tblBodyContent').html(content);   //Schreibe den Tabellen-Inhalt in den Tabellen-Body tblBodyContent

    prepareChartData();
}

function deleteAllData(){
    allMeasurement = [];
    dateTimeValues = [];
    temperatureValues = [];
    rainValues = [];
}

function prepareChartData(){
    allMeasurement.forEach(function (measurement){
        dateTimeValues.push(measurement.time);

        temperatureValues.push(measurement.temperature);

        rainValues.push(measurement.rain);
    });

    buildChart();
}

//Funktion zum Konfigurieren des charts
function buildChart(){
    const labels = dateTimeValues;

    console.log("Datumswerte: "+dateTimeValues);

    console.log("Temperaturwerte:"+temperatureValues);

    console.log("Niederschlagswerte:"+rainValues);

    const data = {
        labels: labels,
        datasets: [{
            label: "Temperatur-Werte",
            backgroundColor: '#0000ff',
            borderColor: '#0000ff',
            data: temperatureValues
        },{
            label: "Niederschlags-Werte",
            backgroundColor: '#ff0000',
            borderColor: '#ff0000',
            data: rainValues

        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    };

    displayChart(config);
}

//Funktion zum Löschen des chart-canvas-html-Elements und anhängen eines neuen chart-chanvas-Elements -> zurücksetzen
function resetChart(){
    $('#chart').remove();
    $('#chartContainer').append("<canvas id='chart'></canvas>")
}

//Funktion zum Anzeigen des charts im canvas-Element
function displayChart(config){
    let chart = new Chart($('#chart'), config);
}