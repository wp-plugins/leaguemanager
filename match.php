<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :

	if ( isset( $_GET['edit'] ) ) {
		$form_title = __( 'Edit Match', 'leaguemanager' );
						
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
			
			$max_matches = 1;
		}
	} else {
		$form_title = __( 'Add Match', 'leaguemanager' );
								
		$league_id = $_GET['league_id'];
		$league = $leaguemanager->getLeagues( $league_id );
		$league_title = $league['title'];
		$match_day = ''; $match_month = ''; $match_year = date("Y"); $home_team = ''; $away_team = '';
		$begin_hour = ''; $begin_minutes = ''; $location = ''; $match_id = ''; $max_matches = 15;
	}
	?>
	
	<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form class="leaguemanager" action="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<label for="date" class="date"><?php _e('Date', 'leaguemanager') ?>:</label>
			<select size="1" name="match_day" class="date">
			<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
				<?php if ( $day == $match_day ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $day ?>"<?php echo $selected ?>><?php echo $day ?></option>
			<?php endfor; ?>
			</select>
			<select size="1" name="match_month" class="date">
			<?php foreach ( $leaguemanager->months AS $key => $month ) : ?>
				<?php if ( $key == $match_month ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $key ?>"<?php echo $selected ?>><?php echo $month ?></option>
			<?php endforeach; ?>
			</select>
			<select size="1" name="match_year" class="date">
			<?php for ( $year = date("Y"); $year <= date("Y")+1; $year++ ) : ?>
				<?php if ( $year == $match_year ) $selected = ' selected="selected"'; else $selected = ''; ?>
				<option value="<?php echo $year ?>"<?php echo $selected ?>><?php echo $year ?></option>
			<?php endfor; ?>
			</select>
			<br />
			
			<p class="match_info"><?php _e( 'Note: Matches with different Home and Guest Teams will be added to the database.', 'leaguemanager' ) ?></p>
			<?php $teams = $leaguemanager->getTeams( "league_id = '".$league_id."'" ); ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e( 'Home', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Guest', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php for ( $i = 1; $i <= $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class ?>">
					<td>
						<select size="1" name="home_team[<?php echo $i ?>]" id="home_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<?php if ( $team->id == $home_team ) $selected = 'selected="selected"'; else $selected = ''; ?>
							<option value="<?php echo $team->id ?>"<?php echo $selected?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select size="1" id="away_team[<?php echo $i ?>]" name="away_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<?php if ( $team->id == $away_team ) $selected = 'selected="selected"'; else $selected = ''; ?>
							<option value="<?php echo $team->id ?>"<?php echo $selected?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $location ?>" size="30" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
							<?php if ( $hour == $begin_hour ) $selected = 'selected="selected"'; else $selected = ''; ?>
							<option value="<?php echo $hour ?>"<?php echo $selected ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
							<?php if ( $minute == $begin_minutes ) $selected = 'selected="selected"'; else $selected = ''; ?>
							<?php if ( 0 == $minute % 15 && 60 != $minute ) : ?>
							<option value="<?php echo $minute ?>"<?php echo $selected ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
				</tr>
				<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $i ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="match_id" value="<?php echo $match_id ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>