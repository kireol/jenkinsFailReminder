<?php

date_default_timezone_set('America/Detroit');
require 'vendor/autoload.php';
$PROJROOT = dirname(__FILE__) . "/";
require_once $PROJROOT . "src/Utils.php";

$CONFIG = getConfig($PROJROOT);


$channels = $CONFIG['channels'];

foreach($channels as $channel){
    $jenkinsserver = $channel['jenkinsserver'];
    echo "Connecting to " . $jenkinsserver . "\n";
    $jenkins = new \JenkinsApi\Jenkins($jenkinsserver);

    $viewToWatch = $channel['jenkinsview'];
    echo "Loading jobs for view " . $viewToWatch . "\n";

    $view = $jenkins->getView($viewToWatch);

    foreach ($view->allJobs as $job) {
        if($job->buildable == true){
            if(strcmp($job->color, "red") == 0){
                echo "Found failed job: " . $job->name . "\n";
                $failedJob = $jenkins->getJob($job->name);
                if($failedJob->builds[0]->timestamp/1000 < strtotime($channel['alerttimestring'])){
                    echo $job->name . " is over an hour old\n";
                }
            }
        }
    }
}