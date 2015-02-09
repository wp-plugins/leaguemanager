<?php
if ( isset($_POST['updateLeague']) && !isset($_POST['doaction']) && !isset($_POST['doaction2']) && !isset($_POST['doaction3']) )  {
	if ( 'team' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-teams');
		$home = isset( $_POST['home'] ) ? 1 : 0;
		$custom = !isset($_POST['custom']) ? array() : htmlspecialchars($_POST['custom']);
		$roster = ( isset($_POST['roster_group']) && isset($_POST['roster']) ) ? array('id' => intval($_POST['roster']), 'cat_id' => intval($_POST['roster_group'])) : array( 'id' => '', 'cat_id' => false );
		$group = isset($_POST['group']) ? htmlspecialchars($_POST['group']) : '';
		if ( '' == $_POST['team_id'] ) {
			$this->addTeam( intval($_POST['league_id']), htmlspecialchars($_POST['season']), htmlspecialchars($_POST['team']), htmlspecialchars($_POST['website']), htmlspecialchars($_POST['coach']), htmlspecialchars($_POST['stadium']), $home, $group, $roster, $custom, htmlspecialchars($_POST['logo_db']) );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$this->editTeam( intval($_POST['team_id']), htmlspecialchars($_POST['team']), htmlspecialchars($_POST['website']), htmlspecialchars($_POST['coach']), htmlspecialchars($_POST['stadium']), $home, $group, $roster, $custom, htmlspecialchars($_POST['logo_db']), $del_logo, $overwrite_image );
		}
	} elseif ( 'match' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-matches');

		$group = isset($_POST['group']) ? htmlspecialchars($_POST['group']) : '';
		if ( 'add' == $_POST['mode'] ) {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i => $match_id ) {
				if ( isset($_POST['add_match'][$i]) || $_POST['away_team'][$i] != $_POST['home_team'][$i]  ) {
					$index = ( isset($_POST['mydatepicker'][$i]) ) ? $i : 0;
					$date = $_POST['mydatepicker'][$index].' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
					$match_day = ( isset($_POST['match_day'][$i]) ? $_POST['match_day'][$i] : (!empty($_POST['match_day']) ? intval($_POST['match_day']) : '' )) ;
					$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();

					$this->addMatch( $date, intval($_POST['home_team'][$i]), intval($_POST['away_team'][$i]), $match_day, htmlspecialchars($_POST['location'][$i]), intval($_POST['league_id']), htmlspecialchars($_POST['season']), $group, htmlspecialchars($_POST['final']), $custom );
				} else {
					$num_matches -= 1;
				}
			}
			$leaguemanager->setMessage(sprintf(_n('%d Match added', '%d Matches added', $num_matches, 'leaguemanager'), $num_matches));
		} else {
			$num_matches = count($_POST['match']);
			$post_match = $this->htmlspecialchars_array($_POST['match']);
			foreach ( $post_match AS $i => $match_id ) {
				if( isset($_POST['mydatepicker'][$i]) ) {
					$index = ( isset($_POST['mydatepicker'][$i]) ) ? $i : 0;
					$date = $_POST['mydatepicker'][$index].' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
				} else {
					$index = ( isset($_POST['year'][$i]) && isset($_POST['month'][$i]) && isset($_POST['day'][$i]) ) ? $i : 0;
					$date = intval($_POST['year'][$index]).'-'.intval($_POST['month'][$index]).'-'.intval($_POST['day'][$index]).' '.intval($_POST['begin_hour'][$i]).':'.intval($_POST['begin_minutes'][$i]).':00';
				}
				$match_day = is_array($_POST['match_day']) ? $_POST['match_day'][$i] : (!empty($_POST['match_day']) ? $_POST['match_day'] : '' ) ;
				$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();
				$home_team = isset($_POST['home_team'][$i]) ? $_POST['home_team'][$i] : '';
				$away_team = isset($_POST['away_team'][$i]) ? $_POST['away_team'][$i] : '';
				$final = isset($_POST['final'][$i]) ? $_POST['final'][$i] : '';
				$this->editMatch( $date, intval($home_team), intval($away_team), $match_day, htmlspecialchars($_POST['location'][$i]), intval($_POST['league_id']), $match_id, $group, htmlspecialchars($final), $custom );
			}
			$leaguemanager->setMessage(sprintf(_n('%d Match updated', '%d Matches updated', $num_matches, 'leaguemanager'), $num_matches));
		}
	} elseif ( 'results' == $_POST['updateLeague'] ) {
		check_admin_referer('matches-bulk');
		$custom = isset($_POST['custom']) ? $_POST['custom'] : array();
		$this->updateResults( intval($_POST['league_id']), $_POST['matches'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $custom );
	} elseif ( 'teams_manual' == $_POST['updateLeague'] ) {
		check_admin_referer('teams-bulk');
		$this->saveStandingsManually( $_POST['team_id'], $_POST['points_plus'], $_POST['points_minus'], $_POST['num_done_matches'], $_POST['num_won_matches'], $_POST['num_draw_matches'], $_POST['num_lost_matches'], $_POST['add_points'], $_POST['custom'] );

		$leaguemanager->setMessage(__('Standings Table updated','leaguemanager'));
	}
	
	$leaguemanager->printMessage();
}  elseif ( isset($_POST['doaction']) || isset($_POST['doaction2']) ) {
	if ( isset($_POST['doaction']) && $_POST['action'] == "delete" ) {
		check_admin_referer('teams-bulk');
		foreach ( $_POST['team'] AS $team_id )
			$this->delTeam( intval($team_id), true );
	} elseif ( isset($_POST['doaction2']) && $_POST['action2'] == "delete" ) {
		check_admin_referer('matches-bulk');
		foreach ( $_POST['match'] AS $match_id )
			$this->delMatch( intval($match_id) );
	}
}

// rank teams manually
if (isset($_POST['updateRanking'])) {
	$league = $leaguemanager->getCurrentLeague();
	$season = $leaguemanager->getSeason($league);
		
	$team_ranks = array();
	foreach ($_POST['rank'] AS $team_id => $rank) {
		$team = $leaguemanager->getTeam($team_id);
		$team_ranks[$rank-1] = $team;
	}
	ksort($team_ranks);
	updateRanking($league->id, $season, "", $team_ranks, $team_ranks);
	$leaguemanager->setMessage(__('Team ranking saved','leaguemanager'));
	$leaguemanager->printMessage();
}
	
$league = $leaguemanager->getCurrentLeague();
$season = $leaguemanager->getSeason($league);
$leaguemanager->setSeason($season);
$league_mode = (isset($league->mode) ? ($league->mode) : '' );

// check if league is a cup championship
$cup = ( $league_mode == 'championship' ) ? true : false;

$group = isset($_GET['group']) ? htmlspecialchars($_GET['group']) : '';

$team_search = '`league_id` = "'.$league->id.'" AND `season` = "'.$season['name'].'"';
$team_list = $leaguemanager->getTeams( $team_search, "`id` ASC", 'ARRAY' );
$options = get_option('leaguemanager');

$match_search = '`league_id` = "'.$league->id.'" AND `final` = ""';

if ( $season )
	$match_search .= " AND `season` = '".$season['name']."'";
if ( $group )
	$match_search .= " AND `group` = '".$group."'";
if ( isset($_POST['doaction3'])) {
	if ($_POST['match_day'] != -1) {
		$matchDay = $_POST['match_day'];
		$leaguemanager->setMatchDay($matchDay);
		$match_search .= " AND `match_day` = '".$matchDay."'";
	}
} else {
/*	$matchDay = $leaguemanager->getMatchDay('current');
	$leaguemanager->setMatchDay($matchDay);
	$match_search .= " AND `match_day` = '".$matchDay."'";
*/
}

if ( empty($league->seasons)  ) {
	$leaguemanager->setMessage( __( 'You need to add at least one season', 'leaguemanager' ), true );
	$leaguemanager->printMessage();
}

if ( $league_mode != 'championship' ) {
	$teams = $leaguemanager->getTeams( $team_search );
	$matches = $leaguemanager->getMatches( $match_search );
}
?>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'LeagueManager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league->title ?></p>

	<h2><?php echo $league->title ?></h2>

	<?php if ( !empty($league->seasons) ) : ?>
	<!-- Season Dropdown -->
	<div class="alignright" style="clear: both;">
	<form action="admin.php" method="get" style="display: inline;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="show-league" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<label for="season" style="vertical-align: middle;"><?php _e( 'Season', 'leaguemanager' ) ?></label>
		<select size="1" name="season" id="season">
		<?php foreach ( $league->seasons AS $s ) : ?>
			<option value="<?php echo $s['name'] ?>"<?php if ( $s['name'] == $season['name'] ) echo ' selected="selected"' ?>><?php echo $s['name'] ?></option>
		<?php endforeach; ?>
		</select>
		<input type="submit" value="<?php _e( 'Show', 'leaguemanager' ) ?>" class="button" />
	</form>
	</div>
	<?php endif; ?>

	<!-- League Menu -->
	<ul class="subsubsub">
	<?php foreach ( $this->getMenu() AS $key => $menu ) : ?>
	<?php if ( !isset($menu['show']) || $menu['show'] ) : ?>
		<li><a href="admin.php?page=leaguemanager&amp;subpage=<?php echo $key ?>&amp;league_id=<?php echo $league->id ?>&amp;season=<?php echo $season['name'] ?>&amp;group=<?php echo $group ?>"><?php echo $menu['title'] ?></a></li>
	<?php endif; ?>
	<?php endforeach; ?>
	</ul>


	<?php if ( $league_mode == 'championship' ) : ?>
		<?php include('championship.php'); ?>
	<?php else : ?>
		<h3 style="clear: both;"><?php _e( 'Table', 'leaguemanager' ) ?></h3>
		<?php include_once('standings.php'); ?>

		<br style="clear: both;" />
		<h3><?php _e( 'Match Plan','leaguemanager' ) ?></h3>
		<?php include('matches.php'); ?>
	<?php endif; ?>
</div>
