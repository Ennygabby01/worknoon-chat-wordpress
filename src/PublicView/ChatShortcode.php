<?php

namespace Worknoon\Chat\PublicView;

use Worknoon\Chat\Settings\SettingsRepository;

if (! defined('ABSPATH')) {
    exit;
}

final class ChatShortcode
{
    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function registerHooks(): void
    {
        add_shortcode('worknoon_chat', [$this, 'render']);
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);
        add_action('wp_footer', [$this, 'renderSiteWidget']);
    }

    public function registerAssets(): void
    {
        wp_register_style(
            'worknoon-chat-widget',
            WORKNOON_CHAT_URL . 'assets/css/worknoon-chat-widget.css',
            [],
            WORKNOON_CHAT_VERSION
        );

        wp_register_script(
            'worknoon-chat-widget',
            WORKNOON_CHAT_URL . 'assets/js/worknoon-chat-widget.js',
            [],
            WORKNOON_CHAT_VERSION,
            true
        );
    }

    /**
     * @param array<string,mixed>|string $attributes
     */
    public function render($attributes = []): string
    {
        $attributes = shortcode_atts(
            [
                'context' => $this->settings->get(SettingsRepository::DEFAULT_CONTEXT),
                'title' => $this->settings->get(SettingsRepository::WIDGET_TITLE),
                'url' => $this->settings->get(SettingsRepository::FRONTEND_APP_URL),
                'position' => $this->settings->get(SettingsRepository::WIDGET_POSITION),
            ],
            is_array($attributes) ? $attributes : [],
            'worknoon_chat'
        );

        $context = sanitize_key((string) $attributes['context']);
        if (! in_array($context, ['support', 'designer', 'merchant'], true)) {
            $context = $this->settings->get(SettingsRepository::DEFAULT_CONTEXT);
        }

        $title = sanitize_text_field((string) $attributes['title']);
        $frontendAppUrl = esc_url_raw((string) $attributes['url']);
        $position = sanitize_key((string) $attributes['position']);

        if (! in_array($position, ['bottom-right', 'bottom-left'], true)) {
            $position = $this->settings->get(SettingsRepository::WIDGET_POSITION);
        }

        $iframeUrl = $this->buildIframeUrl($frontendAppUrl, $context);
        $panelId = wp_unique_id('worknoon-chat-panel-');

        wp_enqueue_style('worknoon-chat-widget');
        wp_enqueue_script('worknoon-chat-widget');
        wp_localize_script(
            'worknoon-chat-widget',
            'WorknoonChat',
            [
                'restUrl' => esc_url_raw(rest_url('worknoon-chat/v1')),
                'nonce' => wp_create_nonce('wp_rest'),
                'isLoggedIn' => is_user_logged_in(),
            ]
        );

        ob_start();
        ?>
        <section
            class="worknoon-chat-widget worknoon-chat-widget--<?php echo esc_attr($position); ?>"
            data-worknoon-chat
            data-context="<?php echo esc_attr($context); ?>"
        >
            <button
                class="worknoon-chat-widget__launcher"
                type="button"
                data-worknoon-chat-toggle
                aria-controls="<?php echo esc_attr($panelId); ?>"
                aria-expanded="false"
            >
                <span class="worknoon-chat-widget__launcher-icon worknoon-chat-widget__launcher-icon--chat" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22Z" fill="white"/>
                        <path d="M15 12C15 12.5523 15.4477 13 16 13C16.5523 13 17 12.5523 17 12C17 11.4477 16.5523 11 16 11C15.4477 11 15 11.4477 15 12Z" fill="#0f766e"/>
                        <path d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z" fill="#0f766e"/>
                        <path d="M7 12C7 12.5523 7.44772 13 8 13C8.55228 13 9 12.5523 9 12C9 11.4477 8.55228 11 8 11C7.44772 11 7 11.4477 7 12Z" fill="#0f766e"/>
                    </svg>
                </span>
                <span class="worknoon-chat-widget__launcher-icon worknoon-chat-widget__launcher-icon--close" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 -4.5 24 24" fill="none" focusable="false" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23.405,0.63 C22.576,-0.2 21.23,-0.2 20.401,0.63 L12.016,9.88 L3.63,0.63 C2.801,-0.2 1.455,-0.2 0.626,0.63 C-0.203,1.46 -0.203,2.81 0.626,3.64 L10.381,14.4 C10.83,14.85 11.429,15.05 12.016,15.01 C12.603,15.05 13.201,14.85 13.65,14.4 L23.405,3.64 C24.234,2.81 24.234,1.46 23.405,0.63" fill="white"/>
                    </svg>
                </span>
                <span class="screen-reader-text"><?php echo esc_html($title); ?></span>
            </button>

            <div class="worknoon-chat-widget__panel" id="<?php echo esc_attr($panelId); ?>" data-worknoon-chat-panel hidden>
                <header class="worknoon-chat-widget__header">
                    <div class="worknoon-chat-widget__header-avatar" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" focusable="false" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22Z" fill="white"/>
                            <path d="M15 12C15 12.5523 15.4477 13 16 13C16.5523 13 17 12.5523 17 12C17 11.4477 16.5523 11 16 11C15.4477 11 15 11.4477 15 12Z" fill="#0f766e"/>
                            <path d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z" fill="#0f766e"/>
                            <path d="M7 12C7 12.5523 7.44772 13 8 13C8.55228 13 9 12.5523 9 12C9 11.4477 8.55228 11 8 11C7.44772 11 7 11.4477 7 12Z" fill="#0f766e"/>
                        </svg>
                    </div>
                    <div class="worknoon-chat-widget__header-meta">
                        <h2 class="worknoon-chat-widget__title"><?php echo esc_html($title); ?></h2>
                        <span class="worknoon-chat-widget__status">
                            <span class="worknoon-chat-widget__status-dot" aria-hidden="true"></span>
                            <?php echo esc_html__('Online', 'worknoon-chat'); ?>
                        </span>
                    </div>
                    <button class="worknoon-chat-widget__close" type="button" data-worknoon-chat-close aria-label="<?php echo esc_attr__('Close chat', 'worknoon-chat'); ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" focusable="false">
                            <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                </header>

                <?php if ('' !== $iframeUrl) : ?>
                    <iframe
                        class="worknoon-chat-widget__iframe"
                        title="<?php echo esc_attr($title); ?>"
                        src="<?php echo esc_url($iframeUrl); ?>"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="clipboard-write"
                    ></iframe>
                <?php else : ?>
                    <div class="worknoon-chat-widget__empty">
                        <?php echo esc_html__('Set the Worknoon frontend app URL in plugin settings to load chat.', 'worknoon-chat'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    public function renderSiteWidget(): void
    {
        if ('1' !== $this->settings->get(SettingsRepository::SITE_WIDGET_ENABLED)) {
            return;
        }

        echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function buildIframeUrl(string $frontendAppUrl, string $context): string
    {
        if ('' === $frontendAppUrl) {
            return '';
        }

        return add_query_arg(
            [
                'embed' => 'wordpress',
                'context' => $context,
                'sourceUrl' => $this->currentSourceUrl(),
            ],
            trailingslashit($frontendAppUrl)
        );
    }

    private function currentSourceUrl(): string
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI'])) : '/';

        return esc_url_raw(home_url($requestUri));
    }
}
