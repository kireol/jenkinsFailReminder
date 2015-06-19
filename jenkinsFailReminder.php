<?php

date_default_timezone_set('America/Detroit');
require 'vendor/autoload.php';
$PROJROOT = dirname(__FILE__) . "/";
require_once $PROJROOT . "src/Utils.php";
require_once $PROJROOT . "src/Hipchat.php";

$CONFIG = getConfig($PROJROOT);

$channels = $CONFIG['channels'];
$outputString = "";

foreach ($channels as $channel) {
    $hipchat = new Hipchat($channel);

    $jenkinsserver = $channel['jenkinsserver'];
    echo "Connecting to " . $jenkinsserver . "\n";
    $jenkins = new \JenkinsApi\Jenkins($jenkinsserver);

    $viewToWatch = $channel['jenkinsview'];
    echo "Loading jobs for view " . $viewToWatch . "\n";

    $view = $jenkins->getView($viewToWatch);

    $outputString = getBrokenBuildOutputText($view, $jenkins, $channel, $outputString);

    if (strlen($outputString) > 0) {
        $outputString = getGreeting() . "\n" . $outputString;
        $hipchat->postOutput($outputString);
    }
}

function getBrokenBuildOutputText($view, \JenkinsApi\Jenkins $jenkins, $channel, $outputString)
{
    foreach ($view->allJobs as $job) {
        if ($job->buildable == true) {
//            echo $job->color . " : " . $job->name . "\n";
            if (strcmp($job->color, "red") == 0) {
                $failedJob = $jenkins->getJob($job->name);
                if ($failedJob->builds[0]->timestamp / 1000 < strtotime($channel['alerttimestring'])) {
                    $outputString .= $job->name . " appears to have been broken since " .
                        getPrettyDateFromTimestamp($failedJob->builds[0]->timestamp / 1000) . " " .
                        $job->url . "\n";
                }
            }
        }
    }
    return $outputString;
}
