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

    public static function deactivate() {
    }
}
