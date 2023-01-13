<?php
/**

 * Plugin Name: Automatic Articles Importer
 * Description: This plugin is used to import articles from Intercom Service.
 * Plugin URI: https://www.zluck.com/
 * Version: 1.0
 * Author: Zluck Solutions
 * Author URI: https://profiles.wordpress.org/zluck
 * Text Domain: ziai-articles
 * Domain Path: /languages
 **/

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

define("ZIAI_FILE_URL", plugin_dir_url(__FILE__));
define("ZIAI_FILE_PATH", plugin_dir_path( __FILE__ ));


include_once('classes/class-admin-menu.php');
include_once('classes/class-article-import.php');
include_once('classes/class-ziai-cron.php');

ZIAI_Modules::ziai_init();
ZIAI_CronJob::ziai_cron_init();