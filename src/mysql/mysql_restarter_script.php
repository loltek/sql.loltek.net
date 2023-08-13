<?php

declare(strict_types=1);
$mysqld_path = __DIR__ . "/bin/mysqld";
$my_cnf_path = __DIR__ . "/my.cnf";
$restart_log = __DIR__ . "/restart.log";
$max_restarts = 100;
$timezone = "Europe/Oslo";
$format = DateTime::RFC3339;
$singleInstanceLock = fopen(__FILE__, 'rb');
if (!flock($singleInstanceLock, LOCK_EX | LOCK_NB)) {
    die("Another instance is already running.\n");
}
for ($i = 0; $i < $max_restarts; ++$i) {
    $date = (new DateTime('now', timezone_open($timezone)))->format($format);
    $msg = "{$date}: Restarting mysqld. Attempt $i of $max_restarts\n";
    file_put_contents($restart_log, $msg, FILE_APPEND | LOCK_EX);
    echo $msg;
    $cmd = implode(" ", array(
        escapeshellarg($mysqld_path),
        "--defaults-file=" . escapeshellarg($my_cnf_path),
    ));
    echo "cmd:\n$cmd\n";
    // passthru() is easier but lose shell size information, proc_open doesn't.
    $empty1 = array();
    $empty2 = array();
    $proc = proc_open($cmd, $empty1, $empty2);
    $ret = proc_close($proc);
    $date = (new DateTime('now', timezone_open($timezone)))->format($format);
    $msg = "{$date}: mysqld exited with code $ret\n";
    echo $msg;
    file_put_contents($restart_log, $msg, FILE_APPEND | LOCK_EX);
}
$date = (new DateTime('now', timezone_open($timezone)))->format($format);
$msg = "{$date}: mysqld crashed too many times, exiting.. crash count: $i\n";
file_put_contents($restart_log, $msg, FILE_APPEND | LOCK_EX);
echo $msg;
flock($singleInstanceLock, LOCK_UN);
fclose($singleInstanceLock);
exit(1);
