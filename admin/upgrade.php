<?php
/**
 * leaguemanager_upgrade() - update routine for older version
 * 
 * @return Success Message
 */
function leaguemanager_upgrade() {
	global $wpdb, $leaguemanager, $leaguemanager_loader;
	
	$options = get_option( 'leaguemanager' );
	$installed = $options['dbversion'];
	
	echo __('Upgrade database structure...', 'leaguemanager');
	$wpdb->show_errors();

	$leaguemanager_loader->install();

	if (version_compare($options['version'], '2.0', '<')) {
		/*
		* Drop deprecated tables
		*/
		$wpdb->query( "DROP TABLE `wp_leaguemanager_leaguemeta`" );
		$wpdb->query( "DROP TABLE `wp_leaguemanager_teammeta`" );
		
		/*
		* Update leagues table
		*/
		$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
		if ( !in_array('forwin', $lm_cols) )	
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `forwin` tinyint( 4 ) NOT NULL default '2';" );
		if ( !in_array('fordraw', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `fordraw` tinyint( 4 ) NOT NULL default '1';" );
		if ( !in_array('forloss', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `forloss` tinyint( 4 ) NOT NULL default '0';" );
		if ( !in_array('match_calendar', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `match_calendar` tinyint( 1 ) NOT NULL default '1';" );
		if ( !in_array('type', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `type` tinyint( 1 ) NOT NULL default '2';" );
		if ( !in_array('active', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `active` tinyint( 1 ) NOT NULL default '1';" );
	
		
		/*
		* Update Match table
		*/
		$wpdb->query( "RENAME TABLE `wp_leaguemanager_competitions` TO `wp_leaguemanager_matches`" ); 
		
		$wpdb->query( "ALTER TABLE  {$wpdb->leaguemanager_matches} DROP `competitor`" );
		$wpdb->query( "ALTER TABLE  {$wpdb->leaguemanager_matches} DROP `home`" );
		
		$lm_matches_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_matches}" );
		if ( !in_array('home_team', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_team` int( 11 ) NOT NULL;" );
		if ( !in_array('away_team', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_team` int( 11 ) NOT NULL;" );
		if ( !in_array('home_apparatus_points', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_apparatus_points` tinyint( 4 ) NULL default NULL;" );
		if ( !in_array('away_apparatus_points', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_apparatus_points` tinyint( 4 ) NULL default NULL;" );
		if ( !in_array('home_points', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `home_points` tinyint( 4 ) NULL default NULL;" );
		if ( !in_array('away_points', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `away_points` tinyint( 4 ) NULL default NULL;" );
		if ( !in_array('winner_id', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `winner_id` int( 11 ) NOT NULL;" );
		if ( !in_array('loser_id', $lm_matches_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `loser_id` int( 11 ) NOT NULL;" );
	}

	/*
	* Upgrade from 2.0 to 2.1p_
	*/
	if (version_compare($options['version'], '2.0', '<')) {
		$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
		if ( in_array('date_format', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `date_format`" );
		
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} CHANGE `home_teams_only` `match_calendar` TINYINT( 1 ) NOT NULL DEFAULT '1'" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} CHANGE `gymnastics` `type` TINYINT( 1 ) NOT NULL DEFAULT '2'" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} CHANGE `home_apparatus_points` `home_apparatus_points` TINYINT( 4 ) NULL DEFAULT NULL , 
		CHANGE `away_apparatus_points` `away_apparatus_points` TINYINT( 4 ) NULL DEFAULT NULL ,
		CHANGE `home_points` `home_points` TINYINT( 4 ) NULL DEFAULT NULL ,
		CHANGE `away_points` `away_points` TINYINT( 4 ) NULL DEFAULT NULL" );
	}

	/*
	* Upgrade to Version 2.3.1
	*/
	if (version_compare($options['version'], '2.3.1', '<')) {
		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "CONVERT TO CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} $charset_collate" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} $charset_collate" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} $charset_collate" );
	}

	/*
	* Upgrade to 2.4.1
	*/
	if (version_compare($options['version'], '2.4.1', '<')) {
		$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager}" );
		if ( !in_array('show_logo', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `show_logo` TINYINT( 1 ) NOT NULL" );
		
		$lm_teams_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_teams}" );
		if ( !in_array('logo', $lm_teams_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `logo` VARCHAR( 50 ) NOT NULL AFTER `short_title`" );
	}

	/*
	* Upgrade to 2.5
	*/
	if (version_compare($options['version'], '2.5', '<')) {
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `match_day` TINYINT( 4 ) NOT NULL AFTER `away_team`" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `num_match_days` TINYINT( 4 ) NOT NULL AFTER `type`" );
			
		/**
		* Copy Logos to new image directory and delete old one
		*/
		$dir_src = WP_CONTENT_DIR.'/leaguemanager';
		$dir_handle = opendir($dir_src);
		if ( wp_mkdir_p( $leaguemanager->getImagePath() ) ) {
			while( $file = readdir($dir_handle) ) {
				if( $file!="." && $file!=".." ) {
					if ( copy ($dir_src."/".$file, $leaguemanager->getImagePath()."/".$file) )
						unlink($dir_src."/".$file);
				}
			}
			
			@rmdir($dir_src);
		}
	}

	/*
	* Upgrade to 2.5.1
	*/
	if (version_compare($options['version'], '2.5.1', '<')) {
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `match_calendar`" );
	}
	
	
	/*
	* Upgrade to 2.6.3
	*/
	/*
	if (version_compare($installed, '2.6.3', '<')) {
		$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_matches}" );
		if ( !in_array('`post_id`', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `post_id` int( 11 ) NOT NULL" );
			
		$lm_cols = $wpdb->get_col( "SHOW COLUMNS FROM {$wpdb->leaguemanager_teams}" );
		if ( !in_array('`points_plus`', $lm_cols) )
			$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points_plus` int( 11 ) NOT NULL, ADD `points_minus` int( 11 ) NOT NULL, ADD `points2_plus` int( 11 ) NOT NULL, ADD `points2_minus` int( 11 ) NOT NULL, ADD `done_matches` int( 11 ) NOT NULL, ADD `won_matches` int( 11 ) NOT NULL, ADD `draw_matches` int( 11 ) NOT NULL, ADD `lost_matches` int( 11 ) NOT NULL" );
	}
	*/
	
	/*
	* Upgrade to 2.6.6
	*/
	if (version_compare($installed, '2.6.6', '<')) {
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `post_id` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points_plus` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points_minus` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points2_plus` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `points2_minus` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `done_matches` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `won_matches` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `draw_matches` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams}, ADD `lost_matches` int( 11 ) NOT NULL" );
	}
	
	/*
	* Upgrade to 2.7
	*/
	if (version_compare($installed, '2.7', '<')) {
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `forwin`" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `fordraw`" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `forloss`" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} DROP `match_calendar`" );
			
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD point_rule LONGTEXT NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager} ADD `point_format` varchar( 255 ) NOT NULL" );
			
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `overtime` tinyint( 1 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} ADD `points2` LONGTEXT  NOT NULL" );
		
		if ( $matches = $wpdb->get_results( "SELECT * FROM {$wpdb->leaguemanager_matches}" ) ) {
			$points2 = array();
			foreach ( $matches AS $match ) {
				$points2[] = array( 'plus' => $match->home_apparatus_points, 'minus' => $match->away_appratus_points );
					
				$wpdb->query( "UPDATE {$wpdb->leaguemanager_matches} SET `points2` = '".maybe_serialize($points2)."' WHERE id = '".$match->id."'" );
			}
		}
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `diff` int( 11 ) NOT NULL" );
		$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_teams} ADD `website` varchar( 255 ) NOT NULL" );
		//$wpdb->query( "ALTER TABLE {$wpdb->leaguemanager_matches} DROP `home_apparatus_points`, DROP `away_apparatus_points`" );
	}
	

	/*
	* Update version and dbversion
	*/
	$options['dbversion'] = LEAGUEMANAGER_DBVERSION;
	
	update_option('leaguemanager', $options);
	echo __('finished', 'leaguemanager') . "<br />\n";
	$wpdb->hide_errors();
	return;
}


/**
* leaguemanager_upgrade_page() - This page showsup , when the database version doesn't fit to the script LEAGUEMANAGER_DBVERSION constant.
* 
* @return Upgrade Message
*/
function leaguemanager_upgrade_page()  {	
	$filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];

	if ($_GET['upgrade'] == 'now') {
		leaguemanager_do_upgrade($filepath);
		return;
	}
?>
	<div class="wrap">
		<h2><?php _e('Upgrade LeagueManager', 'leaguemanager') ;?></h2>
		<p><?php _e('Your database for LeagueManager is out-of-date, and must be upgraded before you can continue.', 'leaguemanager'); ?>
		<p><?php _e('The upgrade process may take a while, so please be patient.', 'leaguemanager'); ?></p>
		<h3><a class="button" href="<?php echo $filepath;?>&amp;upgrade=now"><?php _e('Start upgrade now', 'leaguemanager'); ?>...</a></h3>
	</div>
	<?php
}


/**
 * leaguemanager_do_upgrade() - Proceed the upgrade routine
 * 
 * @param mixed $filepath
 * @return void
 */
function leaguemanager_do_upgrade($filepath) {
	global $wpdb;
?>
<div class="wrap">
	<h2><?php _e('Upgrade LeagueManager', 'leaguemanager') ;?></h2>
	<p><?php leaguemanager_upgrade();?></p>
	<p><?php _e('Upgrade sucessfull', 'leaguemanager') ;?></p>
	<h3><a class="button" href="<?php echo $filepath;?>"><?php _e('Continue', 'leaguemanager'); ?>...</a></h3>
</div>
<?php
}


?>