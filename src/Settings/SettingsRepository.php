<?php

namespace Worknoon\Chat\Settings;

if (! defined('ABSPATH')) {
    exit;
}

final class SettingsRepository
{
    public const API_BASE_URL = 'api_base_url';
    public const FRONTEND_APP_URL = 'frontend_app_url';
    public const WIDGET_TITLE = 'widget_title';
    public const DEFAULT_CONTEXT = 'default_context';
    public const WIDGET_POSITION = 'widget_position';
    public const SITE_WIDGET_ENABLED = 'site_widget_enabled';

    /**
     * @return array{api_base_url:string,frontend_app_url:string,widget_title:string,default_context:string,widget_position:string,site_widget_enabled:string}
     */
    public function defaults(): array
    {
        return [
            self::API_BASE_URL => '',
            self::FRONTEND_APP_URL => '',
            self::WIDGET_TITLE => __('Worknoon Chat', 'worknoon-chat'),
            self::DEFAULT_CONTEXT => 'support',
            self::WIDGET_POSITION => 'bottom-right',
            self::SITE_WIDGET_ENABLED => '0',
        ];
    }

    /**
     * @return array{api_base_url:string,frontend_app_url:string,widget_title:string,default_context:string,widget_position:string,site_widget_enabled:string}
     */
    public function all(): array
    {
        $storedOptions = get_option(WORKNOON_CHAT_OPTIONS, []);
        $options = is_array($storedOptions) ? $storedOptions : [];

        return wp_parse_args($options, $this->defaults());
    }

    public function get(string $key): string
    {
        $options = $this->all();

        return isset($options[$key]) && is_string($options[$key]) ? $options[$key] : '';
    }

    /**
     * @param mixed $rawOptions
     * @return array{api_base_url:string,frontend_app_url:string,widget_title:string,default_context:string,widget_position:string,site_widget_enabled:string}
     */
    public function sanitize($rawOptions): array
    {
        $rawOptions = is_array($rawOptions) ? $rawOptions : [];
        $defaults = $this->defaults();

        $apiBaseUrl = isset($rawOptions[self::API_BASE_URL]) ? esc_url_raw(trim((string) $rawOptions[self::API_BASE_URL])) : '';
        $frontendAppUrl = isset($rawOptions[self::FRONTEND_APP_URL]) ? esc_url_raw(trim((string) $rawOptions[self::FRONTEND_APP_URL])) : '';
        $widgetTitle = isset($rawOptions[self::WIDGET_TITLE]) ? sanitize_text_field((string) $rawOptions[self::WIDGET_TITLE]) : '';
        $defaultContext = isset($rawOptions[self::DEFAULT_CONTEXT]) ? sanitize_key((string) $rawOptions[self::DEFAULT_CONTEXT]) : '';
        $widgetPosition = isset($rawOptions[self::WIDGET_POSITION]) ? sanitize_key((string) $rawOptions[self::WIDGET_POSITION]) : '';
        $siteWidgetEnabled = isset($rawOptions[self::SITE_WIDGET_ENABLED]) && '1' === (string) $rawOptions[self::SITE_WIDGET_ENABLED] ? '1' : '0';

        if (! in_array($defaultContext, ['support', 'designer', 'merchant'], true)) {
            $defaultContext = $defaults[self::DEFAULT_CONTEXT];
        }

        if (! in_array($widgetPosition, ['bottom-right', 'bottom-left'], true)) {
            $widgetPosition = $defaults[self::WIDGET_POSITION];
        }

        return [
            self::API_BASE_URL => $apiBaseUrl,
            self::FRONTEND_APP_URL => $frontendAppUrl,
            self::WIDGET_TITLE => '' !== $widgetTitle ? $widgetTitle : $defaults[self::WIDGET_TITLE],
            self::DEFAULT_CONTEXT => $defaultContext,
            self::WIDGET_POSITION => $widgetPosition,
            self::SITE_WIDGET_ENABLED => $siteWidgetEnabled,
        ];
    }
}
