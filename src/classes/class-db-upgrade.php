<?php
/**
 * DB upgrade function
 */

namespace WGACT\Classes;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Db_Upgrade
{

    public function __construct()
    {
        // TODO remove by end of 2021

        if (!get_option('wooptpm_delete_old_partial_refund_hit_keys')) {
            error_log('run delete key');
            $this->delete_old_partial_refund_hit_keys();
        }
    }

    protected function delete_old_partial_refund_hit_keys()
    {
        global $wpdb;

        $sql = "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%wooptpm_google_analytics_4_mp_partial_refund_hit_%'";

        $wpdb->get_results($sql);

        $sql = "DELETE FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '%wooptpm_google_analytics_ua_mp_partial_refund_hit_%'";

        $wpdb->get_results($sql);

        add_option('wooptpm_delete_old_partial_refund_hit_keys', true);
    }

    protected $options_backup_name = 'wgact_options_backup';

    public function run_options_db_upgrade()
    {

        $mysql_db_version = $this->get_mysql_db_version();

        // determine version and run version specific upgrade function
        // check if options db version zero by looking if the old entries are still there.
        if ('0' === $mysql_db_version) {
            $this->up_from_zero_to_1();
        }

        if (version_compare(2, $mysql_db_version, '>')) {
            $this->up_from_1_to_2();
        }

        if (version_compare(3, $mysql_db_version, '>')) {
            $this->up_from_2_to_3();
        }
    }

    protected function up_from_2_to_3()
    {
        $options_old = get_option(WOOPTPM_DB_OPTIONS_NAME);

        $this->backup_options($options_old, '2');

        $options_new = $options_old;

        $options_new['shop']['order_total_logic'] = $options_old['gads']['order_total_logic'];

        $options_new['google']['ads']  = $options_old['gads'];
        $options_new['google']['gtag'] = $options_old['gtag'];


        unset($options_new['google']['ads']['order_total_logic']);
        unset($options_new['gads']);
        unset($options_new['gtag']);
        unset($options_new['google']['ads']['google_business_vertical']);

        $options_new['google']['ads']['google_business_vertical'] = 0;

        $options_new['db_version'] = '3';

        update_option(WOOPTPM_DB_OPTIONS_NAME, $options_new);
    }

    protected function up_from_1_to_2()
    {
        $options_old = get_option(WOOPTPM_DB_OPTIONS_NAME);

        $this->backup_options($options_old, '1');

        $options_new = [
            'gads'       => [
                'conversion_id'      => $options_old['conversion_id'],
                'conversion_label'   => $options_old['conversion_label'],
                'order_total_logic'  => $options_old['order_total_logic'],
                'add_cart_data'      => $options_old['add_cart_data'],
                'aw_merchant_id'     => $options_old['aw_merchant_id'],
                'product_identifier' => $options_old['product_identifier'],
            ],
            'gtag'       => [
                'deactivation' => $options_old['gtag_deactivation'],
            ],
            'db_version' => '2',
        ];

        update_option(WOOPTPM_DB_OPTIONS_NAME, $options_new);
    }

    protected function get_mysql_db_version(): string
    {

        $options = get_option(WOOPTPM_DB_OPTIONS_NAME);

//		error_log(print_r($options,true));

        if ((get_option('wgact_plugin_options_1')) || (get_option('wgact_plugin_options_2'))) {
            return '0';
        } elseif (array_key_exists('conversion_id', $options)) {
            return '1';
        } else {
            return $options['db_version'];
        }
    }

    public function up_from_zero_to_1()
    {

        $option_name_old_1 = 'wgact_plugin_options_1';
        $option_name_old_2 = 'wgact_plugin_options_2';

        // db version place options into new array
        $options = [
            'conversion_id'    => $this->get_option_value_v1($option_name_old_1),
            'conversion_label' => $this->get_option_value_v1($option_name_old_2),
        ];

        // store new option array into the options table
        update_option(WOOPTPM_DB_OPTIONS_NAME, $options);

        // delete old options
        // only on single site
        // we will run the multisite deletion only during uninstall
        delete_option($option_name_old_1);
        delete_option($option_name_old_2);
    }

    protected function get_option_value_v1(string $option_name): string
    {
        if (!get_option($option_name)) {
            $option_value = "";
        } else {
            $option       = get_option($option_name);
            $option_value = $option['text_string'];
        }

        return $option_value;
    }

    protected function backup_options($options, $version)
    {

        $options_backup = get_option($this->options_backup_name);

        $options_backup[$version] = $options;

        update_option($this->options_backup_name, $options_backup);
    }
}