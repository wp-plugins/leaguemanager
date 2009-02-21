<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	if ( isset($_POST['updateSettings']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');

		$widget_options = get_option('leaguemanager_widget');
		$league_id = $_POST['league_id'];
		$widget_options[$league_id]['table_display'] = array( $_POST['table_display'], $_POST['table_display_logos'] );
		$widget_options[$league_id]['match_display'] = array( $_POST['match_show'], $_POST['match_display_logos'], $_POST['match_display'] );
		$widget_options[$league_id]['match_limit'] = ( $_POST['match_display'] == 'home' ) ? '' : $_POST['match_limit'];
		$widget_options[$league_id]['match_logo'] = 
		$widget_options[$league_id]['match_report'] = isset($_POST['match_report']) ? 1 : 0;
		//$widget_options[$league_id]['info'] = $_POST['info'];
		$widget_options[$league_id]['date_format'] = $_POST['date_format'];
		//$widget_options[$league_id]['time_format'] = $_POST['time_format'];
		
		update_option( 'leaguemanager_widget', $widget_options );
		
		// Set textdomain
		$options = get_option('leaguemanager');
		$options['textdomain'] = $this->getTextdomain($_POST['sport']);
		update_option('leaguemanager', $options);
		
		$point_rule = isset($_POST['forwin']) ? array( 'forwin' => $_POST['forwin'], 'fordraw' => $_POST['fordraw'], 'forloss' => $_POST['forloss'], 'forwin_overtime' => $_POST['forwin'], 'forloss_overtime' => $_POST['forloss'] ) : $_POST['point_rule'];
		$this->editLeague( $_POST['league_title'], $point_rule, $_POST['point_format'], $_POST['sport'], $_POST['num_match_days'], $_POST['team_ranking'], $_POST['league_id'] );
		$this->printMessage();
	}
	
	$league = $leaguemanager->getLeague( $_GET['league_id'] );
	$league->point_rule = maybe_unserialize( $league->point_rule );
	$forwin = $fordraw = $forloss = 0;
	// Manual point rule
	if ( is_array($league->point_rule) ) {
		$forwin = $league->point_rule['forwin'];
		$fordraw = $league->point_rule['fordraw'];
		$forloss = $league->point_rule['forloss'];
		$league->point_rule = 6;
	}
	
	$widget_options = get_option('leaguemanager_widget');
	$settings['widget'] = $widget_options[$league->id];
	if ( $settings['widget']['date_format'] == '' ) $settings['widget']['date_format'] = get_option('date_format');
?>	
	
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'League Preferences', 'leaguemanager' ) ?></p>
			
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
					<select size="1" name="sport" id="sport">
						<?php foreach ( $leaguemanager->getLeagueTypes() AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $league->type ) echo ' selected="selected"' ?>><?php echo $title ?></option>
						<?php endforeach; ?>
					</select>
					<span class="setting-description"><?php printf( __( "Check the <a href='%s'>Documentation</a> for details", 'leaguemanager'), admin_url() . 'admin.php?page=leaguemanager-doc' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="point_rule"><?php _e( 'Point Rule', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="point_rule" id="point_rule" onchange="Leaguemanager.checkPointRule(<?php echo $forwin ?>, <?php echo $fordraw ?>, <?php echo $forloss ?>)">
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
					<select size="1" name="point_format" id="point_format" >
					<?php foreach ( $this->getPointFormats() AS $format ) : ?>
					<option value="<?php echo $format ?>"<?php if ( $format == $league->point_format  ) echo ' selected="selected"'; ?>><?php echo $format ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="team_ranking"><?php _e( 'Team Ranking', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="team_ranking" id="team_ranking" >
						<option value="auto"<?php if ( 'auto' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Automatic', 'leaguemanager' ) ?></option>
						<option value="manual"<?php if ( 'manual' == $league->team_ranking  ) echo ' selected="selected"'; ?>><?php _e( 'Manual', 'leaguemanager' ) ?></option>
					</select>
					&#160;<span class="setting-description"><?php _e( 'Team Ranking via Drag & Drop probably will only work in Firefox', 'leaguemanager' ) ?></span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="num_match_days"><?php _e( 'Number of Match Days', 'leaguemanager' ) ?></label></th>
				<td>
					<input type="text" name="num_match_days" id="num_match_days" value="<?php echo $league->num_match_days ?>" size="2" />
					<?php if ( $league->num_match_days == 0 ) : ?>
					<span class="setting-description error"><?php _e( 'You have to set the number of match days to use this feature.', 'leaguemanager') ?></span>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		
		<h3><?php _e( 'Widget Settings', 'leaguemanager' ) ?></h3>
		<table class="form-table">
		<tr scope="row">
			<th><label for="match_show"><?php _e( 'Matches','leaguemanager' ) ?></label></th>
			<td>
				<select size="1" name="match_show" id="match_show">
					<option value="none"<?php  if ( 'none' == $settins['widget']['match_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Do not show', 'leaguemanager') ?></option>
					<option value="prev_matches"<?php if ( 'prev_matches' == $settings['widget']['match_display'][0] ) echo ' selected="selected"' ?>><?php _e('Last Matches', 'leaguemanager') ?></option>
					<option value="next_matches" <?php if ( 'next_matches' == $settings['widget']['match_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Next Matches', 'leaguemanager') ?></option>
					<option value="all" <?php if ( 'all' == $settings['widget']['match_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Next & Last Matches', 'leaguemanager') ?></option>
				</select>
				<select size="1" name="match_display_logos" id="match_display_logos">
					<option value="1"<?php  if ( 1 == $settings['widget']['match_display'][1] ) echo ' selected="selecteed"' ?>><?php _e('Show Logos', 'leaguemanager') ?></option>
					<option value="0"<?php  if ( 0 == $settings['widget']['match_display'][1] ) echo ' selected="selecteed"' ?>><?php _e("Don't show Logos", 'leaguemanager') ?></option>
				</select><br />
				<select size="1" name="match_display" id="match_display">
					<option value="home"<?php if ( 'home' == $settings['widget']['match_display'][2] ) echo ' selected="selected"' ?>><?php _e('Only own matches', 'leaguemanager') ?></option>
					<option value="all" <?php if ( 'all' == $settings['widget']['match_display'][2] ) echo ' selected="selecteed"' ?>><?php _e('All Teams with Limit ...', 'leaguemanager') ?></option>
				</select>
				<input type="text" name="match_limit" id="match_limit" value="<?php echo $settings['widget']['match_limit'] ?>" size="2" />&#160;<span class="setting-description"><?php _e( 'Leave empty for no limit', 'leaguemanager' ) ?></span>
			</td>
		</tr>
		<!--<tr valign="top">
			<th scope="row"><label for="widget_show_logo"><?php _e( 'Show Logos', 'leaguemanager' ) ?></label></th>
			<td><input type="checkbox" id="widget_show_logo" name="widget_show_logo"<?php if ( $settings['widget']['show_logo'] ) echo ' checked="checked"'; ?> value="1" /></td>
		</tr>-->
		<tr valign="top">
			<th scope="row"><label for="table_display"><?php _e( 'Standings', 'leaguemanager' ) ?></label></th>
			<td>
				<select size="1" name="table_display" id="table_display">
					<option value="none"<?php  if ( 'none' == $settings['widget']['table_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Do not show', 'leaguemanager') ?></option>
					<option value="compact"<?php  if ( 'compact' == $settings['widget']['table_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Compact Version', 'leaguemanager') ?></option>
					<option value="extend"<?php  if ( 'extend' == $settings['widget']['table_display'][0] ) echo ' selected="selecteed"' ?>><?php _e('Extend Version', 'leaguemanager') ?></option>
					
				</select>
				<select size="1" name="table_display_logos" id="table_display_logos">
					<option value="1"<?php  if ( 1 == $settings['widget']['table_display'][1] ) echo ' selected="selecteed"' ?>><?php _e('Show Logos', 'leaguemanager') ?></option>
					<option value="0"<?php  if ( 0 == $settings['widget']['table_display'][1] ) echo ' selected="selecteed"' ?>><?php _e("Don't show Logos", 'leaguemanager') ?></option>
			</td>
			<!--<td><input type="checkbox" name="table_display" id="table_display" value="1" <?php if ( 1 == $settings['widget']['table_display'] ) echo ' checked="checked"' ?>></td>-->
		</tr>
		<tr valign="top">
			<th scope="row"><label for="match_report"><?php _e( 'Link to report', 'leaguemanager' ) ?></label></th>
			<td><input type="checkbox" id="match_report" name="match_report"<?php if ( 1 == $settings['widget']['match_report'] ) echo ' checked="checked"'; ?> value="1" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="date_format"><?php _e( 'Date Format' ) ?></label></th><td><input type="text" name="date_format" id="date_format" value="<?php echo $settings['widget']['date_format'] ?>" />&#160;<?php echo date_i18n($settings['widget']['date_format']) ?>
			<p><?php _e('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date formatting</a>. Click "Save Changes" to update sample output.') ?></p></td>
		</tr>
		<!--
		<tr valign="top">
			<th scope="row"><label for="time_format"><?php _e( 'Time Format' ) ?></label></th><td><input type="text" name="time_format" id="time_format" value="<?php echo $settings['widget']['time_format'] ?>" />&#160;<?php echo date_i18n($settings['widget']['time_format']) ?><p><?php _e( 'If the Time Format is empty, no time will be displayed in the match list', 'leaguemanager' ) ?></td>
		</tr>
		-->
		<!--<tr valign="top">
			<th scope="row"><label for="info"><?php _e( 'Page', 'leaguemanager' ) ?><label></th><td><?php wp_dropdown_pages(array('name' => 'info', 'selected' => $settings['widget']['info'])) ?></td>
		</tr>-->
		</table>
		
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<p class="submit"><input type="submit" name="updateSettings" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
<?php endif; ?>
