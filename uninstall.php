<?php
/**
 * Removes Worknoon Chat data when the plugin is uninstalled.
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('worknoon_chat_options');

$chatSessions = get_posts(
    [
        'post_type' => 'chat_session',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]
);

foreach ($chatSessions as $chatSessionId) {
    wp_delete_post((int) $chatSessionId, true);
}
