<?php
function saveToLogFile(string $type, string $message, $ctx=null, $stackTrace = null, string $customFileName = null) {
// log_date.txt
    $date = date("Y-m-d");
    $fullDate = date("Y/m/d G:i:s");

    if ($customFileName !== null) {
        $fileName = sprintf("%s_%s.txt", $customFileName, $date);
    }
    else {
        $fileName = sprintf("log_%s.txt", $date);
    }
    $longFilename = "../logs/$fileName";

    $logData = sprintf("[%s] [%s] %s", $fullDate, $type, $message);

    if ($ctx !== null) {
        if (gettext($ctx) === "array") {
            $jsonData = json_encode($ctx, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
            if ($jsonData !== null && json_last_error() === JSON_ERROR_NONE) {
                $logData .= "\n---LOG CONTEXT---\n" . $jsonData;
            }
        }
        else if (gettype($ctx) === "string") {
            $logData .= " " . trim($ctx);
        }
    }
    
    
    if ($stackTrace !== null) {
        if ($stackTrace === true) {
            $stackTrace = array_slice(debug_backtrace(), 1); //removes the first element that is this file trace
            $logData .= "\n---LOG STACKTRACE---\n" . json_encode($stackTrace, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        }
        else if (gettype($stackTrace) === "string") {
            $logData .= "\n---LOG STACKTRACE---\n" . $stackTrace;
        }
        else if (gettype($stackTrace) === "array") {
            $logData .= "\n---LOG STACKTRACE---\n" . json_encode($stackTrace, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT);
        }
    }
    $logData .= "\n\n";

    if (!file_exists($longFilename)) {
        file_put_contents($longFilename, $logData);
    }
    else {
        file_put_contents($longFilename, $logData, FILE_APPEND);
    }
}

// log: error, alert, debug, info
function log_error($message, $ctx=null, $stackTrace=true) {
    saveToLogFile("ERROR", $message, $ctx, $stackTrace);
}

function log_exc($message, $ctx=null, $stackTrace=true) {
    saveToLogFile("EXCEPTION ERROR", $message, $ctx, $stackTrace);
}

function log_alert($message, $ctx=null, $stackTrace=true) {
    saveToLogFile("ALERT", $message, $ctx, $stackTrace);
}

function log_info($message, $ctx=null, $stackTrace=true) {
    saveToLogFile("INFO", $message, $ctx, $stackTrace);
}

function log_debug($message, $ctx=null, $stackTrace=true) {
    saveToLogFile("DEBUG", $message, $ctx, $stackTrace);
}

function log_custom($fileName, $message, $type, $ctx=null, $stackTrace=null) {
    saveToLogFile($type, $message, $ctx, $stackTrace, $fileName);
}