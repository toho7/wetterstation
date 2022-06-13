<div class="row">
    <div class="col-md-2">
        <div class="form-group required <?= $measurement->hasError('time') ? 'has-error' : ''; ?>">
            <?php
            $dateTime = $measurement->getTime();
            $dateTimeFormatted = date("Y-m-d\TH:i:s", strtotime($dateTime));    //Messzeitpunkt-String in html-datetime-local-Format formatieren
            ?>
            <label class="control-label">Zeitpunkt *</label>
            <input type="datetime-local" class="form-control" name="time" value=<?=$dateTimeFormatted?>>

            <?php if ($measurement->hasError('time')): ?>
                <div class="help-block"><?= $measurement->getError('time') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-2">
        <div class="form-group required <?= $measurement->hasError('temperature') ? 'has-error' : ''; ?>">
            <label class="control-label">Temperatur [°C] *</label>
            <input type="text" class="form-control" name="temperature" value="<?= $measurement->getTemperature() ?>">

            <?php if ($measurement->hasError('temperature')): ?>
                <div class="help-block"><?= $measurement->getError('temperature') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-2">
        <div class="form-group required <?= $measurement->hasError('rain') ? 'has-error' : ''; ?>">
            <label class="control-label">Regenmenge [ml] *</label>
            <input type="text" class="form-control" name="rain" value="<?= $measurement->getRain() ?>">

            <?php if ($measurement->hasError('rain')): ?>
                <div class="help-block"><?= $measurement->getError('rain') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-1">
        <div class="form-group required">
            <label class="control-label">Station*</label>
            <select class="form-select" name="station_id" aria-label="Default select example">
                <?php
                foreach ($stations as $station){
                    echo "<option value='".$station->getId()."'>".$station->getName()."</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
