<?php
/**
 * Infravision Dummy Plugin Example
 * Usage: php check_dummy.php <ip>
 * 
 * Returns:
 * 0: OK
 * 1: Warning
 * 2: Critical
 * 3: Unknown
 */

if ($argc < 2) {
    echo "Usage: php check_dummy.php <ip>\n";
    exit(3);
}

$ip = $argv[1];

// Randomly return a status for demonstration purposes
$status = rand(0, 3);

if ($status == 0) {
    echo "OK: O dispositivo $ip esta funcionando corretamente.";
    exit(0);
} elseif ($status == 1) {
    echo "WARNING: O dispositivo $ip esta com carga alta.";
    exit(1);
} elseif ($status == 2) {
    echo "CRITICAL: O dispositivo $ip parou de responder.";
    exit(2);
} else {
    echo "UNKNOWN: Nao foi possivel determinar o estado do dispositivo $ip.";
    exit(3);
}
