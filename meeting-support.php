<?php

/**
 *
 * The actual file called by WordPress to initiate the plugin
 *
 * @link              https://www.ripe.net
 * @since             1.0.0
 * @package           Meeting_Support
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Meeting Support
 * Description:       Plugin to assist in the setting up and administering of ripe.net-style meetings
 * Version:           1.3.0
 * Author:            Oliver Payne
 * Author URI:        https://www.ripe.net
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       meeting-support
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-meeting-support-activator.php
 */
function activate_meeting_support($network_wide)
{
    if (is_multisite() && $network_wide) {
        die();
    }
    require_once plugin_dir_path(__FILE__) . 'includes/class-meeting-support-activator.php';
    Meeting_Support_Activator::activate();
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-meeting-support-deactivator.php
 */
function deactivate_meeting_support()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-meeting-support-deactivator.php';
    Meeting_Support_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_meeting_support');
register_deactivation_hook(__FILE__, 'deactivate_meeting_support');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-meeting-support.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_meeting_support()
{
    $plugin = new Meeting_Support();
    $plugin->run();
}
run_meeting_support();
