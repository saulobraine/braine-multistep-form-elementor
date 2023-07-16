<?php
/**
* Plugin Name: MultiStep Form - Braine
* Description: Creates a elementor widget for multistep form
* Version: 1.0
* Author: Saulo Braine
* Author URI: https://braine.dev
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: multistep-form-braine
* Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/* ACTIVATION */
register_activation_hook(__FILE__, function () {
    /* UPDATE PERMALINKS */
    flush_rewrite_rules();
});

/* DEACTIVATION */
register_deactivation_hook(__FILE__, function () {
    /* UPDATE PERMALINKS */
    flush_rewrite_rules();
});

// Including widget file
require_once __DIR__ . '/widgets/widgets.php';
