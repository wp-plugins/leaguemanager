<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	
 	if ( isset($_POST['updateLeague']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		if ( '' == $_POST['league_id'] ) {
			$return_message = $leaguemanager->addLeague( $_POST['league_title'] );
		} else {
			$return_message = $leaguemanager->editLeague( $_POST['league_title'], $_POST['forwin'], $_POST['fordraw'], $_POST['forloss'], $_POST['match_calendar'], $_POST['type'], $_POST['league_id'] );
		}
		echo '<div id="message" class="updated fade"><p><strong>'.__( $return_message, 'leaguemanager' ).'</strong></p></div>';
	}
	
	if ( isset( $_GET['edit'] ) ) {
		$league_id = $_GET['edit'];
		$league = $leaguemanager->getLeagues( $league_id );
		$form_title = 'League Preferences';
		$league_title = $league['title'];
		
		$league_preferences = $leaguemanager->getLeaguePreferences( $league_id );
	} else {
		$league_id = $_GET['league_id']; $form_title = 'Add League'; $league_title = '';
	}
	
	$match_calendar = array( 1 => "Show All", 2 => "Only own matches" );
	$league_types = array( 1 => "Gymnastics", 2 => "Ball Game", 3 => "Other" );
	?>	
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap" style="margin-bottom: 1.5em;">
			<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php _e( $form_title, 'leaguemanager' ) ?></p>
			
			<h2><?php _e( $form_title, 'leaguemanager' ) ?></h2>
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
							<option value="<?php echo $id ?>"<?php echo $selected ?>><?php _e( $title, 'leaguemanager' ) ?></option>
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
								<option value="<?php echo $id ?>"<?php echo $selected ?>><?php _e( $title, 'leaguemanager' ) ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
