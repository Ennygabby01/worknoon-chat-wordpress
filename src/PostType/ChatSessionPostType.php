<?php

namespace Worknoon\Chat\PostType;

if (! defined('ABSPATH')) {
    exit;
}

final class ChatSessionPostType
{
    public const NAME = 'chat_session';

    public function registerHooks(): void
    {
        add_action('init', [$this, 'register']);
    }

    public function register(): void
    {
        register_post_type(
            self::NAME,
            [
                'labels' => [
                    'name' => __('Chat Sessions', 'worknoon-chat'),
                    'singular_name' => __('Chat Session', 'worknoon-chat'),
                    'add_new_item' => __('Add New Chat Session', 'worknoon-chat'),
                    'edit_item' => __('Edit Chat Session', 'worknoon-chat'),
                    'new_item' => __('New Chat Session', 'worknoon-chat'),
                    'view_item' => __('View Chat Session', 'worknoon-chat'),
                    'search_items' => __('Search Chat Sessions', 'worknoon-chat'),
                    'not_found' => __('No chat sessions found.', 'worknoon-chat'),
                ],
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'menu_icon' => 'dashicons-format-chat',
                'supports' => ['title', 'custom-fields'],
                'capability_type' => 'post',
                'map_meta_cap' => true,
            ]
        );
    }
}
