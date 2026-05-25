<?php

namespace Worknoon\Chat\Admin;

use Worknoon\Chat\Settings\SettingsRepository;

if (! defined('ABSPATH')) {
    exit;
}

final class AdminPage
{
    private string $hookSuffix = '';

    public function __construct(private readonly SettingsRepository $settings)
    {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('wp_ajax_worknoon_save_settings', [$this, 'handleAjaxSave']);
    }

    public function registerMenu(): void
    {
        $hook = add_submenu_page(
            'edit.php?post_type=chat_session',
            __('Worknoon Chat Settings', 'worknoon-chat'),
            __('Settings', 'worknoon-chat'),
            'manage_options',
            'worknoon-chat-settings',
            [$this, 'renderSettingsPage']
        );

        $this->hookSuffix = (string) $hook;
    }

    public function enqueueAdminAssets(string $hookSuffix): void
    {
        if ($hookSuffix !== $this->hookSuffix) {
            return;
        }

        wp_enqueue_style(
            'worknoon-chat-admin',
            WORKNOON_CHAT_URL . 'assets/css/worknoon-chat-admin.css',
            [],
            WORKNOON_CHAT_VERSION
        );

        wp_enqueue_script(
            'worknoon-chat-admin',
            WORKNOON_CHAT_URL . 'assets/js/worknoon-chat-admin.js',
            [],
            WORKNOON_CHAT_VERSION,
            true
        );

        wp_localize_script('worknoon-chat-admin', 'wnAdminSettings', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('worknoon_settings_save'),
        ]);
    }

    public function handleAjaxSave(): void
    {
        check_ajax_referer('worknoon_settings_save', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'worknoon-chat')], 403);
            return;
        }

        $rawOptions = isset($_POST[WORKNOON_CHAT_OPTIONS]) ? wp_unslash($_POST[WORKNOON_CHAT_OPTIONS]) : [];
        if (! is_array($rawOptions)) {
            $rawOptions = [];
        }

        $sanitized = $this->settings->sanitize($rawOptions);
        update_option(WORKNOON_CHAT_OPTIONS, $sanitized);

        wp_send_json_success(['message' => __('Settings saved.', 'worknoon-chat')]);
    }

    public function renderSettingsPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage Worknoon Chat settings.', 'worknoon-chat'));
        }

        $apiBaseUrl     = $this->settings->get(SettingsRepository::API_BASE_URL);
        $frontendAppUrl = $this->settings->get(SettingsRepository::FRONTEND_APP_URL);
        $widgetTitle    = $this->settings->get(SettingsRepository::WIDGET_TITLE);
        $widgetPosition = $this->settings->get(SettingsRepository::WIDGET_POSITION);
        $siteWidget     = $this->settings->get(SettingsRepository::SITE_WIDGET_ENABLED);
        $defaultContext = $this->settings->get(SettingsRepository::DEFAULT_CONTEXT);

        $optName = WORKNOON_CHAT_OPTIONS;
        ?>
        <div class="wn-settings">

            <div class="wn-header">
                <div class="wn-header-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 13.5997 2.37562 15.1116 3.04346 16.4525C3.22094 16.8088 3.28001 17.2161 3.17712 17.6006L2.58151 19.8267C2.32295 20.793 3.20701 21.677 4.17335 21.4185L6.39939 20.8229C6.78393 20.72 7.19121 20.7791 7.54753 20.9565C8.88837 21.6244 10.4003 22 12 22Z" fill="white"/>
                        <path d="M15 12C15 12.5523 15.4477 13 16 13C16.5523 13 17 12.5523 17 12C17 11.4477 16.5523 11 16 11C15.4477 11 15 11.4477 15 12Z" fill="#0f766e"/>
                        <path d="M11 12C11 12.5523 11.4477 13 12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12Z" fill="#0f766e"/>
                        <path d="M7 12C7 12.5523 7.44772 13 8 13C8.55228 13 9 12.5523 9 12C9 11.4477 8.55228 11 8 11C7.44772 11 7 11.4477 7 12Z" fill="#0f766e"/>
                    </svg>
                </div>
                <div class="wn-header-text">
                    <h1 class="wn-header-title"><?php esc_html_e('Worknoon Chat', 'worknoon-chat'); ?></h1>
                    <span class="wn-header-version">v<?php echo esc_html(WORKNOON_CHAT_VERSION); ?></span>
                </div>
            </div>

            <form id="wn-settings-form" method="post">

                <!-- Connection -->
                <div class="wn-card">
                    <div class="wn-card-head">
                        <div class="wn-card-head-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                        </div>
                        <h2 class="wn-card-title"><?php esc_html_e('Connection', 'worknoon-chat'); ?></h2>
                    </div>
                    <div class="wn-card-body">
                        <div class="wn-field">
                            <label class="wn-label" for="wn-api-base-url"><?php esc_html_e('Backend API URL', 'worknoon-chat'); ?></label>
                            <input
                                class="wn-input"
                                id="wn-api-base-url"
                                name="<?php echo esc_attr($optName . '[' . SettingsRepository::API_BASE_URL . ']'); ?>"
                                type="url"
                                value="<?php echo esc_attr($apiBaseUrl); ?>"
                                placeholder="https://api.example.com/api/v1"
                                autocomplete="off"
                                spellcheck="false"
                            />
                            <p class="wn-hint"><?php esc_html_e('Base URL for the Worknoon backend API. Leave empty until your backend is ready.', 'worknoon-chat'); ?></p>
                        </div>

                        <div class="wn-field-divider"></div>

                        <div class="wn-field">
                            <label class="wn-label" for="wn-frontend-app-url"><?php esc_html_e('Frontend App URL', 'worknoon-chat'); ?></label>
                            <input
                                class="wn-input"
                                id="wn-frontend-app-url"
                                name="<?php echo esc_attr($optName . '[' . SettingsRepository::FRONTEND_APP_URL . ']'); ?>"
                                type="url"
                                value="<?php echo esc_attr($frontendAppUrl); ?>"
                                placeholder="https://chat.example.com"
                                autocomplete="off"
                                spellcheck="false"
                            />
                            <p class="wn-hint"><?php esc_html_e('Public URL for the Next.js chat frontend loaded inside the floating widget iframe.', 'worknoon-chat'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Widget -->
                <div class="wn-card">
                    <div class="wn-card-head">
                        <div class="wn-card-head-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <path d="M3 9h18M9 21V9"/>
                            </svg>
                        </div>
                        <h2 class="wn-card-title"><?php esc_html_e('Widget', 'worknoon-chat'); ?></h2>
                    </div>
                    <div class="wn-card-body">
                        <div class="wn-field">
                            <label class="wn-label" for="wn-widget-title"><?php esc_html_e('Widget Title', 'worknoon-chat'); ?></label>
                            <input
                                class="wn-input"
                                id="wn-widget-title"
                                name="<?php echo esc_attr($optName . '[' . SettingsRepository::WIDGET_TITLE . ']'); ?>"
                                type="text"
                                value="<?php echo esc_attr($widgetTitle); ?>"
                            />
                            <p class="wn-hint"><?php esc_html_e('Displayed in the chat widget header and used as the iframe title for accessibility.', 'worknoon-chat'); ?></p>
                        </div>

                        <div class="wn-field-divider"></div>

                        <div class="wn-field">
                            <span class="wn-label"><?php esc_html_e('Position', 'worknoon-chat'); ?></span>
                            <div class="wn-position-grid">
                                <div class="wn-position-card">
                                    <input
                                        type="radio"
                                        id="wn-pos-right"
                                        name="<?php echo esc_attr($optName . '[' . SettingsRepository::WIDGET_POSITION . ']'); ?>"
                                        value="bottom-right"
                                        <?php checked($widgetPosition, 'bottom-right'); ?>
                                    />
                                    <label class="wn-position-label" for="wn-pos-right">
                                        <div class="wn-position-preview">
                                            <span class="wn-position-dot wn-position-dot--right"></span>
                                        </div>
                                        <span class="wn-position-text"><?php esc_html_e('Bottom right', 'worknoon-chat'); ?></span>
                                    </label>
                                </div>
                                <div class="wn-position-card">
                                    <input
                                        type="radio"
                                        id="wn-pos-left"
                                        name="<?php echo esc_attr($optName . '[' . SettingsRepository::WIDGET_POSITION . ']'); ?>"
                                        value="bottom-left"
                                        <?php checked($widgetPosition, 'bottom-left'); ?>
                                    />
                                    <label class="wn-position-label" for="wn-pos-left">
                                        <div class="wn-position-preview">
                                            <span class="wn-position-dot wn-position-dot--left"></span>
                                        </div>
                                        <span class="wn-position-text"><?php esc_html_e('Bottom left', 'worknoon-chat'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="wn-field-divider"></div>

                        <div class="wn-toggle-row">
                            <div class="wn-toggle-info">
                                <span class="wn-label"><?php esc_html_e('Site-wide widget', 'worknoon-chat'); ?></span>
                                <p class="wn-hint"><?php esc_html_e('Show the floating chat widget on every public page automatically.', 'worknoon-chat'); ?></p>
                            </div>
                            <div class="wn-toggle-wrap">
                                <input
                                    type="checkbox"
                                    id="wn-site-widget"
                                    name="<?php echo esc_attr($optName . '[' . SettingsRepository::SITE_WIDGET_ENABLED . ']'); ?>"
                                    value="1"
                                    <?php checked($siteWidget, '1'); ?>
                                />
                                <label class="wn-toggle-track" for="wn-site-widget"></label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Default Context -->
                <div class="wn-card">
                    <div class="wn-card-head">
                        <div class="wn-card-head-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                        </div>
                        <h2 class="wn-card-title"><?php esc_html_e('Default Context', 'worknoon-chat'); ?></h2>
                    </div>
                    <div class="wn-card-body">
                        <div class="wn-field">
                            <p class="wn-hint"><?php esc_html_e('Sets the default chat flow when the widget opens without an explicit context attribute.', 'worknoon-chat'); ?></p>
                            <div class="wn-context-group">
                                <?php
                                $contexts = [
                                    'support'  => __('Support', 'worknoon-chat'),
                                    'designer' => __('Designer', 'worknoon-chat'),
                                    'merchant' => __('Merchant', 'worknoon-chat'),
                                ];
                                foreach ($contexts as $value => $label) :
                                    $fieldId = 'wn-context-' . esc_attr($value);
                                ?>
                                <div class="wn-context-card">
                                    <input
                                        type="radio"
                                        id="<?php echo esc_attr($fieldId); ?>"
                                        name="<?php echo esc_attr($optName . '[' . SettingsRepository::DEFAULT_CONTEXT . ']'); ?>"
                                        value="<?php echo esc_attr($value); ?>"
                                        <?php checked($defaultContext, $value); ?>
                                    />
                                    <label class="wn-context-pill" for="<?php echo esc_attr($fieldId); ?>">
                                        <?php echo esc_html($label); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wn-submit-row">
                    <button type="submit" id="wn-submit" class="wn-submit">
                        <?php esc_html_e('Save settings', 'worknoon-chat'); ?>
                    </button>
                </div>

            </form>
        </div>
        <?php
    }
}
