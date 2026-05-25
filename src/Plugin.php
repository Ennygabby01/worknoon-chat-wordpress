<?php

namespace Worknoon\Chat;

use Worknoon\Chat\Admin\AdminPage;
use Worknoon\Chat\PostType\ChatSessionPostType;
use Worknoon\Chat\PublicView\ChatShortcode;
use Worknoon\Chat\Rest\RestController;
use Worknoon\Chat\Settings\SettingsRepository;

if (! defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    private static ?self $instance = null;

    private SettingsRepository $settings;

    private function __construct()
    {
        $this->settings = new SettingsRepository();
    }

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        (new ChatSessionPostType())->registerHooks();
        (new AdminPage($this->settings))->registerHooks();
        (new ChatShortcode($this->settings))->registerHooks();
        (new RestController($this->settings))->registerHooks();
    }

    public static function activate(): void
    {
        (new ChatSessionPostType())->register();
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
