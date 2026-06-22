<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_Meta_Box {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_meta_box'));
        add_action('save_post', array($this, 'save_meta_box'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        wp_enqueue_style('investoryspot-seo-assistant-admin', INVESTORYSPOT_SEO_PLUGIN_URL . 'assets/admin.css', array(), INVESTORYSPOT_SEO_VERSION);

        wp_enqueue_script('investoryspot-seo-assistant-admin', INVESTORYSPOT_SEO_PLUGIN_URL . 'assets/admin.js', array('jquery'), INVESTORYSPOT_SEO_VERSION, true);

        wp_localize_script('investoryspot-seo-assistant-admin', 'investoryspot_seo', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('investoryspot_seo_nonce'),
            'post_id'  => get_the_ID(),
        ));
    }

    public function add_meta_box() {
        $post_types = get_option('investoryspot_seo_post_types', array('post', 'page'));

        add_meta_box(
            'investoryspot_seo_meta',
            'InvestorySpot SEO Assistant',
            array($this, 'render_meta_box'),
            $post_types,
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        wp_nonce_field('investoryspot_seo_meta', 'investoryspot_seo_meta_nonce');

        $title       = get_post_meta($post->ID, '_investoryspot_seo_title', true);
        $description = get_post_meta($post->ID, '_investoryspot_seo_description', true);
        $keyphrase   = get_post_meta($post->ID, '_investoryspot_seo_keyphrase', true);
        $score       = get_post_meta($post->ID, '_investoryspot_seo_score', true);

        $ai = new InvestorySpot_SEO_AI();
        $api_ok = $ai->is_configured();
        ?>
        <div class="investoryspot-seo-meta-box">
            <div class="investoryspot-seo-sidebar">
                <div class="investoryspot-seo-score-wrap">
                    <h4>SEO Score</h4>
                    <div class="investoryspot-seo-score-circle <?php echo $score ? esc_attr('score-' . $this->score_class($score)) : ''; ?>">
                        <span class="investoryspot-seo-score-value"><?php echo $score ? esc_html($score) : '--'; ?></span>
                    </div>
                    <button type="button" class="button investoryspot-analyze-btn" data-post-id="<?php echo esc_attr($post->ID); ?>">
                        Analyze Now
                    </button>
                </div>

                <div class="investoryspot-seo-keyphrase-wrap">
                    <h4>Focus Keyphrase</h4>
                    <input type="text" id="investoryspot_seo_keyphrase" name="investoryspot_seo_keyphrase"
                           value="<?php echo esc_attr($keyphrase); ?>" placeholder="e.g. SEO tips"
                           class="widefat" />
                    <button type="button" class="button investoryspot-suggest-kw-btn" data-post-id="<?php echo esc_attr($post->ID); ?>">
                        Suggest
                    </button>
                </div>
            </div>

            <div class="investoryspot-seo-main">
                <div class="investoryspot-seo-field">
                    <label for="investoryspot_seo_title">SEO Title</label>
                    <div class="investoryspot-seo-input-row">
                        <input type="text" id="investoryspot_seo_title" name="investoryspot_seo_title"
                               value="<?php echo esc_attr($title); ?>" placeholder="Enter SEO title..."
                               class="widefat" maxlength="60" />
                        <button type="button" class="button investoryspot-generate-title-btn" <?php echo $api_ok ? '' : 'disabled'; ?>
                                data-post-id="<?php echo esc_attr($post->ID); ?>">
                            Generate with AI
                        </button>
                    </div>
                    <div class="investoryspot-seo-counter"><span id="title-count"><?php echo esc_html(strlen($title)); ?></span>/60</div>
                </div>

                <div class="investoryspot-seo-field">
                    <label for="investoryspot_seo_description">Meta Description</label>
                    <div class="investoryspot-seo-input-row">
                        <textarea id="investoryspot_seo_description" name="investoryspot_seo_description"
                                  placeholder="Enter meta description..." rows="3" class="widefat" maxlength="160"><?php echo esc_textarea($description); ?></textarea>
                        <button type="button" class="button investoryspot-generate-desc-btn" <?php echo $api_ok ? '' : 'disabled'; ?>
                                data-post-id="<?php echo esc_attr($post->ID); ?>">
                            Generate with AI
                        </button>
                    </div>
                    <div class="investoryspot-seo-counter"><span id="desc-count"><?php echo esc_html(strlen($description)); ?></span>/160</div>
                </div>

                <div class="investoryspot-seo-actions">
                    <button type="button" class="button button-primary investoryspot-generate-all-btn" <?php echo $api_ok ? '' : 'disabled'; ?>
                            data-post-id="<?php echo esc_attr($post->ID); ?>">
                        Generate All with AI
                    </button>
                    <span class="investoryspot-api-status <?php echo $api_ok ? 'api-ok' : 'api-missing'; ?>">
                        <?php echo $api_ok ? 'AI ready' : 'Set API key in Settings > InvestorySpot SEO Assistant'; ?>
                    </span>
                </div>

                <div id="investoryspot-seo-results"></div>
                <div class="investoryspot-seo-loading" style="display:none;">
                    <span class="spinner is-active"></span> Generating...
                </div>
            </div>
        </div>
        <?php
    }

    public function save_meta_box($post_id) {
        if (!isset($_POST['investoryspot_seo_meta_nonce'])) {
            return;
        }
        $nonce = sanitize_text_field(wp_unslash($_POST['investoryspot_seo_meta_nonce']));
        if (!wp_verify_nonce($nonce, 'investoryspot_seo_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            '_investoryspot_seo_title'       => 'investoryspot_seo_title',
            '_investoryspot_seo_description' => 'investoryspot_seo_description',
            '_investoryspot_seo_keyphrase'   => 'investoryspot_seo_keyphrase',
        );

        foreach ($fields as $meta_key => $field_name) {
            if (isset($_POST[$field_name])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field(wp_unslash($_POST[$field_name])));
            }
        }

        $auto = get_option('investoryspot_seo_auto_generate', '0');
        if ($auto === '1') {
            $this->auto_generate($post_id);
        }
    }

    private function auto_generate($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return;
        }

        $ai = new InvestorySpot_SEO_AI();
        if (!$ai->is_configured()) {
            return;
        }

        $keyphrase = get_post_meta($post_id, '_investoryspot_seo_keyphrase', true);

        $existing_title = get_post_meta($post_id, '_investoryspot_seo_title', true);
        if (empty($existing_title)) {
            $title = $ai->generate_meta_title($post->post_content, $keyphrase);
            if (strpos($title, 'Error') === false && strpos($title, 'API') === false) {
                update_post_meta($post_id, '_investoryspot_seo_title', sanitize_text_field($title));
            }
        }

        $existing_desc = get_post_meta($post_id, '_investoryspot_seo_description', true);
        if (empty($existing_desc)) {
            $desc = $ai->generate_meta_description($post->post_content, $keyphrase);
            if (strpos($desc, 'Error') === false && strpos($desc, 'API') === false) {
                update_post_meta($post_id, '_investoryspot_seo_description', sanitize_textarea_field($desc));
            }
        }
    }

    private function score_class($score) {
        if ($score >= 80) return 'good';
        if ($score >= 50) return 'ok';
        return 'bad';
    }
}
