<?php
	public static function cron_init() {
        add_action('wp', array( __CLASS__, 'zl_intercom_cronstarter_activation' ));
		add_filter('cron_schedules', array( __CLASS__, 'zl_intercom_cron_add_minute' ));
		add_action('zl_intercom_cronjobs', array( __CLASS__, 'zl_intercom_function_to_get_articles_using_cron' ));
    }

	// and make sure it's called whenever WordPress loads
	public static function zl_intercom_cronstarter_activation()
	{
		// create a scheduled event (if it does not exist already)
		$cron_start_time = get_option('zl_intercom_cron_start_time');
		if ($cron_start_time == '') {
			self::zl_intercom_cronstarter_deactivate();
			return false;
		}
		$schedule_at = strtotime($cron_start_time);
		if (!wp_next_scheduled('zl_intercom_cronjobs')) {
			wp_schedule_event($schedule_at, 'zl_intercom_cron', 'zl_intercom_cronjobs');
		}
	}

	// unschedule event upon plugin deactivation
	public static function zl_intercom_cronstarter_deactivate()
	{
		// find out when the last event was scheduled
		$timestamp = wp_next_scheduled('zl_intercom_cronjobs');
		// unschedule previous event if any
		wp_unschedule_event($timestamp, 'zl_intercom_cronjobs');
	}

	// add cron interval
	public static function zl_intercom_cron_add_minute($schedules)
	{
		$cron_time = get_option('zl_intercom_cron_time');
		$cron_time = ($cron_time > 0) ? $cron_time : 0;
		if ($cron_time <= 0) {
			self::zl_intercom_cronstarter_deactivate();
			return false;
		}
		$cron_time = (($cron_time * 60) * 60);
		// Adds once every minute to the existing schedules.
		$schedules['zl_intercom_cron'] = array(
			'interval' => $cron_time,
			'display' => __('Zluck Intercom Cron')
		);
		return $schedules;
	}

	// hook that function onto our scheduled event:
	public static function zl_intercom_function_to_get_articles_using_cron()
	{
		// here's the function we'd like to call with our cron job
		$token                      = get_option('zl_intercom_access_token');
		$post_type                  = get_option('zl_post_type_get');
		$category                   = get_option('zl_category_get');
		$taxonomies                 = get_option('zl_taxonomy_get');
		$zl_default_author          = get_option('zl_default_author');
		$arry = array(
			"access_token" => $token,
			"import_post_type" => $post_type,
			"import_author" => $zl_default_author,
			"import_taxonomy" => $taxonomies,
		);
		$importer_article 	= new Intercom_Article_Import_Handler($arry);
		$importer_article->import_intercom_article();
	}
}





