<?php

$autoloadCandidates = [
    __DIR__ . '/../Packages/Libraries/autoload.php',
    __DIR__ . '/../../../Packages/Libraries/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
];

foreach ($autoloadCandidates as $autoloadCandidate) {
    if (file_exists($autoloadCandidate)) {
        require_once $autoloadCandidate;
        return;
    }
}

throw new RuntimeException('Could not find Composer autoload file for unit tests');
