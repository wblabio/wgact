<?php

$source_file = '../src/readme.txt';

$target_file_pro  = 'tmp/changelog-pro.md';
$target_file_free = 'tmp/changelog-free.md';

$handle = fopen($source_file, 'r');

$changelog_pro  = [];
$changelog_free = [];

if ($handle) {
    $write_pro  = false;
    $write_free = false;

    while (($line = fgets($handle)) !== false) {

        if (strpos($line, 'Changelog') == true) {
            $write_pro  = true;
            $write_free = true;

            array_push($changelog_pro, $line);
            array_push($changelog_free, $line);

            continue;
        }

        if ($write_pro == true && !(strpos($line, 'fs_premium_only_begin') || strpos($line, 'fs_premium_only_end'))) {
            array_push($changelog_pro, $line);
        }

        if (strpos($line, 'fs_premium_only_begin')) {
            $write_free = false;
            continue;
        } else if (strpos($line, 'fs_premium_only_end')) {
            $write_free = true;
            continue;
        }

        if ($write_free == true) {
            array_push($changelog_free, $line);
        }
    }

    fclose($handle);
} else {
    echo('error opening the file');
}

file_put_contents($target_file_pro, $changelog_pro);
file_put_contents($target_file_free, $changelog_free);

