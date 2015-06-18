<?php

date_default_timezone_set('America/Detroit');
require 'vendor/autoload.php';
$PROJROOT = dirname(__FILE__) . "/";
require_once $PROJROOT . "src/Utils.php";

$CONFIG = getConfig($PROJROOT);
$jenkinsserver = $CONFIG['jenkinsserver'];


echo "Connecting to " . $jenkinsserver . "\n";
$jenkins = new \JenkinsApi\Jenkins($jenkinsserver);

$viewToWatch = $CONFIG['jenkinsview'];
echo "Loading jobs for view " . $viewToWatch . "\n";

$view = $jenkins->getView($viewToWatch);

foreach ($view->allJobs as $job) {
    if($job->buildable == true){
        if(strcmp($job->color, "red") != 0){
            $failedJob = $jenkins->getJob($job->name);
            if($failedJob->builds[0]->timestamp/1000 < strtotime('-1 hour')){
                echo $job->name . " is over an hour old\n";
            }
//            echo json_encode($failedJob->builds[0]->timestamp)."\n";
//            echo strtotime('-1 hour')."\n";
//            exit;
        }
    }
}