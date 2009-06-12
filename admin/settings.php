<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	$options = get_option('leaguemanager');
	$league = $leaguemanager->getCurrentLeague();
	if ( isset($_POST['updateSettings']) ) {
		check_admin_referer('leaguemanager_manage-league-options');

		$settings = (array)$_POST['settings'];

		// Set textdomain
		$options['textdomain'] = (string)$settings['sport'];
		update_option('leaguemanager', $options);
		
		if ( isset($_POST['forwin']) )
			$settings['point_rule'] = array( 'forwin' => $_POST['forwin'], 'fordraw' => $_POST['fordraw'], 'forloss' => $_POST['forloss'], 'forwin_overtime' => $_POST['forwin'], 'forloss_overtime' => $_POST['forloss'] );

		$this->editLeague( $_POST['league_title'], $settings, $_POST['league_id'] );
		$this->printMessage();
	}
	
	$options = get_option('leaguemanager');
	$league = $leaguemanager->getLeague( $_GET['league_id'] );

	$forwin = $fordraw = $forloss = 0;
	// Manual point rule
	if ( is_array($league->point_rule) ) {
		$forwin = $league->point_rule['forwin'];
		$fordraw = $league->point_rule['fordraw'];
		$forloss = $league->point_rule['forloss'];
		$league->point_rule = 6;
	}
?>

<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'League Preferences', 'leaguemanager' ) ?></p>
			
	<h2><?php _e( 'League Preferences', 'leaguemanager' ) ?></h2>
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
			
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="league_title"><?php _e( 'Title', 'leaguemanager' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="<?php echo $league->title ?>" size="30" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="sport"><?php _e( 'Sport', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[sport]" id="sport">
						<?php foreach ( $leaguemanager->getLeagueTypes() AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $league->sport ) echo ' selected="selected"' ?>><?php echo $title ?></option>
						<?php endforeach; ?>
					</select>
					<span class="setting-description"><?php printf( __( "Check the <a href='%s'>Documentation</a> for details", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="point_rule"><?php _e( 'Point Rule', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[point_rule]" id="point_rule" onchange="Leaguemanager.checkPointRule(<?php echo $forwin ?>, <?php echo $fordraw ?>, <?php echo $forloss ?>)">
					<?php foreach ( $this->getPointRules() AS $id => $point_rule ) : ?>
					<option value="<?php echo $id ?>"<?php if ( $id == $league->point_rule ) echo ' selected="selected"'; ?>><?php echo $point_rule ?></option>
					<?php endforeach; ?>
					</select>
					<span class="setting-description"><?php printf( __("For details on point rules see the <a href='%s'>Documentation</a>", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
					<div id="point_rule_manual" style="display: block;">
					<?php if ( $league->point_rule == 6 ) : ?>
						<div id="point_rule_manual_content">
							<input type='text' name='forwin' id='forwin' value='<?php echo $forwin ?>' size='2' />
							<input type='text' name='fordraw' id='fordraw' value='<?php echo $fordraw ?>' size='2' />
							<input type='text' name='forloss' id='forloss' value='<?php echo $forloss ?>' size='2' />
							&#160;<span class='setting-description'><?php _e( 'Order: Forwin, Fordraw, Forloss', 'leaguemanager' ) ?></span>
						</div>
					<?php endif; ?>
					</div>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="point_format"><?php _e( 'Point Format', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[point_format]" id="point_format" >
					<?php foreach ( $this->getPointFormats() AS $format ) : ?>
					<option value="<?php echo $format ?>"<?php if ( $format == $league->point_format  ) echo ' selected="selected"'; ?>><?php echo $format ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="team_ranking"><?php _e( 'Team Ranking', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[team_ranking]" id="team_ranking" >
						<option value="auto"<?php if ( 'auto' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Automatic', 'leaguemanager' ) ?></option>
						<option value="manual"<?php if ( 'manual' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Manual', 'leaguemanager' ) ?></option>
					</select>
					&#160;<span class="setting-description"><?php _e( 'Team Ranking via Drag & Drop probably will only work in Firefox', 'leaguemanager' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="mode"><?php _e( 'Mode', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[mode]" id="mode">
					<?php foreach ( $this->getModes() AS $id => $mode ) : ?>
						<option value="<?php echo $id ?>"<?php if ( $id == $league->mode ) echo ' selected="selected"' ?>><?php echo $mode ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign"top">
				<th scope="row"><label for="upload_dir"><?php _e( 'Upload Directory', 'leaguemanager' ) ?></label></th>
				<td><input type="text" size="40" name="settings[upload_dir]" id="upload_dir" value="<?php echo $league->upload_dir ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="default_start_time"><?php _e( 'Default Match Start Time', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="settings[default_match_start_time][hour]">
					<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
						<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $hour, $league->default_match_start_time['hour'] ) ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
					<?php endfor; ?>
					</select>
					<select size="1" name="settings[default_match_start_time][minutes]">
					<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
						<?php if ( 0 == $minute % 5 && 60 != $minute ) : ?>
						<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php selected( $minute, $league->default_match_start_time['minutes'] ) ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
					<?php endif; ?>
					<?php endfor; ?>
					</select>
				</td>
			</tr>

			<?php do_action( 'league_settings_'.$league->sport, $league ); ?> 
			<?php do_action( 'league_settings_'.$league->mode, $league ); ?> 
			<?php do_action( 'league_settings', $league ); ?> 
		</table>
		
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<p class="submit"><input type="submit" name="updateSettings" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>



<?php endif; ?>
