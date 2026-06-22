<?php
defined('ABSPATH') || exit;

class InvestorySpot_SEO_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_investoryspot_generate_title', array($this, 'ajax_generate_title'));
        add_action('wp_ajax_investoryspot_generate_description', array($this, 'ajax_generate_description'));
        add_action('wp_ajax_investoryspot_generate_all', array($this, 'ajax_generate_all'));
        add_action('wp_ajax_investoryspot_analyze', array($this, 'ajax_analyze'));
        add_action('wp_ajax_investoryspot_suggest_keyphrases', array($this, 'ajax_suggest_keyphrases'));

        add_action('admin_init', array($this, 'add_list_columns'));
        add_action('pre_get_posts', array($this, 'sort_by_score'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_list_styles'));
    }

    public function add_list_columns() {
        $post_types = get_option('investoryspot_seo_post_types', array('post', 'page'));

        foreach ((array) $post_types as $pt) {
            add_filter("manage_{$pt}_posts_columns", array($this, 'seo_score_column'));
            add_action("manage_{$pt}_posts_custom_column", array($this, 'render_seo_score_column'), 10, 2);
            add_filter("manage_edit-{$pt}_sortable_columns", array($this, 'sortable_seo_score_column'));
        }
    }

    public function seo_score_column($columns) {
        $columns['investoryspot_seo_score'] = 'SEO Score';
        return $columns;
    }

    public function render_seo_score_column($column, $post_id) {
        if ($column !== 'investoryspot_seo_score') {
            return;
        }

        $score = get_post_meta($post_id, '_investoryspot_seo_score', true);
        $title = get_post_meta($post_id, '_investoryspot_seo_title', true);

        if ($score === '' || $score === false) {
            echo '<span class="investoryspot-score-dot score-none" title="Not analyzed">--</span>';
            return;
        }

        $score = intval($score);
        $class = $score >= 80 ? 'good' : ($score >= 50 ? 'ok' : 'bad');
        $status = $score >= 80 ? 'Good' : ($score >= 50 ? 'Needs work' : 'Poor');
        $hint = $title ? "Title: " . esc_attr($title) : 'No SEO title set';

        echo '<span class="investoryspot-score-dot score-' . esc_attr($class) . '" title="' . esc_attr($status) . ' - ' . esc_attr($hint) . '">' . esc_html($score) . '</span>';
    }

    public function sortable_seo_score_column($columns) {
        $columns['investoryspot_seo_score'] = 'investoryspot_seo_score';
        return $columns;
    }

    public function sort_by_score($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');
        if ($orderby === 'investoryspot_seo_score') {
            $query->set('meta_key', '_investoryspot_seo_score');
            $query->set('orderby', 'meta_value_num');
        }
    }

    public function enqueue_list_styles($hook) {
        if ($hook === 'edit.php') {
            wp_enqueue_style('investoryspot-seo-assistant-admin', INVESTORYSPOT_SEO_PLUGIN_URL . 'assets/admin.css', array(), INVESTORYSPOT_SEO_VERSION);
        }
    }

    public function add_admin_menu() {
        add_menu_page(
            'InvestorySpot SEO Assistant',
            'InvestorySpot SEO Assistant',
            'manage_options',
            'investoryspot-seo-assistant-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-chart-line',
            100
        );
    }

    public function render_dashboard() {
        $ai = new InvestorySpot_SEO_AI();
        $api_ok = $ai->is_configured();
        ?>
        <div class="wrap">
            <h1>InvestorySpot SEO Assistant Dashboard</h1>

            <?php if (!$api_ok) : ?>
                <div class="notice notice-warning">
                    <p><strong>API key not configured.</strong> <a href="<?php echo esc_url(admin_url('options-general.php?page=investoryspot-seo-assistant')); ?>">Go to Settings → InvestorySpot SEO Assistant</a> to add your Groq API key.</p>
                </div>
            <?php else : ?>
                <div class="notice notice-success">
                    <p>Groq API is connected and ready.</p>
                </div>
            <?php endif; ?>

            <div class="investoryspot-dashboard-grid">
                <div class="card">
                    <h2>Quick Start</h2>
                    <ol>
                        <li>Edit a post or page</li>
                        <li>Set a <strong>Focus Keyphrase</strong> in the InvestorySpot SEO Assistant meta box</li>
                        <li>Click <strong>Generate with AI</strong> for instant SEO title & description</li>
                        <li>Click <strong>Analyze</strong> to get your SEO score and suggestions</li>
                    </ol>
                </div>

                <div class="card">
                    <h2>Settings</h2>
                    <p>Configure API key, model, and post types:</p>
                    <a href="<?php echo esc_url(admin_url('options-general.php?page=investoryspot-seo-assistant')); ?>" class="button button-primary">Open Settings</a>
                </div>

                <div class="card">
                    <h2>Bulk SEO (Coming Soon)</h2>
                    <p>Generate SEO for all your posts at once.</p>
                </div>
            </div>

            <hr>

            <h2>Posts without SEO Data</h2>
            <?php $this->render_unoptimized_posts(); ?>
        </div>
        <?php
    }

    private function render_unoptimized_posts() {
        $args = array(
            'post_type'      => get_option('investoryspot_seo_post_types', array('post', 'page')),
            'posts_per_page' => 10,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_investoryspot_seo_title',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => '_investoryspot_seo_description',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            echo '<ul>';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<li><a href="' . esc_url(get_edit_post_link()) . '">' . esc_html(get_the_title()) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>All posts have SEO data!</p>';
        }
        wp_reset_postdata();
    }

    public function ajax_generate_title() {
        check_ajax_referer('investoryspot_seo_nonce', 'nonce');
        $this->verify_ajax();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post    = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $keyphrase = get_post_meta($post_id, '_investoryspot_seo_keyphrase', true);
        $ai        = new InvestorySpot_SEO_AI();
        $result    = $ai->generate_meta_title($post->post_content, $keyphrase);

        if (strpos($result, 'Error') === 0 || strpos($result, 'API') === 0) {
            wp_send_json_error($result);
        }

        update_post_meta($post_id, '_investoryspot_seo_title', sanitize_text_field($result));
        wp_send_json_success(array('title' => $result));
    }

    public function ajax_generate_description() {
        check_ajax_referer('investoryspot_seo_nonce', 'nonce');
        $this->verify_ajax();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post    = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $keyphrase = get_post_meta($post_id, '_investoryspot_seo_keyphrase', true);
        $ai        = new InvestorySpot_SEO_AI();
        $result    = $ai->generate_meta_description($post->post_content, $keyphrase);

        if (strpos($result, 'Error') === 0 || strpos($result, 'API') === 0) {
            wp_send_json_error($result);
        }

        update_post_meta($post_id, '_investoryspot_seo_description', sanitize_textarea_field($result));
        wp_send_json_success(array('description' => $result));
    }

    public function ajax_generate_all() {
        check_ajax_referer('investoryspot_seo_nonce', 'nonce');
        $this->verify_ajax();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post    = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $keyphrase = get_post_meta($post_id, '_investoryspot_seo_keyphrase', true);
        $ai        = new InvestorySpot_SEO_AI();

        $title = $ai->generate_meta_title($post->post_content, $keyphrase);
        $desc  = $ai->generate_meta_description($post->post_content, $keyphrase);

        $errors = array();

        if (strpos($title, 'Error') === 0 || strpos($title, 'API') === 0) {
            $errors[] = $title;
            $title = '';
        } else {
            update_post_meta($post_id, '_investoryspot_seo_title', sanitize_text_field($title));
        }

        if (strpos($desc, 'Error') === 0 || strpos($desc, 'API') === 0) {
            $errors[] = $desc;
            $desc = '';
        } else {
            update_post_meta($post_id, '_investoryspot_seo_description', sanitize_textarea_field($desc));
        }

        wp_send_json_success(array(
            'title'       => $title,
            'description' => $desc,
            'errors'      => $errors,
        ));
    }

    public function ajax_analyze() {
        check_ajax_referer('investoryspot_seo_nonce', 'nonce');
        $this->verify_ajax();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post    = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $keyphrase = get_post_meta($post_id, '_investoryspot_seo_keyphrase', true);

        $analysis = new InvestorySpot_SEO_Analysis($post->post_content, $keyphrase, $post->post_title);
        $score    = $analysis->calculate_score();
        $checks   = $analysis->get_checks();

        update_post_meta($post_id, '_investoryspot_seo_score', $score);

        $ai = new InvestorySpot_SEO_AI();
        $ai_analysis = array();
        if ($ai->is_configured()) {
            $ai_analysis = $ai->analyze_content($post->post_content, $keyphrase);
        }

        wp_send_json_success(array(
            'score'       => $score,
            'checks'      => $checks,
            'ai_analysis' => $ai_analysis,
        ));
    }

    public function ajax_suggest_keyphrases() {
        check_ajax_referer('investoryspot_seo_nonce', 'nonce');
        $this->verify_ajax();

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $post    = get_post($post_id);

        if (!$post) {
            wp_send_json_error('Post not found');
        }

        $ai     = new InvestorySpot_SEO_AI();
        $result = $ai->suggest_keyphrases($post->post_content);

        if (strpos($result, 'Error') === 0 || strpos($result, 'API') === 0) {
            wp_send_json_error($result);
        }

        wp_send_json_success(array('keyphrases' => $result));
    }

    private function verify_ajax() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Unauthorized');
        }
    }
}
