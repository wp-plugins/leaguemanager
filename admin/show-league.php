<?php
if ( isset($_POST['updateLeague']) && !isset($_POST['doaction']) && !isset($_POST['doaction2']) && !isset($_POST['doaction3']) )  {
	if ( 'team' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-teams');
		$home = isset( $_POST['home'] ) ? 1 : 0;
		if ( '' == $_POST['team_id'] ) {
			$this->addTeam( $_POST['league_id'], $_POST['short_title'], $_POST['team'], $_POST['website'], $home );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$this->editTeam( $_POST['team_id'], $_POST['short_title'], $_POST['team'], $_POST['website'], $home, $del_logo, $_POST['image_file'], $overwrite_image );
		}
	} elseif ( 'match' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-matches');
		
		if ( 'add' == $_POST['mode'] ) {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i => $match_id ) {
				if ( $_POST['away_team'][$i] != $_POST['home_team'][$i] ) {
					$date = $_POST['year'][0].'-'.$_POST['month'][0].'-'.$_POST['day'][0].' '.$_POST['begin_hour'][$i].':'.$_POST['begin_minutes'][$i].':00';
					$this->addMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $_POST['match_day'], $_POST['location'][$i], $_POST['league_id'] );
				} else {
					$num_matches -= 1;
				}
			}
			$this->setMessage(sprintf(__ngettext('%d Match added', '%d Matches added', $num_matches, 'leaguemanager'), $num_matches));
		} else {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i => $match_id ) {
				$index = ( isset($_POST['year'][$i]) && isset($_POST['month'][$i]) && isset($_POST['day'][$i]) ) ? $i : 0;	
				$date = $_POST['year'][$index].'-'.$_POST['month'][$index].'-'.$_POST['day'][$index].' '.$_POST['begin_hour'][$i].':'.$_POST['begin_minutes'][$i].':00';
				$overtime = isset($_POST['overtime'][$i]) ? 1 : 0;
				
				$this->editMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $_POST['match_day'], $_POST['location'][$i], $_POST['league_id'], $match_id, $_POST['home_points'][$i], $_POST['away_points'][$i],  $_POST['home_points2'][$i], $_POST['away_points2'][$i], $overtime );
			}
			$this->setMessage(sprintf(__ngettext('%d Match updated', '%d Matches updated', $num_matches, 'leaguemanager'), $num_matches));
		}
	} elseif ( 'results' == $_POST['updateLeague'] ) {
		check_admin_referer('matches-bulk');
		$this->updateResults( $_POST['league_id'], $_POST['matches'], $_POST['home_points2'], $_POST['away_points2'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $_POST['overtime'] );
	} elseif ( 'teams_manual' == $_POST['updateLeague'] ) {
		check_admin_referer('teams-bulk');
		foreach ( $_POST['team_id'] AS $team_id )
			$this->saveStandingsManually( $team_id, $_POST['points_plus'][$team_id], $_POST['points_minus'][$team_id], $_POST['points2_plus'][$team_id], $_POST['points2_minus'][$team_id], $_POST['num_done_matches'][$team_id], $_POST['num_won_matches'][$team_id], $_POST['num_draw_matches'][$team_id], $_POST['num_lost_matches'][$team_id] );

		$this->setMessage(__('Standings Table updated','leaguemanager'));
	}
	$this->printMessage();
}  elseif ( isset($_POST['doaction']) || isset($_POST['doaction2']) ) {
	if ( isset($_POST['doaction']) && $_POST['action'] == "delete" ) {
		check_admin_referer('teams-bulk');
		foreach ( $_POST['team'] AS $team_id )
			$this->delTeam( $team_id);
	} elseif ( isset($_POST['doaction2']) && $_POST['action2'] == "delete" ) {
		check_admin_referer('matches-bulk');
		foreach ( $_POST['match'] AS $match_id )
			$this->delMatch( $match_id );
	}
}

$leaguemanager->setLeagueID($_GET['id']); // set leagueID
$league = $leaguemanager->getLeague( $_GET['id'] );
$team_list = $leaguemanager->getTeams( 'league_id = "'.$league->id.'"', 'ARRAY' );

$match_search = 'league_id = "'.$league->id.'"';
if ( isset($_POST['doaction3']) && $_POST['match_day'] != -1 ) {
	$leaguemanager->setMatchDay( $_POST['match_day'] );
	$match_search .= " AND `match_day` = '".$leaguemanager->getMatchDay()."'";
}

if ( !wp_mkdir_p( $leaguemanager->getImagePath() ) )
	echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $leaguemanager->getImagePath() )."</p></div>";
?>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league->title ?></p>
	
	<h2><?php echo $league->title ?></h2>
	
	<ul class="subsubsub">
		<li><a href="admin.php?page=leaguemanager&amp;subpage=settings&amp;league_id=<?php echo $league->id ?>"><?php _e( 'Preferences', 'leaguemanager' ) ?></a></li> |
		<li><a href="admin.php?page=leaguemanager&amp;subpage=team&amp;league_id=<?php echo $league->id ?>"><?php _e( 'Add Team','leaguemanager' ) ?></a></li> |
		<li><a href="admin.php?page=leaguemanager&amp;subpage=match&amp;league_id=<?php echo $league->id ?>"><?php _e( 'Add Matches','leaguemanager' ) ?></a></li>
	</ul>
	
	<h3 style="clear: both;"><?php _e( 'Table', 'leaguemanager' ) ?></h3>
	
	<form id="teams-filter" action="" method="post">
		<?php wp_nonce_field( 'teams-bulk' ) ?>
			
		<div class="tablenav" style="margin-bottom: 0.1em;">
			<!-- Bulk Actions -->
			<select name="action" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		
		<table class="widefat" summary="" title="<?php _e( 'Table', 'leaguemanager' ) ?>">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('teams-filter'));" /></th>
			<th class="num">#</th>
			<th class="logo">&#160;</th>
			<th><?php _e( 'Club', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Win','leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Tie','leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Defeat','leaguemanager' ) ?></th>
			<?php if ( $leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
				<th class="num"><?php _e( 'AP', 'leaguemanager' ) ?></th>
			<?php else : ?>
				<th class="num"><?php _e( 'Goals', 'leaguemanager' ) ?></th>
			<?php endif; ?>
			<th class="num"><?php _e( 'Diff', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php $teams = $leaguemanager->rankTeams( $league->id ) ?>
		<?php if ( count($teams) > 0 ) : $rank = 0; ?>
		<?php foreach( $teams AS $team ) : $rank++; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $team->id ?>" name="team[<?php echo $team->id ?>]" /></th>
			<td class="num"><?php echo $rank ?></td>
			<td class="logo">
			<?php if ( $team->logo != '' ) : ?>
				<img src="<?php echo $leaguemanager->getThumbnailUrl($team->logo) ?>" alt="<?php _e( 'Logo', 'leaguemanager' ) ?>" title="<?php _e( 'Logo', 'leaguemanager' ) ?> <?php echo $team->title ?>" />
			<?php endif; ?>
			</td>
			<td><a href="admin.php?page=leaguemanager&amp;subpage=team&amp;edit=<?php echo $team->id; ?>"><?php echo $team->title ?></a></td>
			<?php if ( !defined('LEAGUEMANAGER_MANUAL') ) : ?>
			<td class="num"><?php echo $team->done_matches ?></td>
			<td class="num"><?php echo $team->won_matches ?></td>
			<td class="num"><?php echo $team->draw_matches ?></td>
			<td class="num"><?php echo $team->lost_matches ?></td>
			<?php else : ?>
			<td class="num"><input type="text" size="2" name="num_done_matches[<?php echo $team->id ?>]" value="<?php echo $team->done_matches  ?>" /></td>
			<td class="num"><input type="text" size="2" name="num_won_matches[<?php echo $team->id ?>]" value="<?php echo $team->won_matches  ?>" /></td>
			<td class="num"><input type="text" size="2" name="num_draw_matches[<?php echo $team->id ?>]" value="<?php echo $team->draw_matches ?>" /></td>
			<td class="num"><input type="text" size="2" name="num_lost_matches[<?php echo $team->id ?>]" value="<?php echo $team->lost_matches ?>" /></td>
			<?php endif; ?>
			<td class="num">
				<?php if ( !defined('LEAGUEMANAGER_MANUAL') ) : ?>
				<?php printf('%d:%d', $team->points2['plus'], $team->points2['minus']) ?>
				<?php else : ?>
				<input type="text" size="2" name="points2_plus[<?php echo $team->id ?>]" value="<?php echo $team->points2['plus'] ?>" /> : <input type="text" size="2" name="points2_minus[<?php echo $team->id ?>]" value="<?php echo $team->points2['minus'] ?>" />
				<?php endif; ?>
			</td>
			<td class="num"><?php echo $team->diff ?></td>
			<td class="num">
				<?php if ( !defined('LEAGUEMANAGER_MANUAL') ) : ?>
				<?php printf($league->point_format, $team->points['plus'], $team->points['minus']) ?>
				<?php else : ?>
				<input type="text" size="2" name="points_plus[<?php echo $team->id ?>]" value="<?php echo $team->points['plus'] ?>" /> : <input type="text" size="2" name="points_minus[<?php echo $team->id ?>]" value="<?php echo $team->points['minus'] ?>" />
				<?php endif; ?>
			</td>
		</tr>
		<input type="hidden" name="team_id[]" value="<?php echo $team->id ?>" />
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<?php if ( defined('LEAGUEMANAGER_MANUAL') ) : ?>
			<input type="hidden" name="updateLeague" value="teams_manual" />
			<p class="submit"><input type="submit" value="<?php _e( 'Save Standings', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
	
	<h3><?php _e( 'Match Plan','leaguemanager' ) ?></h3>
	<?php if ( $league->num_match_days > 0 ) : ?>
	<!-- Bulk Editing of Matches -->
	<form action="admin.php" method="get" style="float: right;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="match" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<select size="1" name="match_day">
			<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
			<option value="<?php echo $i ?>"><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
			<?php endfor; ?>
		</select>
		<input type="submit" value="<?php _e('Edit Matches', 'leaguemanager'); ?>" class="button-secondary action" />
	</form>
	<?php endif; ?>
	<form id="competitions-filter" action="" method="post">
		<?php wp_nonce_field( 'matches-bulk' ) ?>
		
		<?php $matches = $leaguemanager->getMatches( $match_search ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em; clear: none;">
			<!-- Bulk Actions -->
			<select name="action2" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
			
			<?php if ( $league->num_match_days > 0 ) : ?>
			<select size='1' name='match_day'>
			<?php $selected = ( !isset($_POST['doaction3']) || (isset($_POST['doaction3']) && $_POST['match_day'] == -1) ) ? ' selected="selected"' : ''; ?>
			<option value="-1"<?php echo $selected ?>><?php _e( 'Show all Matches', 'leaguemanager' ) ?></option>
			<?php for ($i = 1; $i <= $league->num_match_days; $i++) : ?>
			<option value='<?php echo $i ?>'<?php if ($leaguemanager->getMatchDay() == $i && isset($_POST['doaction3']) && $_POST['doaction'] != -1 ) echo ' selected="selected"' ?>><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
			<?php endfor; ?>
			</select>
			<input type='submit' name="doaction3" id="doaction3" class="button-secondary action" value='<?php _e( 'Filter' ) ?>' />
			<?php endif; ?>
		</div>
		
		<table class="widefat" summary="" title="<?php _e( 'Match Plan','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Match','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
			<?php if ( $leaguemanager->getMatchParts($league->type) ) : ?>
			<th><?php $leaguemanager->matchPartsTitle( $league->type ) ?></th>
			<?php endif; ?>
			<th><?php _e( 'Score', 'leaguemanager' ) ?></th>
			<?php if ( !$leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
			<th><?php _e( 'Overtime?', 'leaguemanager' ) ?></th>
			<?php endif; ?>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php if ( $matches ) : ?>
			<?php foreach ( $matches AS $match ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column">
					<input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" />
					<input type="hidden" name="home_team[<?php echo $match->id ?>]" value="<?php echo $match->home_team ?>" />
					<input type="hidden" name="away_team[<?php echo $match->id ?>]" value="<?php echo $match->away_team ?>" />
					<input type="checkbox" value="<?php echo $match->id ?>" name="match[<?php echo $match->id ?>]" /></th>
				<td><?php echo mysql2date(get_option('date_format'), $match->date) ?></td>
				<td><a href="admin.php?page=leaguemanager&amp;subpage=match&amp;edit=<?php echo $match->id ?>">
				<?php echo $team_list[$match->home_team]['title'] ?> - <?php echo $team_list[$match->away_team]['title'] ?>
				</td>
				<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
				<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
				<?php if ( $leaguemanager->getMatchParts( $league->type ) ) : ?>
				<?php $points2 = maybe_unserialize( $match->points2 ); if ( !is_array($points2) ) $points2 = array($points2); ?>
				<td>
				<?php for ( $i = 1; $i <= $leaguemanager->getMatchParts($league->type); $i++ ) : ?>
					<input class="points" type="text" size="2" id="home_points2_<?php echo $match->id ?>_<?php echo $i ?>" name="home_points2[<?php echo $match->id ?>][<?php echo $i ?>]" value="<?php echo $points2[$i-1]['plus'] ?>" /> : <input class="points" type="text" size="2" id="away_points_<?php echo $match->id ?>_<?php echo $i ?>" name="away_points2[<?php echo $match->id ?>][<?php echo $i ?>]" value="<?php echo $points2[$i-1]['minus'] ?>" />
					<br />
				<?php endfor; ?>
				</td>
				<?php endif; ?>
				<td><input class="points" type="text" size="2" id="home_points[<?php echo $match->id ?>]" name="home_points[<?php echo $match->id ?>]" value="<?php echo $match->home_points ?>" /> : <input class="points" type="text" size="2" id="away_points[<?php echo $match->id ?>]" name="away_points[<?php echo $match->id ?>]" value="<?php echo $match->away_points ?>" /></td>
				<?php if ( !$leaguemanager->isGymnasticsLeague( $league->id ) ) : ?>
				<td><input type="checkbox" value="1" name="overtime[<?php echo $match->id ?>]" <?php if ( $match->overtime == 1 ) echo ' checked="checked"' ?> /></td>
				<?php endif; ?>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<?php if ( $matches ) : ?>
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="updateLeague" value="results" />
			<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Update Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
</div>