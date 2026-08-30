<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_Activator {

    public static function activate() {
        $defaults = array(
            'investoryspot_seo_api_key'       => '',
            'investoryspot_seo_model'         => 'llama-3.3-70b-versatile',
            'investoryspot_seo_auto_generate' => '0',
            'investoryspot_seo_post_types'    => array('post', 'page'),
        );

        foreach ($defaults as $key => $value) {
            if (false === get_option($key)) {
                update_option($key, $value);
            }
        }
    }

    public static function maybe_upgrade() {
        $version = get_option('investoryspot_seo_version', '1.0.0');

        if (version_compare($version, '1.1.0', '<')) {
            self::upgrade_to_1_1_0();
        }

        update_option('investoryspot_seo_version', INVESTORYSPOT_SEO_VERSION);
    }

    private static function upgrade_to_1_1_0() {
        $option_map = array(
            'simbe_ai_seo_api_key'       => 'investoryspot_seo_api_key',
            'simbe_ai_seo_model'         => 'investoryspot_seo_model',
            'simbe_ai_seo_auto_generate' => 'investoryspot_seo_auto_generate',
            'simbe_ai_seo_post_types'    => 'investoryspot_seo_post_types',
        );

        foreach ($option_map as $old_key => $new_key) {
            $old_value = get_option($old_key, null);
            if (null !== $old_value) {
                update_option($new_key, $old_value);
                delete_option($old_key);
            }
        }

        $meta_map = array(
            '_simbe_ai_seo_title'       => '_investoryspot_seo_title',
            '_simbe_ai_seo_description' => '_investoryspot_seo_description',
            '_simbe_ai_seo_keyphrase'   => '_investoryspot_seo_keyphrase',
            '_simbe_ai_seo_score'       => '_investoryspot_seo_score',
        );

        foreach ($meta_map as $old_key => $new_key) {
            global $wpdb;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $rows = $wpdb->get_results(
                $wpdb->prepare("SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s", $old_key)
            );

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $existing = get_post_meta($row->post_id, $new_key, true);
                    if ('' === $existing || false === $existing) {
                        add_post_meta($row->post_id, $new_key, maybe_unserialize($row->meta_value));
                    }
                }
                delete_post_meta_by_key($old_key);
            }
        }
    }

    public static function deactivate() {
    }
}
