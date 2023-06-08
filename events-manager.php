<?php
/**
 * Plugin Name:       Events Manager
 * Plugin URI:        https://localhost/solvative-interview/
 * Description:       A plugin to to manage events
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Dharmrajsinh Jadeja
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       events-manager
 * Domain Path:       /languages
 *
 * @package EventsManager
 */

// Exit if plugin accessed directly.

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

// Include plugin main class file.
require_once plugin_dir_path( __FILE__ ) . './src/class-eventsmanager.php';

$events_manager = new EventsManager\EventsManager();

// Register activation and deactivation hook.
register_activation_hook( __FILE__, array( $events_manager, 'activate' ) );
