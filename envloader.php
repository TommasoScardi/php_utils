<?php
$envFileS = fopen("../.env", "r");
while (!feof($envFileS)) {
    $line = trim(fgets($envFileS));
    if (strlen($line) <= 0) {
        continue;
    }
    //se la linea inizia con # si tratta di un commento
    if (strpos($line, "#") === 0) {
        continue;
    }
    $envData = explode("=", $line);
    if ($envData !== false && count($envData) == 2) {
        if (is_numeric($envData[1])) {
            $_ENV[$envData[0]] = intval($envData[1]);
        }
        else {
            $_ENV[$envData[0]] = $envData[1];
        }
    }
}
fclose($envFileS);
unset($envFileS);