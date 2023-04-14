<?php
class ZIAI_CronJob {
	public static function ziai_cron_init() {
        add_action('wp', array( __CLASS__, 'ziai_cronstarter_activation' ));
		add_filter('cron_schedules', array( __CLASS__, 'ziai_cron_add_minute' ));
		add_action('zl_ziai_cronjobs', array( __CLASS__, 'ziai_get_articles_using_cron' ));
    }

	// and make sure it's called whenever WordPress loads
	public static function ziai_cronstarter_activation()
	{
		// create a scheduled event (if it does not exist already)
		$cron_start_time = get_option('ziai_cron_start_time');
		if ($cron_start_time == '') {
			self::ziai_cronstarter_deactivate();
			return false;
		}
		$schedule_at = strtotime($cron_start_time);
		if (!wp_next_scheduled('zl_ziai_cronjobs')) {
			wp_schedule_event($schedule_at, 'zl_ziai_cron', 'zl_ziai_cronjobs');
		}
	}

	// unschedule event upon plugin deactivation
	public static function ziai_cronstarter_deactivate()
	{
		// find out when the last event was scheduled
		$timestamp = wp_next_scheduled('zl_ziai_cronjobs');
		// unschedule previous event if any
		wp_unschedule_event($timestamp, 'zl_ziai_cronjobs');
	}

	// add cron interval
	public static function ziai_cron_add_minute($schedules)
	{
		$cron_time = get_option('ziai_cron_time');
		$cron_time = ($cron_time > 0) ? $cron_time : 0;
		if ($cron_time <= 0) {
			self::ziai_cronstarter_deactivate();
			return false;
		}
		$cron_time = (($cron_time * 60) * 60);
		// Adds once every minute to the existing schedules.
		$schedules['zl_ziai_cron'] = array(
			'interval' => $cron_time,
			'display' => __('Zluck ZIAI Cron')
		);
		return $schedules;
	}

	// hook that function onto our scheduled event:
	public static function ziai_get_articles_using_cron()
	{
        $ZIAI_Modules = new ZIAI_Modules();
		$arry = $ZIAI_Modules->get_ziai_options_array();
        $importer_article 	= new ZIAI_Handler($arry);
		$importer_article->sync_articles();
	}
}





