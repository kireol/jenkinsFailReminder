<?php

function getConfigFilePath($PROJROOT)
{
    GLOBAL $argv;
    $projectRoot = $PROJROOT . "config.yml";
    $userSpecified = $argv[1];

    if (file_exists($projectRoot)) {
        echo "Loading config from " . $projectRoot . "\n";
        return $projectRoot;
    }
    if (file_exists($userSpecified)) {
        echo "Loading config from " . $userSpecified . "\n";
        return $userSpecified;
    } else {
        echo "Could not find config.yml\n";
        echo "Looked in:\n";
        echo "1) " . $projectRoot . "\n";
        echo "2) " . $userSpecified . "\n";
        exit(1);
    }
}

function getConfig($PROJROOT)
{
    return Spyc::YAMLLoad(getConfigFilePath($PROJROOT));
}