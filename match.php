<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	if ( isset( $_GET['edit'] ) ) {
		$mode = 'edit';
		$edit = true;
		$form_title = __( 'Edit Match', 'leaguemanager' );

		if ( $match = $leaguemanager->getMatch($_GET['edit']) ) {
			$league_id = $match->league_id;
			$match_day = $match->match_day;
			$m_day = $match->day;
			$m_month = $match->month;
			$m_year = $match->year;
			$match_id[1] = $match->id;
			$begin_hour[1] = $match->hour;
			$begin_minutes[1] = $match->minutes;
			$location[1] = $match->location;
			$home_team[1] = $match->home_team;
			$away_team[1] = $match->away_team;
			$home_apparatus_points[1] = $match->home_apparatus_points;
			$away_apparatus_points[1] = $match->away_apparatus_points;
			$home_points[1] = $match->home_points;
			$away_points[1] = $match->away_points;
	
			$max_matches = 1;
		}
	} elseif (isset($_GET['match_day'])) {
		$mode = 'edit';
		$edit = true;
		$form_title = __( 'Edit Matches', 'leaguemanager' );
		
		$match_day = $_GET['match_day'];
		$league_id = $_GET['league_id'];
		if ( $matches = $leaguemanager->getMatches( "`match_day` = '".$match_day."' AND `league_id` = '".$league_id."'" ) ) {
			$m_day = $matches[0]->day;
			$m_month = $matches[0]->month;
			$m_year = $matches[0]->year;
			$date = $m_year."-".$m_month."-".$m_day;
	
			$i = 1;
			foreach ( $matches AS $match ) {
				$match_date[$i] = $match->year."-".$match->month."-".$match->day;
				$match_id[$i] = $match->id;
				$begin_hour[$i] = $match->hour;
				$begin_minutes[$i] = $match->minutes;
				$location[$i] = $match->location;
				$home_team[$i] = $match->home_team;
				$away_team[$i] = $match->away_team;
				$home_apparatus_points[$i] = $match->home_apparatus_points;
				$away_apparatus_points[$i] = $match->away_apparatus_points;
				$home_points[$i] = $match->home_points;
				$away_points[$i] = $match->away_points;
	
				$i++;
			}
			$max_matches = count($matches);
		}
	} else {
		$mode = 'add';
		$edit = false;
		$form_title = __( 'Add Matches', 'leaguemanager' );

		$league_id = $_GET['league_id'];
		$max_matches = 15;
		$m_year = date("Y");
		$match_day = $m_day = $m_month = $home_team = $away_team = $begin_hour = $begin_minutes = $location = $match_id = array_fill(1, $max_matches, '');
	}
	$league = $leaguemanager->getLeagues( $league_id );
	?>
	
	<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=leaguemanager/manage-leagues.php"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id ?>"><?php echo $league['title'] ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form action="edit.php?page=leaguemanager/show-league.php&amp;id=<?php echo $league_id?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<table class="form-table">
			<tr>
				<th scope="row"><label for="date"><?php _e('Date', 'leaguemanager') ?></label></th>
				<td>
					<select size="1" name="m_day" class="date">
						<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
						<option value="<?php echo str_pad($day, 2, 0, STR_PAD_LEFT) ?>"<?php if ( $day == $m_day ) echo ' selected="selected"' ?>><?php echo $day ?></option>
						<?php endfor; ?>
					</select>
					<select size="1" name="m_month" class="date">
						<?php foreach ( $leaguemanager->months AS $key => $month ) : ?>
						<option value="<?php echo str_pad($key, 2, 0, STR_PAD_LEFT) ?>"<?php if ( $key == $m_month ) echo ' selected="selected"' ?>><?php echo $month ?></option>
						<?php endforeach; ?>
					</select>
					<select size="1" name="m_year" class="date">
						<?php for ( $year = date("Y")-1; $year <= date("Y")+1; $year++ ) : ?>
						<option value="<?php echo $year ?>"<?php if ( $year == $m_year ) echo ' selected="selected"' ?>><?php echo $year ?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="match_day"><?php _e('Match Day', 'leaguemanager') ?></label></th>
				<td>
					<select size="1" name="match_day">
						<?php for ($i = 1; $i <= $leaguemanager->getNumMatchDays($league_id); $i++) : ?>
						<option value="<?php echo $i ?>"<?php if($i == $match_day) echo ' selected="selected"' ?>><?php echo $i ?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			</table>
			
			
			<p class="match_info"><?php if ( !$edit ) : ?><?php _e( 'Note: Matches with different Home and Guest Teams will be added to the database.', 'leaguemanager' ) ?><?php endif; ?></p>
			
			
			<?php $teams = $leaguemanager->getTeams( "league_id = '".$league_id."'" ); ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e( 'Home', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Guest', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
						<?php if ( $edit ) : ?>
						<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
						<th><?php _e( 'Apparatus Points', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody id="the-list" class="form-table">
				<?php for ( $i = 1; $i <= $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; if ( $match_date[$i] != $date ) $date_missmatch = true; ?>
				<tr class="<?php echo $class; if ( $date_missmatch ) echo ' error' ?>">
					<td>
						<select size="1" name="home_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php if ( $team->id == $home_team[$i] ) echo ' selected="selected"' ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select size="1" name="away_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php if ( $team->id == $away_team[$i] ) echo ' selected="selected"' ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $location[$i] ?>" size="30" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 0; $hour <= 23; $hour++ ) : ?>
							<option value="<?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?>"<?php if ( $hour == $begin_hour[$i] ) echo ' selected="selected"' ?>><?php echo str_pad($hour, 2, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 60; $minute++ ) : ?>
							<?php if ( 0 == $minute % 15 && 60 != $minute ) : ?>
							<option value="<?php  echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?>"<?php if ( $minute == $begin_minutes[$i] ) echo ' selected="selected"' ?>><?php echo str_pad($minute, 2, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
					<?php if ( $edit ) : ?>
					<?php if ( $leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
					<td><input class="points" type="text" size="2" name="home_apparatus_points[<?php echo $i ?>]" value="<?php echo $home_apparatus_points[$i] ?>" /> : <input class="points" type="text" size="2" name="away_apparatus_points[<?php echo $i ?>]" value="<?php echo $away_apparatus_points[$i] ?>" /></td>
					<?php endif; ?>
					<td><input class="points" type="text" size="2" name="home_points[<?php echo $i ?>]" value="<?php echo $home_points[$i] ?>" /> : <input class="points" type="text" size="2" name="away_points[<?php echo $i ?>]" value="<?php echo $away_points[$i] ?>" /></td>
					<?php endif; ?>
				</tr>
				<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $i ?>" />
				<input type="hidden" name="match_id[<?php echo $i ?>]" value="<?php echo $match_id[$i] ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			<?php if ( $date_missmatch ) : ?>
			<div class="error"><p><?php _e( '<strong>Attention</strong>: The dates of one or more matches differ from that of the first one, which are indicated by red background! Be aware that all matches will be given the same date when you edit them and you will need to re-edit them separately!', 'leaguemanager' ) ?></p></div>
			<?php endif; ?>
			
			<input type="hidden" name="mode" value="<?php echo $mode ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>