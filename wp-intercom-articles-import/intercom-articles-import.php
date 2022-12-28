<?php
/**

 * Plugin Name: WP Intercom Articles Importer
 * Plugin URI: https://www.zluck.com/
 * Description: This is the Intercom plugin, that will collect Articles from Intercom services.
 * Version: 1.0.0
 * Author: Zluck
 * Author URI: https://zluck.com/
 **/

if(!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); }

define("INTERCOM_FILE_URL", plugin_dir_url(__FILE__));
define("INTERCOM_FILE_PATH", plugin_dir_path( __FILE__ ));


include_once('classes/class-admin-menu.php');
include_once('classes/class-article-import.php');
include_once('classes/class-intercom-cron.php');

ZL_Intercom_Article_Import_Modules::init();
ZLIntercom_Article_Import_CronJob::cron_init();