<?php

/**
 * This file acts a cron replacement. Pass the command you want to run every minute as argument.
 */

// remove time limit
set_time_limit(0);

// get the command to run
$command = $argv[1];

// run the command every minute
runCommandEveryMinute($command);

/**
 * Run the command every minute.
 *
 * @param string $command
 * @return void
 */
function runCommandEveryMinute(string $command): void
{

    // loop forever
    while (true) {

        // get the current time
        $timeStart = microtime(true);

        // run the command in background
        exec("$command &");

        // get the time after the command was started
        $timeStop = microtime(true);

        // calculate the execution duration
        $executionDuration = $timeStop - $timeStart;

        // calculate the sleep time
        $sleepTimeUS = (60 - $executionDuration) * 1000 * 1000;

        // sleep for the remaining time
        usleep((int)$sleepTimeUS);
    }

}