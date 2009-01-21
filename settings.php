<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
 	if ( isset($_POST['updateLeague']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		$show_logo = isset($_POST['show_logo']) ? 1 : 0;
		$message = $leaguemanager->editLeague( $_POST['league_title'], $_POST['forwin'], $_POST['fordraw'], $_POST['forloss'], $_POST['match_calendar'], $_POST['type'], $_POST['num_match_days'], $show_logo, $_POST['league_id'] );
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
		
	if ( 1 == $league_preferences->show_logo && !wp_mkdir_p( $leaguemanager->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
	?>	
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap">
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
							<option value="<?php echo $id ?>"<?php if ( $id == $league_preferences->match_calendar ) echo ' selected="selected"' ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
						<p><?php _e('Only used for match display in widget', 'leaguemanager' ) ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="type"><?php _e( 'Type', 'leaguemanager' ) ?></label></th>
					<td>
						<select size="1" name="type" id="type">
							<?php foreach ( $league_types AS $id => $title ) : ?>
								<option value="<?php echo $id ?>"<?php if ( $id == $league_preferences->type ) echo ' selected="selected"' ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="num_match_days"><?php _e( 'Number of Match Days', 'leaguemanager' ) ?></label></th>
					<td><input type="text" name="num_match_days" id="num_match_days" value="<?php echo $league_preferences->num_match_days ?>" size="2" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="show_logo"><?php _e( 'Show Logo', 'leaguemanager' ) ?></label></th>
					<td><input type="checkbox" id="show_logo" name="show_logo"<?php if ( 1 == $league_preferences->show_logo ) echo ' checked="checked"'; ?> value="1" /></td>
				</tr>
			</table>
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
