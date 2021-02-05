<?php

$source_file = '../src/readme.txt';

$target_file_pro  = 'tmp/changelog-pro.md';
$target_file_free = 'tmp/changelog-free.md';

$handle = fopen($source_file, 'r');

$changelog_pro  = [];
$changelog_free = [];

if ($handle) {
    $write_mode = null;

    while (($line = fgets($handle)) !== false) {

        if (strpos($line, 'Changelog') == true) {
            $write_mode = 'all';
            array_push($changelog_pro, $line);
            array_push($changelog_free, $line);
            continue;
        }

        // set up write mode
        if (strpos($line, 'fs_premium_only_begin')) {
            $write_mode = 'pro';
            continue;
        } else if(strpos($line, 'fs_free_only_begin')) {
            $write_mode = 'free';
            continue;
        }

        // never write if we're on an end directive
        if (strpos($line, 'fs_premium_only_end') || strpos($line, 'fs_free_only_end')) {
            $write_mode = 'all';
            continue;
        }

        if($write_mode === 'pro') {
            array_push($changelog_pro, $line);
        } else if($write_mode === 'free') {
            array_push($changelog_free, $line);
        } else if ($write_mode === 'all') {
            array_push($changelog_pro, $line);
            array_push($changelog_free, $line);
        }
    }

    fclose($handle);
} else {
    echo('error opening the file');
}

file_put_contents($target_file_pro, $changelog_pro);
file_put_contents($target_file_free, $changelog_free);
