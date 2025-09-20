<?php
/**
 * Plugin Name: Pwork
 * Plugin URI: https://palleon.website/pwork/
 * Description: Intranet For WordPress
 * Version: 1.3.3
 * Requires PHP: 7.0
 * Author: Egemenerd
 * Author URI: http://codecanyon.net/user/egemenerd
 * License: http://codecanyon.net/licenses
 * Text Domain: pwork
 * Domain Path: /languages
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'PWORK_PLUGIN_URL' ) ) {
	define( 'PWORK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'PWORK_VERSION' ) ) {
    define ('PWORK_VERSION', '1.3.3');
}

/* ---------------------------------------------------------
Include required files
----------------------------------------------------------- */

if ( file_exists(__DIR__ . '/cmb2/init.php' ) ) {
    require_once(__DIR__ . '/cmb2/init.php');
} else if ( file_exists(  __DIR__ . '/CMB2/init.php' ) ) {
    require_once(__DIR__ . '/CMB2/init.php');
}

include_once('settings-class.php');
include_once('pwork-class.php');

$messages = PworkSettings::get_option('messages_module', 'enable');
$anns = PworkSettings::get_option('announcements_module', 'enable');
$kb = PworkSettings::get_option('kb_module', 'enable');
$forum = PworkSettings::get_option('forum_module', 'enable');
$projects = PworkSettings::get_option('projects_module', 'enable');
$events = PworkSettings::get_option('events_module', 'enable');
$files = PworkSettings::get_option('files_module', 'enable');

if ($messages == 'enable') {
    include_once('messages-class.php');
}

if ($anns == 'enable') {
    include_once(__DIR__ . '/cpt/announcements.php');
}

if ($kb == 'enable') {
    include_once(__DIR__ . '/cpt/knowledgebase.php');
}

if ($forum == 'enable') {
    include_once(__DIR__ . '/cpt/forum.php');
}

if ($projects == 'enable') {
    include_once(__DIR__ . '/cpt/projects.php');
}

if ($events == 'enable') {
    include_once(__DIR__ . '/cpt/events.php');
}

if ($files == 'enable') {
    include_once(__DIR__ . '/cpt/files.php');
}