<?php

if (! defined('ABSPATH')) {
    exit;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'Worknoon\\Chat\\';

    if (0 !== strncmp($class, $prefix, strlen($prefix))) {
        return;
    }

    $relative_class = substr($class, strlen($prefix));
    $relative_path = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
    $file = WORKNOON_CHAT_PATH . 'src/' . $relative_path;

    if (is_readable($file)) {
        require $file;
    }
});
