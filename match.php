<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :

	if ( isset( $_GET['edit'] ) ) {
		$form_title = 'Edit Match';
						
		$match = $leaguemanager->getMatches( "id = '".$_GET['edit']."'" );
								
		if ( $match ) {
			$league_id = $match[0]->league_id;
			$match_day = $match[0]->day;
			$match_month = $match[0]->month;
			$match_year = $match[0]->year;
			$begin_hour = $match[0]->hour;
			$begin_minutes = $match[0]->minutes;
			$location = $match[0]->location;
			$home_team = $match[0]->home_team;
			$away_team = $match[0]->away_team;
			$match_id = $match[0]->id;
	
			$league = $leaguemanager->getLeagues( $league_id );
			$league_title = $league['title'];
		}
	} else {
		$form_title = 'Add Match';
								
		$league_id = $_GET['league_id'];
		$league = $leaguemanager->getLeagues( $league_id );
		$league_title = $league['title'];
		$match_day = ''; $match_month = ''; $match_year = date("Y"); $home_team = ''; $away_team = '';
		$begin_hour = ''; $begin_minutes = ''; $location = ''; $match_id = '';
	}
	?>
	
	<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php _e( $form_title, 'leaguemanager' ) ?></p>
	<div class="narrow">
		<h2><?php _e( $form_title,'leaguemanager' ) ?></h2>
		
		<form class="leaguemanager" action="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<label for="date"><?php _e('Date', 'leaguemanager') ?>:</label>
			<select size="1" name="match_day">
			<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
				<?php if ( $day == $match_day ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $day ?>"<?php echo $selected ?>><?php echo $day ?></option>
			<?php endfor; ?>
			</select>
			<select size="1" name="match_month">
			<?php foreach ( $leaguemanager->months AS $key => $month ) : ?>
				<?php if ( $key == $match_month ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $month ?></option>
			<?php endforeach; ?>
			</select>
			<select size="1" name="match_year">
			<?php for ( $year = date("Y"); $year <= date("Y")+1; $year++ ) : ?>
				<?php if ( $year == $match_year ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $year ?>"<?php echo $selected ?>><?php echo $year ?></option>
			<?php endfor; ?>
			</select>
			<br />
			
			<?php $teams = $leaguemanager->getTeams( "league_id = '".$league_id."'" ); ?>
			<label for="home_team"><?php _e( 'Home', 'leaguemanager' ) ?>:</label>
			<select size="1" name="home_team" id="home_team">
			<?php foreach ( $teams AS $team ) : ?>
				<?php if ( $team->id == $home_team ) $selected = 'selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $team->id ?>"<?php echo $selected?>><?php echo $team->title ?></option>
			<?php endforeach; ?>
			</select><br />
			<label for="away_team"><?php _e( 'Guest', 'leaguemanager' ) ?>:</label>
			<select size="1" id="away_team" name="away_team">
			<?php foreach ( $teams AS $team ) : ?>
				<?php if ( $team->id == $away_team ) $selected = 'selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $team->id ?>"<?php echo $selected?>><?php echo $team->title ?></option>
			<?php endforeach; ?>
			</select><br />
		
			<label for="location"><?php _e( 'Location','leaguemanager' ) ?></label><input type="text" name="location" id="location" size="20" value="<?php echo $location ?>" size="30" /><br />
			<label for="begin"><?php _e( 'Begin','leaguemanager' ) ?></label>
			<select size="1" name="begin_hour">
			<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
				<?php if ( $hour == $begin_hour ) $selected = 'selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $hour ?>"<?php echo $selected ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
			<?php endfor; ?>
			</select>
			<select size="1" name="begin_minutes">
			<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
				<?php if ( $minute == $begin_minutes ) $selected = 'selected="selected"'; else $selected = ''; ?>
				<?php if ( 0 == $minute % 15 ) : ?>
				<option value="<?php echo $minute ?>"<?php echo $selected ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
				<?php endif; ?>
			<?php endfor; ?>
			</select>
			
			<input type="hidden" name="match_id" value="<?php echo $match_id ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php _e( $form_title,'leaguemanager' ) ?> &raquo;" class="button" /></p>
		</form>
	</div>
	</div> 
<?php endif; ?>