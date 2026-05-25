<?php
/**
 * Plugin Name: Worknoon Chat
 * Description: Embeds the Worknoon realtime chat experience and stores WordPress chat session records.
 * Version: 1.0.0
 * Author: Worknoon Assessment
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: worknoon-chat
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WORKNOON_CHAT_VERSION', '1.0.0');
define('WORKNOON_CHAT_FILE', __FILE__);
define('WORKNOON_CHAT_PATH', plugin_dir_path(__FILE__));
define('WORKNOON_CHAT_URL', plugin_dir_url(__FILE__));
define('WORKNOON_CHAT_OPTION_GROUP', 'worknoon_chat_settings');
define('WORKNOON_CHAT_OPTIONS', 'worknoon_chat_options');

require WORKNOON_CHAT_PATH . 'includes/autoload.php';

add_action('plugins_loaded', static function (): void {
    Worknoon\Chat\Plugin::instance()->boot();
});

register_activation_hook(__FILE__, ['Worknoon\Chat\Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['Worknoon\Chat\Plugin', 'deactivate']);
