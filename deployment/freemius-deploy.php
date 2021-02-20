<?php

/**
 * https://github.com/CodeAtCode/freemius-suite
 *
 * example: php freemius-deploy.php zip-file.zip sandbox release_mode version
 *
 */

require_once('freemius-php-sdk-master/freemius/FreemiusBase.php');
require_once('freemius-php-sdk-master/freemius/Freemius.php');

$options      = include 'freemius-deploy-config.php';
$file_name    = $argv[1];
$sandbox      = $argv[2] === 'false' ? false : true;
$release_mode = $argv[3] ?: 'pending'; // can be 'pending', 'beta' and 'released'
$version      = $argv[4];

// check if we have a valid version number, otherwise stop
if( ! version_compare( $version, '0.0.1', '>=' ) >= 0 ) {
    echo 'Invalid version number' . PHP_EOL;
    die();
}

echo PHP_EOL . 'Deployment to Freemius: started' . PHP_EOL;

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
//        echo(print_r($deploy, true ));

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

        echo 'Deployment to Freemius: successful' . PHP_EOL;

//        echo 'Setting released mode to: ' . $release_mode . PHP_EOL;
//        $is_released = $api->Api('plugins/' . $options['FS__PLUGIN_ID'] . '/tags/' . $deploy->id . '.json', 'PUT', [
//            'release_mode' => $release_mode
//        ], []);
//
//        echo 'Set as released on Freemius' . PHP_EOL;
    }

    /**
     * Generate URLs to download the pro and free zip versions
     *
     * possible URL parameters
     * is_premium=false : (or true) for downloading the free or pro version
     * beautify=true : function unknown
     * XDEBUG_SESSION_START=1 : function unknown
     */

    // Generate url to download the pro zip
    echo PHP_EOL . 'Download the pro version from Freemius: start'. PHP_EOL;
    $zip_pro = $api->GetSignedUrl('plugins/' . $options['FS__PLUGIN_ID'] . '/tags/' . $deploy->id . '.zip' . '?is_premium=true');
    $new_zip_pro_name = 'freemius-plugin-pro/' . 'woopt-pixel-manager-pro.' . $version . '.zip';
    file_put_contents($new_zip_pro_name, file_get_contents($zip_pro));
    echo 'Download the pro version from Freemius: success' . PHP_EOL;

    // Generate url to download the free zip
    echo PHP_EOL . 'Download the free version from Freemius: start'. PHP_EOL;
    $zip_free = $api->GetSignedUrl('plugins/' . $options['FS__PLUGIN_ID'] . '/tags/' . $deploy->id . '.zip' . '?is_premium=false');
    $new_zip_free_name = 'freemius-plugin-free/' . 'woocommerce-google-adwords-conversion-tracking-tag' . '-free.' . $version . '.zip';
    file_put_contents($new_zip_free_name, file_get_contents($zip_free));
    echo 'Download the free version from Freemius: success' . PHP_EOL;

} catch (Exception $e) {
    echo 'Whoops! The server has problems' . PHP_EOL;
//    echo $e . PHP_EOL;
}