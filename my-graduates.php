<?php

/**
 * Plugin Name: My Graduates
 * Description: A plugin to manage and display graduates.
 * Version: 1.0
 * Author: Your Name
 * Author URI: https://janczyk.dev
 * Text Domain: my-graduates
 * Domain Path: /languages
 */

namespace KJanczyk\MyGraduates;

if (! defined('ABSPATH')) {
    exit;
}

define('MY_GRADUATES_VERSION', '1.0.0');
define('MY_GRADUATES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MY_GRADUATES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MY_GRADUATES_PLUGIN_FILE', __FILE__);

spl_autoload_register(function ($class) {
    $prefix = 'KJanczyk\\MyGraduates\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

add_action('plugins_loaded', function () {
    Plugin::getInstance();
});
