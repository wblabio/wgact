<?php

/**
 * https://github.com/CodeAtCode/freemius-suite
 *
 * example: php freemius-deploy.php zip-file.zip version sandbox release_mode
 *
 */

require_once('freemius-php-sdk-master/freemius/FreemiusBase.php');
require_once('freemius-php-sdk-master/freemius/Freemius.php');

$options      = include 'freemius-deploy-config.php';
$file_name    = $argv[1];
$version      = $argv[2];
$sandbox      = $argv[3] === 'false' ? false : true;
$release_mode = $argv[4] ?: 'pending'; // can be 'pending', 'beta' and 'released'

echo PHP_EOL . 'Deployment to Freemius in progress' . PHP_EOL;

try {
    $api = new Freemius_Api(
        $options['FS__API_SCOPE'],
        $options['FS__API_DEV_ID'],
        $options['FS__API_PUBLIC_KEY'],
        $options['FS__API_SECRET_KEY'],
        $sandbox
    );

//    if (!is_object($api)) {
//        echo 'couldn\'t get API';
//        die();
//    }

    // Get all products.
//    $result = $api->Api('/plugins.json');
//    echo(print_r($result, true ));

    $deploy = $api->Api('plugins/' . $options['FS__PLUGIN_ID'] . '/tags.json');
//    echo $deploy->tags[0]->version . PHP_EOL;

//    echo ('absolute file path: ' . realpath($file_name) . PHP_EOL);

//    echo(print_r($deploy, true));

    if ($deploy->tags[0]->version === $version) {
        $deploy = $deploy->tags[0];
        echo 'Package has already been deployed to Freemius' . PHP_EOL;
    } else {
        // Upload the zip
        $deploy = $api->Api('plugins/' . $options['FS__PLUGIN_ID'] . '/tags.json', 'POST', [
            'add_contributor' => false
        ], [
            'file' => realpath($file_name)
        ]);

        if (!property_exists($deploy, 'id')) {
            print_r($deploy);
            die();
        }

        echo 'Deployment to Freemius has been successful' . PHP_EOL;

//        echo 'Setting released mode to: ' . $release_mode . PHP_EOL;
//        $is_released = $api->Api('plugins/' . $options['FS__PLUGIN_ID'] . '/tags/' . $deploy->id . '.json', 'PUT', [
//            'release_mode' => $release_mode
//        ], []);
//
//        echo 'Set as released on Freemius' . PHP_EOL;
    }

    echo PHP_EOL . 'Download the free version from Freemius: start'. PHP_EOL;

    // Generate url to download the zip
    $zip = $api->GetSignedUrl('plugins/' . $options['FS__PLUGIN_ID'] . '/tags/' . $deploy->id . '.zip');

//    $path         = pathinfo($file_name);
//    echo (print_r($path, true));
//    $new_zip_name = $path['dirname'] . '/' . basename($file_name, '.zip');
//    $new_zip_name .= '.free.zip';

    $new_zip_name = 'freemius-plugin-free/' . 'woocommerce-google-adwords-conversion-tracking-tag' . '-free.' . $version . '.zip';

    file_put_contents($new_zip_name, file_get_contents($zip));

    echo 'Download the free version from Freemius: success' . PHP_EOL;
} catch (Exception $e) {
    echo 'Whoops! The server has problems' . PHP_EOL;
//    echo $e . PHP_EOL;
}