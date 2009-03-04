<?php
if ( !current_user_can( 'manage_leagues' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	$error = $is_finals = false;
	if ( isset($_GET['league_id']) ) $league_id = $_GET['league_id'];
	$season = isset($_GET['season']) ? $_GET['season'] : '';
	$final = isset($_GET['final']) ? $_GET['final'] : '';
	
	if ( isset( $_GET['edit'] ) ) {
		$mode = 'edit';
		$edit = true; $bulk = false;
		$form_title  = $submit_title = __( 'Edit Match', 'leaguemanager' );

		if ( $match = $leaguemanager->getMatch($_GET['edit']) ) {
			$league_id = $match->league_id;
			$match_day = $match->match_day;
			$m_day[0] = $match->day;
			$m_month[0] = $match->month;
			$m_year[0] = $match->year;
			$match_id[1] = $match->id;
			$begin_hour[1] = $match->hour;
			$begin_minutes[1] = $match->minutes;
			$location[1] = $match->location;
			$home_team[1] = $match->home_team;
			$away_team[1] = $match->away_team;
			$points2[1] = $match->points2;
			$overtime[1] = maybe_unserialize($match->overtime);
			$penalty[1] = maybe_unserialize($match->penalty);
			$home_points[1] = $match->home_points;
			$away_points[1] = $match->away_points;
	
			$max_matches = 1;
		} else {
			$error = true;
		}
	} elseif ( isset($_GET['match_day']) || isset($_GET['final']) ) {
		$mode = 'edit';
		$edit = true; $bulk = true;
		$match_day = $order = false;
		
		$league_id = $_GET['league_id'];
		$search = "`league_id` = '".$league_id."'";
		if ( isset($_GET['match_day']) ) {
			$match_day = $_GET['match_day'];
			$search .= " AND `match_day` = '".$match_day."' AND `season` = '".$season."'";
			$form_title = sprintf(__( 'Edit Matches &#8211; %d. Match Day', 'leaguemanager' ), $match_day);
			$submit_title = __('Edit Matches', 'leaguemanager');
		} elseif ( isset($_GET['final']) ){
			$is_finals = true;
			$num_groups = $leaguemanager->getNumGroups( $league_id );
			$num_advance = 2; // First and Second of each group qualify for finals
			$num_first_round = $num_groups * $num_advance; // number of teams in first final round -> determines number of finals
			$search .= " AND `final` = '".$final."'";
			$order = "`id` ASC, `date ASC";
			$form_title = sprintf(__( 'Edit Matches &#8211; %s', 'leaguemanager' ), $leaguemanager->getFinalName($final));
			$submit_title = __('Edit Matches', 'leaguemanager');
		}
		
		if ( $matches = $leaguemanager->getMatches( $search, false, $order ) ) {
			$m_day[0] = $matches[0]->day;
			$m_month[0] = $matches[0]->month;
			$m_year[0] = $matches[0]->year;
			$date = $m_year[0]."-".$m_month[0]."-".$m_day[0];
	
			$i = 1;
			foreach ( $matches AS $match ) {
				$match_id[$i] = $match->id;
				$m_day[$i] = $match->day;
				$m_month[$i] = $match->month;
				$m_year[$i] = $match->year;
				$begin_hour[$i] = $match->hour;
				$begin_minutes[$i] = $match->minutes;
				$location[$i] = $match->location;
				$home_team[$i] = $match->home_team;
				$away_team[$i] = $match->away_team;
				$points2[$i] = $match->points2;
				$overtime[$i] = maybe_unserialize($match->overtime);
				$penalty[$i] = maybe_unserialize($match->penalty);
				$home_points[$i] = $match->home_points;
				$away_points[$i] = $match->away_points;
	
				$i++;
			}
			$max_matches = count($matches);
		} else {
			if ( !empty($final) ) {
				$mode = 'add';
				$edit = false; $bulk = false;
				$form_title = $submit_title = sprintf(__( 'Add Matches &#8211; %s', 'leaguemanager' ),$leaguemanager->getFinalName($final));
				$max_matches = $_GET['num_matches'];
				$m_year[0] = date("Y"); $match_day = '';
				$m_day = $m_month = $home_team = $away_team = $begin_hour = $begin_minutes = $location = $match_id  = $overtime = $penalty = array_fill(1, $max_matches, '');
			} else {
				$error = true;
			}
		}
	} else {
		$mode = 'add';
		$edit = false; $bulk = false;
		$form_title = $submit_title = __( 'Add Matches', 'leaguemanager' );

		$max_matches = 15;
		$m_year[0] = date("Y"); $match_day = '';
		$m_day = $m_month = $home_team = $away_team = $begin_hour = $begin_minutes = $location = $match_id  = $overtime = $penalty = array_fill(1, $max_matches, '');
	}
	$season = $leaguemanager->getCurrentSeason($league_id);
	$league = $leaguemanager->getLeague( $league_id );
	$leaguemanager->setLeagueID( $league_id );
	?>
	
	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<?php if ( !$error ) : ?>
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<table class="form-table">
			<?php if ( !$bulk ) : ?>
			<tr>
				<th scope="row"><label for="date"><?php _e('Date', 'leaguemanager') ?></label></th>
				<td><?php echo $this->getDateSelection( $m_day[0], $m_month[0], $m_year[0]) ?></td>
			</tr>
			<?php endif; ?>
			<?php if ( !$is_finals ) : ?>
			<tr>
				<th scope="row"><label for="match_day"><?php _e('Match Day', 'leaguemanager') ?></label></th>
				<td>
					<select size="1" name="match_day">
						<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
						<option value="<?php echo $i ?>"<?php if($i == $match_day) echo ' selected="selected"' ?>><?php echo $i ?></option>
						<?php endfor; ?>
					</select>
				</td>
			</tr>
			<?php endif; ?>
			</table>
			
			
			<p class="match_info"><?php if ( !$edit ) : ?><?php _e( 'Note: Matches with different Home and Guest Teams will be added to the database.', 'leaguemanager' ) ?><?php endif; ?></p>
		
			<?php $final_start = ( $max_matches*2 == $num_first_round ) ? true : false; ?>
			<?php $teams = $is_finals ? $leaguemanager->getFinalTeams($max_matches, $final_start) : $leaguemanager->getTeams( "league_id = '".$league->id."' AND `season`  = '".$season['name']."'" ); ?>
			<table class="widefat">
				<thead>
					<tr>
						<?php if ( $bulk ) : ?>
						<th scope="col"><?php _e( 'Date', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<th scope="col"><?php _e( 'Home', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Guest', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
						<?php if ( $edit ) : ?>
						<?php if ( $leaguemanager->getMatchParts($league->sport) ) : ?>
						<th><?php echo $leaguemanager->getMatchPartsTitle( $league->sport ) ?></th>
						<?php endif; ?>
						<th><?php _e( 'Points', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<?php if ( $edit && !$leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
						<th><?php _e( 'Overtime', 'leaguemanager' ) ?>*</th>
						<th><?php _e( 'Penalty', 'leaguemanager' ) ?>*</th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody id="the-list" class="form-table">
				<?php for ( $i = 1; $i <= $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class; ?>">
					<?php if ( $bulk ) : ?>
					<td><?php echo $this->getDateSelection( $m_day[$i], $m_month[$i], $m_year[$i], $i) ?></td>
					<?php endif; ?>
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
					<?php if ( $leaguemanager->getMatchParts( $league->sport ) ) : ?>
					<?php $points_2 = maybe_unserialize( $points2[$i] ); if ( !is_array($points_2) ) $points_2 = array($points_2); ?>
					<td>
						<?php for ( $x = 1; $x <= $leaguemanager->getMatchParts($league->sport); $x++ ) : ?>
						<input class="points" type="text" size="2" id="home_points2_<?php echo $i ?>_<?php echo $x ?>" name="home_points2[<?php echo $i ?>][<?php echo $x ?>]" value="<?php echo $points_2[$x-1]['plus'] ?>" /> : <input class="points" type="text" size="2" id="away_points_<?php echo $i ?>_<?php echo $x ?>" name="away_points2[<?php echo $i ?>][<?php echo $x ?>]" value="<?php echo $points_2[$x-1]['minus'] ?>" />
						<br />
						<?php endfor; ?>
					</td>
					<?php endif; ?>
					<td><input class="points" type="text" size="2" name="home_points[<?php echo $i ?>]" value="<?php echo $home_points[$i] ?>" /> : <input class="points" type="text" size="2" name="away_points[<?php echo $i ?>]" value="<?php echo $away_points[$i] ?>" /></td>
					<?php endif; ?>
					<?php if ( $edit && !$leaguemanager->isGymnasticsLeague( $league_id ) ) : ?>
					<td>
						<input class="points" type="text" size="2" id="overtime_home_<?php echo $i ?>" name="overtime[<?php echo $i ?>][home]" value="<?php echo $overtime[$i]['home'] ?>" /> : <input class="points" type="text" size="2" id="overtime_away_<?php echo $i ?>" name="overtime[<?php echo $i ?>][away]" value="<?php echo $overtime[$i]['away'] ?>" />
					</td>
					<td>
						<input class="points" type="text" size="2" id="penalty_home_<?php echo $i ?>" name="penalty[<?php echo $i ?>][home]" value="<?php echo $penalty[$i]['home'] ?>" /> : <input class="points" type="text" size="2" id="penalty_away_<?php echo $i ?>" name="penalty[<?php echo $i ?>][away]" value="<?php echo $penalty[$i]['away'] ?>" />
					</td>
					<?php endif; ?>
				</tr>
				<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $match_id[$i] ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="mode" value="<?php echo $mode ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="season" value="<?php echo $season['name'] ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			<input type="hidden" name="final" value="<?php echo $final ?>" />
			
			<p class="submit"><input type="submit" value="<?php echo $submit_title ?> &raquo;" class="button" /></p>
		</form>
		<?php else : ?>
			<div class="error"><p><?php _e('No Matches found', 'leaguemanager') ?></p></div>
		<?php endif; ?>
	</div>
<?php endif; ?>