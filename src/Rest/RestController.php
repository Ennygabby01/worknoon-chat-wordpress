<?php

namespace Worknoon\Chat\Rest;

use Worknoon\Chat\PostType\ChatSessionPostType;
use Worknoon\Chat\Settings\SettingsRepository;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if (! defined('ABSPATH')) {
    exit;
}

final class RestController
{
    private const NAMESPACE = 'worknoon-chat/v1';

    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function registerHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    public function registerRoutes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/config',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'getConfig'],
                'permission_callback' => '__return_true',
            ]
        );

        register_rest_route(
            self::NAMESPACE,
            '/sessions',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'createSession'],
                'permission_callback' => [$this, 'canCreateSession'],
                'args' => [
                    'context' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'sanitize_key',
                    ],
                    'sourceUrl' => [
                        'type' => 'string',
                        'required' => false,
                        'sanitize_callback' => 'esc_url_raw',
                    ],
                ],
            ]
        );
    }

    public function getConfig(): WP_REST_Response
    {
        return rest_ensure_response(
            [
                'widgetTitle' => $this->settings->get(SettingsRepository::WIDGET_TITLE),
                'defaultContext' => $this->settings->get(SettingsRepository::DEFAULT_CONTEXT),
                'frontendAppUrl' => $this->settings->get(SettingsRepository::FRONTEND_APP_URL),
                'isConfigured' => '' !== $this->settings->get(SettingsRepository::FRONTEND_APP_URL),
            ]
        );
    }

    public function canCreateSession(WP_REST_Request $request): bool
    {
        $nonce = $request->get_header('x_wp_nonce');

        return is_user_logged_in() && is_string($nonce) && (bool) wp_verify_nonce($nonce, 'wp_rest');
    }

    public function createSession(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $context = sanitize_key((string) ($request->get_param('context') ?: $this->settings->get(SettingsRepository::DEFAULT_CONTEXT)));
        $sourceUrl = esc_url_raw((string) ($request->get_param('sourceUrl') ?: home_url('/')));

        if (! in_array($context, ['support', 'designer', 'merchant'], true)) {
            return new WP_Error(
                'worknoon_chat_invalid_context',
                __('Invalid chat context.', 'worknoon-chat'),
                ['status' => 400]
            );
        }

        $postId = wp_insert_post(
            [
                'post_type' => ChatSessionPostType::NAME,
                'post_status' => 'private',
                'post_title' => sprintf(
                    /* translators: %s is a formatted date/time. */
                    __('Chat Session - %s', 'worknoon-chat'),
                    current_time('mysql')
                ),
                'meta_input' => [
                    'worknoon_chat_context' => $context,
                    'worknoon_chat_source_url' => $sourceUrl,
                    'worknoon_chat_user_id' => get_current_user_id(),
                ],
            ],
            true
        );

        if (is_wp_error($postId)) {
            return new WP_Error(
                'worknoon_chat_session_failed',
                __('We could not start a chat session. Please try again.', 'worknoon-chat'),
                ['status' => 500]
            );
        }

        return rest_ensure_response(
            [
                'sessionId' => (int) $postId,
                'context' => $context,
                'sourceUrl' => $sourceUrl,
            ]
        );
    }
}
