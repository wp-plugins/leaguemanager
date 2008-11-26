<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	
 	if ( isset($_POST['updateLeague']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		$show_logo = isset($_POST['show_logo']) ? 1 : 0;
		$message = $leaguemanager->editLeague( $_POST['league_title'], $_POST['forwin'], $_POST['fordraw'], $_POST['forloss'], $_POST['match_calendar'], $_POST['type'], array("headers" => $_POST['color_headers'], "rows" => array($_POST['color_rows'], $_POST['color_rows_alt'])), $show_logo, $_POST['league_id'] );
		echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	}
	
	if ( isset( $_GET['edit'] ) ) {
		$league_id = $_GET['edit'];
		$league = $leaguemanager->getLeagues( $league_id );
		$form_title = __( 'League Preferences', 'leaguemanager' );
		$league_title = $league['title'];
		
		$league_preferences = $leaguemanager->getLeaguePreferences( $league_id );
	}
	
	$match_calendar = array( 1 => __('Show All', 'leaguemanager'), 2 => __('Only own matches', 'leaguemanager') );
	$league_types = array( 1 => __('Gymnastics', 'leaguemanager'), 2 => __('Ball Game', 'leaguemanager'), 3 => __('Other', 'leaguemanager') );
	?>	
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap" style="margin-bottom: 1.5em;">
			<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php echo $form_title ?></p>
			
			<h2><?php echo $form_title ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="league_title"><?php _e( 'Title', 'leaguemanager' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="<?php echo $league_title ?>" size="30" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="forwin"><?php _e( 'Points for win', 'leaguemanager' ) ?></label></th><td><input type="text" name="forwin" id="forwin" value="<?php echo $league_preferences->forwin ?>" size="2" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fordraw"><?php _e( 'Points for draw', 'leaguemanager' ) ?></label></th><td><input type="text" name="fordraw" id="fordraw" value="<?php echo $league_preferences->fordraw ?>" size="2" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="forloss"><?php _e( 'Points for loss', 'leaguemanager' ) ?></label></th><td><input type="text" name="forloss" id="forloss" value="<?php echo $league_preferences->forloss ?>" size="2" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="match_calendar"><?php _e( 'Match Plan', 'leaguemanager' ) ?></label></th>
					<td>
						<select size="1" name="match_calendar" id="match_calendar">
							<?php foreach ( $match_calendar AS $id => $title ) : ?>
							<?php $selected = ( $id == $league_preferences->match_calendar ) ? $selected = 'selected="selected"' : ''; ?>
							<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="type"><?php _e( 'Type', 'leaguemanager' ) ?></label></th>
					<td>
						<select size="1" name="type" id="type">
							<?php foreach ( $league_types AS $id => $title ) : ?>
								<?php $selected = ( $id == $league_preferences->type ) ? $selected = 'selected="selected"' : ''; ?>
								<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="show_logo"><?php _e( 'Show Logo', 'leaguemanager' ) ?></label></th>
					<td><input type="checkbox" id="show_logo" name="show_logo"<?php if ( 1 == $league_preferences->show_logo ) echo ' checked="checked"'; ?> value="1" /></td>
				</tr>
			</table>

			<h3><?php _e( 'Color Scheme', 'leaguemanager' ) ?></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="color_headers"><?php _e( 'Table Headers' ) ?></label></th><td><input type="text" name="color_headers" id="color_headers" value="<?php echo $league_preferences->colors['headers'] ?>" size="10" /><a href="#" class="colorpicker" onClick="cp.select(document.forms[0].color_headers,'pick_color_headers'); return false;" name="pick_color_headers" id="pick_color_headers">&#160;&#160;&#160;</a></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="color_rows"><?php _e( 'Table Rows' ) ?></label></th>
					<td>
						<p class="table_rows"><input type="text" name="color_rows" id="color_rows" value="<?php echo $league_preferences->colors['rows'][0] ?>" size="10" /><a href="#" class="colorpicker" onClick="cp.select(document.forms[0].color_rows,'pick_color_rows'); return false;" name="pick_color_rows" id="pick_color_rows">&#160;&#160;&#160;</a></p>
						<p class="table_rows"><input type="text" name="color_rows_alt" id="color_rows_alt" value="<?php echo $league_preferences->colors['rows'][1] ?>" size="10" /><a href="#" class="colorpicker" onClick="cp.select(document.forms[0].color_rows_alt,'pick_color_rows_alt'); return false;" name="pick_color_rows_alt" id="pick_color_rows_alt">&#160;&#160;&#160;</a></p>
					</td>
				</tr>
			</table>
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
	<script language="javascript">
		syncColor('pick_color_headers', 'color_headers', document.getElementById('color_headers').value);
		syncColor('pick_color_rows', 'color_rows', document.getElementById('color_rows').value);
		syncColor('pick_color_rows_alt', 'color_rows_alt', document.getElementById('color_rows_alt').value);
	</script>
<?php endif; ?>
