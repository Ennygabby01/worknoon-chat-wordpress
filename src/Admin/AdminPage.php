<?php

namespace Worknoon\Chat\Admin;

use Worknoon\Chat\Settings\SettingsRepository;

if (! defined('ABSPATH')) {
    exit;
}

final class AdminPage
{
    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function registerMenu(): void
    {
        add_options_page(
            __('Worknoon Chat Settings', 'worknoon-chat'),
            __('Worknoon Chat', 'worknoon-chat'),
            'manage_options',
            'worknoon-chat',
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting(
            WORKNOON_CHAT_OPTION_GROUP,
            WORKNOON_CHAT_OPTIONS,
            [
                'type' => 'array',
                'sanitize_callback' => [$this->settings, 'sanitize'],
                'default' => $this->settings->defaults(),
            ]
        );

        add_settings_section(
            'worknoon_chat_connection',
            __('Chat Connection', 'worknoon-chat'),
            '__return_false',
            'worknoon-chat'
        );

        add_settings_field(
            SettingsRepository::API_BASE_URL,
            __('Backend API URL', 'worknoon-chat'),
            [$this, 'renderApiBaseUrlField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );

        add_settings_field(
            SettingsRepository::FRONTEND_APP_URL,
            __('Frontend App URL', 'worknoon-chat'),
            [$this, 'renderFrontendAppUrlField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );

        add_settings_field(
            SettingsRepository::WIDGET_TITLE,
            __('Widget Title', 'worknoon-chat'),
            [$this, 'renderWidgetTitleField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );

        add_settings_field(
            SettingsRepository::WIDGET_POSITION,
            __('Widget Position', 'worknoon-chat'),
            [$this, 'renderWidgetPositionField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );

        add_settings_field(
            SettingsRepository::SITE_WIDGET_ENABLED,
            __('Site-wide Widget', 'worknoon-chat'),
            [$this, 'renderSiteWidgetEnabledField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );

        add_settings_field(
            SettingsRepository::DEFAULT_CONTEXT,
            __('Default Context', 'worknoon-chat'),
            [$this, 'renderDefaultContextField'],
            'worknoon-chat',
            'worknoon_chat_connection'
        );
    }

    public function renderSettingsPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage Worknoon Chat settings.', 'worknoon-chat'));
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(WORKNOON_CHAT_OPTION_GROUP);
                do_settings_sections('worknoon-chat');
                submit_button(__('Save Settings', 'worknoon-chat'));
                ?>
            </form>
        </div>
        <?php
    }

    public function renderApiBaseUrlField(): void
    {
        $value = $this->settings->get(SettingsRepository::API_BASE_URL);
        ?>
        <input
            class="regular-text"
            id="worknoon-chat-api-base-url"
            name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::API_BASE_URL . ']'); ?>"
            type="url"
            value="<?php echo esc_attr($value); ?>"
            placeholder="<?php echo esc_attr__('https://api.example.com/api/v1', 'worknoon-chat'); ?>"
        />
        <p class="description"><?php echo esc_html__('Base URL for the Worknoon backend API. Leave empty until your backend environment is ready.', 'worknoon-chat'); ?></p>
        <?php
    }

    public function renderFrontendAppUrlField(): void
    {
        $value = $this->settings->get(SettingsRepository::FRONTEND_APP_URL);
        ?>
        <input
            class="regular-text"
            id="worknoon-chat-frontend-app-url"
            name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::FRONTEND_APP_URL . ']'); ?>"
            type="url"
            value="<?php echo esc_attr($value); ?>"
            placeholder="<?php echo esc_attr__('https://chat.example.com', 'worknoon-chat'); ?>"
        />
        <p class="description"><?php echo esc_html__('Public URL for the Next.js chat frontend loaded inside the floating iframe.', 'worknoon-chat'); ?></p>
        <?php
    }

    public function renderWidgetTitleField(): void
    {
        $value = $this->settings->get(SettingsRepository::WIDGET_TITLE);
        ?>
        <input
            class="regular-text"
            id="worknoon-chat-widget-title"
            name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::WIDGET_TITLE . ']'); ?>"
            type="text"
            value="<?php echo esc_attr($value); ?>"
        />
        <?php
    }

    public function renderDefaultContextField(): void
    {
        $value = $this->settings->get(SettingsRepository::DEFAULT_CONTEXT);
        $contexts = [
            'support' => __('Support', 'worknoon-chat'),
            'designer' => __('Designer', 'worknoon-chat'),
            'merchant' => __('Merchant', 'worknoon-chat'),
        ];
        ?>
        <select
            id="worknoon-chat-default-context"
            name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::DEFAULT_CONTEXT . ']'); ?>"
        >
            <?php foreach ($contexts as $context => $label) : ?>
                <option value="<?php echo esc_attr($context); ?>" <?php selected($value, $context); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function renderSiteWidgetEnabledField(): void
    {
        $value = $this->settings->get(SettingsRepository::SITE_WIDGET_ENABLED);
        ?>
        <label for="worknoon-chat-site-widget-enabled">
            <input
                id="worknoon-chat-site-widget-enabled"
                name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::SITE_WIDGET_ENABLED . ']'); ?>"
                type="checkbox"
                value="1"
                <?php checked($value, '1'); ?>
            />
            <?php echo esc_html__('Show the floating chat widget on every public page.', 'worknoon-chat'); ?>
        </label>
        <?php
    }

    public function renderWidgetPositionField(): void
    {
        $value = $this->settings->get(SettingsRepository::WIDGET_POSITION);
        $positions = [
            'bottom-right' => __('Bottom right', 'worknoon-chat'),
            'bottom-left' => __('Bottom left', 'worknoon-chat'),
        ];
        ?>
        <select
            id="worknoon-chat-widget-position"
            name="<?php echo esc_attr(WORKNOON_CHAT_OPTIONS . '[' . SettingsRepository::WIDGET_POSITION . ']'); ?>"
        >
            <?php foreach ($positions as $position => $label) : ?>
                <option value="<?php echo esc_attr($position); ?>" <?php selected($value, $position); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
}
