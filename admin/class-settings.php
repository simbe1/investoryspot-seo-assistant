<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page() {
        add_options_page(
            'InvestorySpot SEO Assistant Settings',
            'InvestorySpot SEO Assistant',
            'manage_options',
             'investoryspot-seo-assistant',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings() {
        register_setting('investoryspot_seo_settings', 'investoryspot_seo_api_key', 'sanitize_text_field');
        register_setting('investoryspot_seo_settings', 'investoryspot_seo_model', array($this, 'sanitize_model'));
        register_setting('investoryspot_seo_settings', 'investoryspot_seo_auto_generate', 'absint');
        register_setting('investoryspot_seo_settings', 'investoryspot_seo_post_types', array($this, 'sanitize_post_types'));

        add_settings_section(
            'investoryspot_seo_main',
            'API Configuration',
            null,
            'investoryspot-seo-assistant'
        );

        add_settings_field(
            'investoryspot_seo_api_key',
            'Groq API Key',
            array($this, 'api_key_field'),
            'investoryspot-seo-assistant',
            'investoryspot_seo_main'
        );

        add_settings_field(
            'investoryspot_seo_model',
            'AI Model',
            array($this, 'model_field'),
            'investoryspot-seo-assistant',
            'investoryspot_seo_main'
        );

        add_settings_field(
            'investoryspot_seo_auto_generate',
            'Auto-generate on save',
            array($this, 'auto_generate_field'),
            'investoryspot-seo-assistant',
            'investoryspot_seo_main'
        );

        add_settings_field(
            'investoryspot_seo_post_types',
            'Enable for post types',
            array($this, 'post_types_field'),
            'investoryspot-seo-assistant',
            'investoryspot_seo_main'
        );
    }

    public function api_key_field() {
        $key = get_option('investoryspot_seo_api_key', '');
        echo '<input type="password" name="investoryspot_seo_api_key" value="' . esc_attr($key) . '" class="regular-text" />';
        echo '<p class="description">Get your API key from <a href="https://console.groq.com" target="_blank">console.groq.com</a></p>';
        if (!empty($key)) {
            echo '<p style="color:green;">&#10003; API key is set</p>';
        }
    }

    public function model_field() {
        $model = get_option('investoryspot_seo_model', 'llama-3.3-70b-versatile');
        $models = array(
            'llama-3.3-70b-versatile'   => 'Llama 3.3 70B (Recommended)',
            'llama-3.1-8b-instant'      => 'Llama 3.1 8B (Fast)',
            'mixtral-8x7b-32768'        => 'Mixtral 8x7B',
            'gemma2-9b-it'              => 'Gemma 2 9B',
        );
        echo '<select name="investoryspot_seo_model">';
        foreach ($models as $val => $label) {
            echo '<option value="' . esc_attr($val) . '" ' . selected($model, $val, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public function auto_generate_field() {
        $auto = get_option('investoryspot_seo_auto_generate', '0');
        echo '<label><input type="checkbox" name="investoryspot_seo_auto_generate" value="1" ' . checked('1', $auto, false) . ' /> Auto-generate SEO title & description when saving posts (sends content to Groq API automatically)</label>';
    }

    public function post_types_field() {
        $saved = get_option('investoryspot_seo_post_types', array('post', 'page'));
        $types = get_post_types(array('public' => true), 'objects');

        echo '<input type="hidden" name="investoryspot_seo_post_types" value="" />';
        foreach ($types as $type) {
            $checked = in_array($type->name, (array) $saved) ? 'checked' : '';
            echo '<label><input type="checkbox" name="investoryspot_seo_post_types[]" value="' . esc_attr($type->name) . '" ' . esc_attr($checked) . ' /> ' . esc_html($type->label) . '</label><br>';
        }
    }

    public function sanitize_model($input) {
        $allowed = array('llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'mixtral-8x7b-32768', 'gemma2-9b-it');
        if (in_array($input, $allowed, true)) {
            return $input;
        }
        return 'llama-3.3-70b-versatile';
    }

    public function sanitize_post_types($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>InvestorySpot SEO Assistant Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('investoryspot_seo_settings');
                do_settings_sections('investoryspot-seo-assistant');
                submit_button('Save Settings');
                ?>
            </form>
            <hr>
            <h2>How to get your Groq API Key</h2>
            <ol>
                <li>Go to <a href="https://console.groq.com" target="_blank">console.groq.com</a></li>
                <li>Sign up or log in</li>
                <li>Navigate to API Keys section</li>
                <li>Create a new API key</li>
                <li>Copy and paste it above</li>
            </ol>
            <p><strong>Note:</strong> Groq offers free credits to get started. Your API key stays on your server and is only used to communicate with Groq's API.</p>
        </div>
        <?php
    }
}
