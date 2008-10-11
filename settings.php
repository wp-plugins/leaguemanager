<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	
 	if ( isset($_POST['updateLeague']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		if ( '' == $_POST['league_id'] ) {
			$return_message = $leaguemanager->addLeague( $_POST['league_title'] );
		} else {
			$home_teams_only = isset( $_POST['home_teams_only']) ? 1 : 0;
			$gymnastics = isset( $_POST['gymnastics']) ? 1 : 0;
			
			$return_message = $leaguemanager->editLeague( $_POST['league_title'], $_POST['date_format'], $_POST['forwin'], $_POST['fordraw'], $_POST['forloss'], $home_teams_only, $gymnastics, $_POST['league_id'] );
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
	?>	
	<form class="leaguemanager" action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap" style="margin-bottom: 1.5em;">
			<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php _e( $form_title, 'leaguemanager' ) ?></p>
			
			<h2><?php _e( $form_title, 'leaguemanager' ) ?></h2>
			<label for="league_title"><?php _e( 'Title', 'leaguemanager' ) ?>:</label><input type="text" name="league_title" id="league_title" value="<?php echo $league_title ?>" size="30" /><br />
			<label for="date_format"><?php _e( 'Date Format', 'leaguemanager' ) ?>:</label>
			<select size="1" name="date_format" id="date_format">
				<?php if ( $league_preferences->date_format == '%e.%c' ) { $selected[1] = 'selected="selected"'; $selected[2] = ''; } elseif ( $league_preferences->date_format == '%m/%d' ) { $selected[1] = ''; $selected[2] = 'selected="selected"'; } ?>
				<option value="%e.%c."<?php echo $selected[1] ?>>dd.mm</option>
				<option value="%m/%d"<?php echo $selected[2] ?>>mm/dd</option>
			</select><br />
			<label for="forwin"><?php _e( 'Points for win', 'leaguemanager' ) ?>:</label><input type="text" name="forwin" id="forwin" value="<?php echo $league_preferences->forwin ?>" size="2" /><br />
			<label for="fordraw"><?php _e( 'Points for draw', 'leaguemanager' ) ?>:</label><input type="text" name="fordraw" id="fordraw" value="<?php echo $league_preferences->fordraw ?>" size="2" /><br />
			<label for="forloss"><?php _e( 'Points for loss', 'leaguemanager' ) ?>:</label><input type="text" name="forloss" id="forloss" value="<?php echo $league_preferences->forloss ?>" size="2" /><br />
			
			<?php $selected = ( 1 == $league_preferences->home_teams_only ) ? 'checked="checked"' : '' ?>
			<label for="home_teams_only"><?php _e( 'Show matches of Home Teams Only', 'leaguemanager' ) ?>:</label>
			<input type="checkbox" name="home_teams_only" id="home_teams_only" value="1"<?php echo $selected ?> /><br />
			
			<?php $selected = ( $leaguemanager->isGymnasticsLeague( $league_id ) ) ? 'checked="checked"' : '' ?>
			<label for="gymnastics"><?php _e( 'Gymnastics League?', 'leaguemanager' ) ?></label><input type="checkbox" name="gymnastics" id="gymnastics" value="1"<?php echo $selected ?> /><br />
			
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( 'Save Preferences', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
