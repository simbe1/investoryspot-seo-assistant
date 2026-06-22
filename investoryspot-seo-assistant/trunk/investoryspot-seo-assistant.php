<?php
/**
 * Plugin Name:       InvestorySpot SEO Assistant
 * Plugin URI:        https://investoryspot.com/plugins
 * Description:       AI-powered SEO plugin using Groq API. Generate meta titles, descriptions, and get SEO analysis with one click.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Author:            simbe1
 * Author URI:        https://investoryspot.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       investoryspot-seo-assistant
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

define('INVESTORYSPOT_SEO_VERSION', '1.0.0');
define('INVESTORYSPOT_SEO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('INVESTORYSPOT_SEO_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'includes/class-activator.php';
require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'includes/class-ai.php';
require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'includes/class-seo-analysis.php';
require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'admin/class-settings.php';
require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'admin/class-meta-box.php';
require_once INVESTORYSPOT_SEO_PLUGIN_DIR . 'admin/class-admin.php';

function investoryspot_seo_init() {
    new InvestorySpot_SEO_Admin();
    new InvestorySpot_SEO_Settings();
    new InvestorySpot_SEO_Meta_Box();
}
add_action('plugins_loaded', 'investoryspot_seo_init');

register_activation_hook(__FILE__, array('InvestorySpot_SEO_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('InvestorySpot_SEO_Activator', 'deactivate'));
