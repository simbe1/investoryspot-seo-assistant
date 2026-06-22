<?php
defined('WP_UNINSTALL_PLUGIN') || exit;

$investoryspot_seo_options = array(
    'investoryspot_seo_api_key',
    'investoryspot_seo_model',
    'investoryspot_seo_auto_generate',
    'investoryspot_seo_post_types',
);

foreach ($investoryspot_seo_options as $investoryspot_seo_option) {
    delete_option($investoryspot_seo_option);
}

$investoryspot_seo_meta_keys = array(
    '_investoryspot_seo_title',
    '_investoryspot_seo_description',
    '_investoryspot_seo_keyphrase',
    '_investoryspot_seo_score',
);

foreach ($investoryspot_seo_meta_keys as $investoryspot_seo_key) {
    delete_post_meta_by_key($investoryspot_seo_key);
}
