<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	if ( isset($_POST['updateSettings']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		$show_logo = isset($_POST['show_logo']) ? 1 : 0;
		
		$widget_options = get_option('leaguemanager_widget');
		$league_id = $_POST['league_id'];
		$widget_options[$league_id]['table_display'] = isset($_POST['table_display']) ? 1 : 0;
		$widget_options[$league_id]['match_display'] = $_POST['match_display'];
		$widget_options[$league_id]['show_logo'] = isset($_POST['widget_show_logo']) ? 1 : 0;
		$widget_options[$league_id]['match_report'] = isset($_POST['match_report']) ? 1 : 0;
		$widget_options[$league_id]['match_show'] = $_POST['match_show'];
		$widget_options[$league_id]['info'] = $_POST['info'];
		$widget_options[$league_id]['date_format'] = $_POST['date_format'];
		$widget_options[$league_id]['time_format'] = $_POST['time_format'];
		
		update_option( 'leaguemanager_widget', $widget_options );
		
		$this->editLeague( $_POST['league_title'], $_POST['forwin'], $_POST['fordraw'], $_POST['forloss'], $_POST['type'], $_POST['num_match_days'], $show_logo, $_POST['league_id'] );
		$this->printMessage();
	}
	
	$league = $leaguemanager->getLeague( $_GET['league_id'] );
	
	$widget_options = get_option('leaguemanager_widget');
	$settings['widget'] = $widget_options[$league->id];
	if ( $settings['widget']['date_format'] == '' ) $settings['widget']['date_format'] = get_option('date_format');
	
	
	$point_formats = array( '%d:%d', '%d' );
		
	if ( 1 == $league->show_logo && !wp_mkdir_p( $leaguemanager->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
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
				<th scope="row"><label for="type"><?php _e( 'Type', 'leaguemanager' ) ?></label></th>
				<td>
					<select size="1" name="type" id="type">
						<?php foreach ( $leaguemanager->getLeagueTypes() AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $league->type ) echo ' selected="selected"' ?>><?php echo $title ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="forwin"><?php _e( 'Points for win', 'leaguemanager' ) ?></label></th><td><input type="text" name="forwin" id="forwin" value="<?php echo $league->forwin ?>" size="2" />&#160;<input type="text" size="2" name="forwin_extra_time" id="forwin_extra_tim" />&#160;<span class="setting-description"><?php _e('The second field is the points for win after extra time. Leave empty if not needed.', 'leaguemanager') ?></span></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="fordraw"><?php _e( 'Points for draw', 'leaguemanager' ) ?></label></th><td><input type="text" name="fordraw" id="fordraw" value="<?php echo $league->fordraw ?>" size="2" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="forloss"><?php _e( 'Points for loss', 'leaguemanager' ) ?></label></th><td><input type="text" name="forloss" id="forloss" value="<?php echo $league->forloss ?>" size="2" />&#160;<input type="text" size="2" name="forloss_extra_time" id="forloss_extra_tim" />&#160;<span class="setting-description"><?php _e('The second field is the points for loss after extra time. Leave empty if not needed.', 'leaguemanager') ?></span></td>
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
			<tr valign="top">
				<th scope="row"><label for="show_logo"><?php _e( 'Show Logo', 'leaguemanager' ) ?></label></th>
				<td><input type="checkbox" id="show_logo" name="show_logo"<?php if ( 1 == $league->show_logo ) echo ' checked="checked"'; ?> value="1" /></td>
			</tr>
		</table>
		
		<h3><?php _e( 'Widget Settings', 'leaguemanager' ) ?></h3>
		<table class="form-table">
		<tr scope="row">
			<th><label for="match_display"><?php _e( 'Matches','leaguemanager' ) ?></label></th>
			<td>
				<select size="1" name="match_display" id="match_display">
					<option value="-1"<?php  if ( -1 == $settins['widget']['match_display'] ) echo ' selected="selecteed"' ?>><?php _e('Do not show', 'leaguemanager') ?></option>
					<option value="0" <?php if ( 0 == $settings['widget']['match_display'] ) echo ' selected="selecteed"' ?>><?php _e('All', 'leaguemanager') ?></option>			
					<?php for($i = 1; $i <= 10;$i++) : ?>
					<option value="<?php echo $i ?>"<?php if ( $i == $settings['widget']['match_display'] ) echo ' selected="selected"' ?>><?php echo $i ?></option>
					<?php endfor; ?>
				</select>
				<select size="1" name="match_show" id="match_show">
					<option value="1"<?php if ( 1 == $settings['widget']['match_show'] ) echo ' selected="selected"' ?>><?php _e('All Teams', 'leaguemanager') ?></option>
					<option value="2"<?php if ( 2 == $settings['widget']['match_show'] ) echo ' selected="selected"' ?>><?php _e('Only own matches', 'leaguemanager') ?></option>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="table_display"><?php _e( 'Show Table', 'leaguemanager' ) ?></label></th><td><input type="checkbox" name="table_display" id="table_display" value="1" <?php if ( 1 == $settings['widget']['table_display'] ) echo ' checked="checked"' ?>></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="widget_show_logo"><?php _e( 'Show Logo', 'leaguemanager' ) ?></label></th>
			<td><input type="checkbox" id="widget_show_logo" name="widget_show_logo"<?php if ( 1 == $settings['widget']['show_logo'] ) echo ' checked="checked"'; ?> value="1" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="match_report"><?php _e( 'Link to report', 'leaguemanager' ) ?></label></th>
			<td><input type="checkbox" id="match_report" name="match_report"<?php if ( 1 == $settings['widget']['match_report'] ) echo ' checked="checked"'; ?> value="1" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="date_format"><?php _e( 'Date Format' ) ?></label></th><td><input type="text" name="date_format" id="date_format" value="<?php echo $settings['widget']['date_format'] ?>" />&#160;<?php echo date_i18n($settings['widget']['date_format']) ?>
			<p><?php _e('<a href="http://codex.wordpress.org/Formatting_Date_and_Time">Documentation on date formatting</a>. Click "Save Changes" to update sample output.') ?></p></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="time_format"><?php _e( 'Time Format' ) ?></label></th><td><input type="text" name="time_format" id="time_format" value="<?php echo $settings['widget']['time_format'] ?>" />&#160;<?php echo date_i18n($settings['widget']['time_format']) ?><p><?php _e( 'If the Time Format is empty, no time will be displayed in the match list', 'leaguemanager' ) ?></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="info"><?php _e( 'Page', 'leaguemanager' ) ?><label></th><td><?php wp_dropdown_pages(array('name' => 'info', 'selected' => $settings['widget']['info'])) ?></td>
		</tr>
		</table>
		
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<p class="submit"><input type="submit" name="updateSettings" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
<?php endif; ?>
