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
                <span class="worknoon-chat-widget__launcher-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" focusable="false">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5A8.48 8.48 0 0 1 21 11v.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="screen-reader-text"><?php echo esc_html($title); ?></span>
            </button>

            <div class="worknoon-chat-widget__panel" id="<?php echo esc_attr($panelId); ?>" data-worknoon-chat-panel hidden>
                <header class="worknoon-chat-widget__header">
                    <h2 class="worknoon-chat-widget__title"><?php echo esc_html($title); ?></h2>
                    <button class="worknoon-chat-widget__close" type="button" data-worknoon-chat-close aria-label="<?php echo esc_attr__('Close chat', 'worknoon-chat'); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" focusable="false">
                            <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>
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
