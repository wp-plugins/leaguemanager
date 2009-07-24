<?php
if ( isset($_POST['updateLeague']) && !isset($_POST['doaction']) && !isset($_POST['doaction2']) && !isset($_POST['doaction3']) )  {
	if ( 'team' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-teams');
		$home = isset( $_POST['home'] ) ? 1 : 0;
		$custom = !isset($_POST['custom']) ? array() : $_POST['custom'];
		$roster = ( isset($_POST['roster_group']) && !empty($_POST['roster_group']) ) ? array('id' => $_POST['roster'], 'cat_id' => $_POST['roster_group']) : array( 'id' => $_POST['roster'], 'cat_id' => false );
		$group = isset($_POST['group']) ? $_POST['group'] : '';
		if ( '' == $_POST['team_id'] ) {
			$this->addTeam( $_POST['league_id'], $_POST['season'], $_POST['team'], $_POST['website'], $_POST['coach'], $home, $group, $roster, $custom, $_POST['logo_db'] );
		} else {
			$del_logo = isset( $_POST['del_logo'] ) ? true : false;
			$overwrite_image = isset( $_POST['overwrite_image'] ) ? true: false;
			$this->editTeam( $_POST['team_id'], $_POST['team'], $_POST['website'], $_POST['coach'], $home, $group, $roster, $custom, $_POST['logo_db'], $del_logo, $overwrite_image );
		}
	} elseif ( 'match' == $_POST['updateLeague'] ) {
		check_admin_referer('leaguemanager_manage-matches');
		
		$group = isset($_POST['group']) ? $_POST['group'] : '';
		if ( 'add' == $_POST['mode'] ) {
			$num_matches = count($_POST['match']);
			foreach ( $_POST['match'] AS $i => $match_id ) {
				if ( isset($_POST['add_match'][$i]) || $_POST['away_team'][$i] != $_POST['home_team'][$i]  ) {
					$index = ( isset($_POST['year'][$i]) && isset($_POST['month'][$i]) && isset($_POST['day'][$i]) ) ? $i : 0;
					$date = $_POST['year'][$index].'-'.$_POST['month'][$index].'-'.$_POST['day'][$index].' '.$_POST['begin_hour'][$i].':'.$_POST['begin_minutes'][$i].':00';
					$match_day = is_array($_POST['match_day']) ? $_POST['match_day'][$i] : $_POST['match_day'];
					$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();

					$this->addMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $match_day, $_POST['location'][$i], $_POST['league_id'], $_POST['season'], $group, $_POST['final'], $custom );
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
				$custom = isset($_POST['custom']) ? $_POST['custom'][$i] : array();
				$this->editMatch( $date, $_POST['home_team'][$i], $_POST['away_team'][$i], $_POST['match_day'], $_POST['location'][$i], $_POST['league_id'], $match_id, $group, $_POST['final'], $custom );
			}
			$this->setMessage(sprintf(__ngettext('%d Match updated', '%d Matches updated', $num_matches, 'leaguemanager'), $num_matches));
		}
	} elseif ( 'results' == $_POST['updateLeague'] ) {
		check_admin_referer('matches-bulk');
		$this->updateResults( $_POST['league_id'], $_POST['matches'], $_POST['home_points'], $_POST['away_points'], $_POST['home_team'], $_POST['away_team'], $_POST['custom'] );
	} elseif ( 'teams_manual' == $_POST['updateLeague'] ) {
		check_admin_referer('teams-bulk');
		$this->saveStandingsManually( $_POST['team_id'], $_POST['points_plus'], $_POST['points_minus'], $_POST['num_done_matches'], $_POST['num_won_matches'], $_POST['num_draw_matches'], $_POST['num_lost_matches'], $_POST['add_points'], $_POST['custom'] );

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

$league = $leaguemanager->getCurrentLeague();
$season = $leaguemanager->getSeason($league);
$leaguemanager->setSeason($season);

$team_search = '`league_id` = "'.$league->id.'" AND `season` = "'.$season['name'].'"';
$team_list = $leaguemanager->getTeams( $team_search, 'ARRAY' );
$options = get_option('leaguemanager');

$match_search = '`league_id` = "'.$league->id.'" AND `final` = ""';

if ( $season )
	$match_search .= " AND `season` = '".$season['name']."'";
if ( isset($_POST['doaction3']) && $_POST['match_day'] != -1 ) {
	$leaguemanager->setMatchDay($_POST['match_day']);
	$match_search .= " AND `match_day` = '".$_POST['match_day']."'";
}

if ( empty($league->seasons)  ) {
	$leaguemanager->setMessage( __( 'You need to add at least one season', 'leaguemanager' ), true );
	$leaguemanager->printMessage();
}
?>
<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <?php echo $league->title ?></p>
	
	<h2><?php echo $league->title ?></h2>
	
	<?php if ( !empty($league->seasons) ) : ?>
	<form action="admin.php" method="get" style="float: right;">
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
	<?php endif; ?>
	
	<ul class="subsubsub">
	<?php foreach ( $this->getMenu() AS $key => $menu ) : ?>
	<?php if ( !isset($menu['show']) || $menu['show'] ) : ?>
		<li><a href="admin.php?page=leaguemanager&amp;subpage=<?php echo $key ?>&amp;league_id=<?php echo $league->id ?>&amp;season=<?php echo $season['name'] ?>"><?php echo $menu['title'] ?></a></li>
	<?php endif; ?>
	<?php endforeach; ?>
	</ul>
	
	<h3 style="clear: both;"><?php _e( 'Table', 'leaguemanager' ) ?></h3>
	
	<form id="teams-filter" action="" method="post" name="standings">
		<?php wp_nonce_field( 'teams-bulk' ) ?>
			
		<div class="tablenav" style="margin-bottom: 0.1em;">
			<!-- Bulk Actions -->
			<select name="action" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
		</div>
		
		<table id="standings" class="widefat" summary="" title="<?php _e( 'Table', 'leaguemanager' ) ?>">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('teams-filter'));" /></th>
			<th class="num"><?php _e( 'ID', 'leaguemanager' ) ?></th>
			<th class="num">#</th>
			<th class="num">&#160;</th>
			<th class="logo">&#160;</th>
			<th><?php _e( 'Club', 'leaguemanager' ) ?></th>
			<?php if ( !empty($league->groups) ) : ?><th class="num"><?php _e( 'Group', 'leaguemanager' ) ?></th><?php endif; ?>
			<th class="num"><?php if ( 1 == $league->standings['pld'] ) : ?><?php _e( 'Pld', 'leaguemanager' ) ?><?php endif; ?></th>
			<th class="num"><?php if ( 1 == $league->standings['won'] ) : ?><?php echo _c( 'W|Won','leaguemanager' ) ?><?php endif; ?></th>
			<th class="num"><?php if ( 1 == $league->standings['tie'] ) : ?><?php echo _c( 'T|Tie','leaguemanager' ) ?><?php endif; ?></th>
			<th class="num"><?php if ( 1 == $league->standings['lost'] ) : ?><?php echo _c( 'L|Lost','leaguemanager' ) ?><?php endif; ?></th>
			<?php do_action( 'leaguemanager_standings_header_'.$league->sport ) ?>
			<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
			<th class="num"><?php _e( '+/- Points', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list-standings" class="form-table">
		<?php $teams = $leaguemanager->getTeams( $team_search ) ?>
		<?php if ( count($teams) > 0 ) : $rank = 0; $class = ''; ?>
		<?php foreach( $teams AS $team ) : $rank++; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>" id="team_<?php echo $team->id ?>">
			<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $team->id ?>" name="team[<?php echo $team->id ?>]" /></th>
			<td><?php echo $team->id ?></td>
			<td class="num"><?php echo $team->rank ?></td>
			<td class="num"><?php echo $team->status ?></td>
			<td class="logo">
			<?php if ( $team->logo != '' ) : ?>
				<img src="<?php echo $leaguemanager->getThumbnailUrl($team->logo) ?>" alt="<?php _e( 'Logo', 'leaguemanager' ) ?>" title="<?php _e( 'Logo', 'leaguemanager' ) ?> <?php echo $team->title ?>" />
			<?php endif; ?>
			</td>
			<td><a href="admin.php?page=leaguemanager&amp;subpage=team&amp;edit=<?php echo $team->id; ?>"><?php echo $team->title ?></a></td>
			<?php if ( !empty($league->groups) ) : ?><td class="num"><?php echo $team->group ?></td><?php endif; ?>
			<?php if ( $league->point_rule != 'manual' ) : ?>

			<td class="num"><?php if ( 1 == $league->standings['pld'] ) : ?><?php echo $team->done_matches ?><?php endif; ?></td>
			<td class="num"><?php if ( 1 == $league->standings['won'] ) : ?><?php echo $team->won_matches ?><?php endif; ?></td>
			<td class="num"><?php if ( 1 == $league->standings['tie'] ) : ?><?php echo $team->draw_matches ?><?php endif; ?></td>
			<td class="num"><?php if ( 1 == $league->standings['lost'] ) : ?><?php echo $team->lost_matches ?><?php endif; ?></td>

			<?php else : ?>

			<td class="num">
				<?php if ( 1 == $league->standings['pld'] ) : ?>
				<input type="text" size="2" name="num_done_matches[<?php echo $team->id ?>]" value="<?php echo $team->done_matches  ?>" />
				<?php else : ?>
				<input type="hidden" name="num_done_matches[<?php echO $team->id ?>]" value="0" />
				<?php endif; ?>
			</td>
			<td class="num">
				<?php if ( 1 == $league->standings['won'] ) : ?>
				<input type="text" size="2" name="num_won_matches[<?php echo $team->id ?>]" value="<?php echo $team->won_matches  ?>" />
				<?php else : ?>
				<input type="hidden" name="num_won_matches[<?php echo $team->id ?>]" value="0" />
				<?php endif; ?>
			</td>
			<td class="num">
				<?php if ( 1 == $league->standings['tie'] ) : ?>
				<input type="text" size="2" name="num_draw_matches[<?php echo $team->id ?>]" value="<?php echo $team->draw_matches ?>" />
				<?php else : ?>
				<input type="hidden" name="num_draw_matches[<?php echo $team->id ?>]" value="0" />
				<?php endif; ?>
			</td>
			<td class="num">
				<?php if ( 1 == $league->standings['lost'] ) : ?>
				<input type="text" size="2" name="num_lost_matches[<?php echo $team->id ?>]" value="<?php echo $team->lost_matches ?>" />
				<?php else : ?>
				<input type="hidden" name="num_lost_matches[<?php echo $team->id ?>]" value="0" />
				<?php endif; ?>
			</td>

			<?php endif; ?>
			<?php do_action( 'leaguemanager_standings_columns_'.$league->sport, $team, $league->point_rule ) ?>
			<td class="num">
				<?php if ( $league->point_rule != 'manual' ) : ?>
				<?php printf($league->point_format, $team->points_plus, $team->points_minus) ?>
				<?php else : ?>
				<input type="text" size="2" name="points_plus[<?php echo $team->id ?>]" value="<?php echo $team->points_plus ?>" /> : <input type="text" size="2" name="points_minus[<?php echo $team->id ?>]" value="<?php echo $team->points_minus ?>" />
				<?php endif; ?>
			</td>
			<td class="num">
				<input type="text" size="2" name="add_points[<?php echo $team->id ?>]" value="<?php echo $team->add_points ?>" id="add_points_<?php echo $team->id ?>" onblur="Leaguemanager.saveAddPoints(<?php echo $team->id ?>)" /><span class="loading" id="loading_<?php echo $team->id ?>"></span>
			</td>
			<input type="hidden" name="team_id[<?php echo $team->id ?>]" value="<?php echo $team->id ?>" />
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		<?php if ( $league->team_ranking == 'manual' ) : ?>
		<script type='text/javascript'>
		// <![CDATA[
			Sortable.create("the-list-standings",
			{dropOnEmpty:true, tag: 'tr', ghosting:true, constraint:false, onUpdate: function() {Leaguemanager.saveStandings(Sortable.serialize('the-list-standings'))} });
		    //")
		// ]]>
		</script>
		<?php endif; ?>
		
		<?php if ( $league->point_rule == 'manual' ) : ?>
			<input type="hidden" name="updateLeague" value="teams_manual" />
			<p class="submit"><input type="submit" value="<?php _e( 'Save Standings', 'leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
	
	<h3><?php _e( 'Match Plan','leaguemanager' ) ?></h3>

	<?php if ( !empty($season['num_match_days']) ) : ?>
	<!-- Bulk Editing of Matches -->
	<form action="admin.php" method="get" style="float: right;">
		<input type="hidden" name="page" value="leaguemanager" />
		<input type="hidden" name="subpage" value="match" />
		<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
		<input type="hidden" name="season" value="<?php echo $season['name'] ?>" />
		
		<select size="1" name="match_day">
			<?php for ($i = 1; $i <= $season['num_match_days']; $i++) : ?>
			<option value="<?php echo $i ?>"><?php printf(__( '%d. Match Day', 'leaguemanager'), $i) ?></option>
			<?php endfor; ?>
		</select>
		<input type="submit" value="<?php _e('Edit Matches', 'leaguemanager'); ?>" class="button-secondary action" />
	</form>
	<?php endif; ?>
	<form id="competitions-filter" action="" method="post">
		<?php wp_nonce_field( 'matches-bulk' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em; clear: none;">
			<!-- Bulk Actions -->
			<select name="action2" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
			
			<?php if ( !empty($season['num_match_days']) ) : ?>
			<select size='1' name='match_day'>
			<?php $selected = ( !isset($_POST['doaction3']) || (isset($_POST['doaction3']) && $_POST['match_day'] == -1) ) ? ' selected="selected"' : ''; ?>
			<option value="-1"<?php echo $selected ?>><?php _e( 'Show all Matches', 'leaguemanager' ) ?></option>
			<?php for ($i = 1; $i <= $season['num_match_days']; $i++) : ?>
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
			<th><?php _e( 'ID', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<?php if ( !empty($league->groups) ) : ?><th class="num"><?php _e( 'Group', 'leaguemanager' ) ?></th><?php endif; ?>
			<th><?php _e( 'Match','leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
			<th><?php _e( 'Score', 'leaguemanager' ) ?></th>
			<?php do_action( 'matchtable_header_'.$league->sport ); ?>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php if ( $matches = $leaguemanager->getMatches( $match_search ) ) : $class2 = ''; ?>
			<?php foreach ( $matches AS $match ) : $class2 = ( 'alternate' == $class2 ) ? '' : 'alternate'; ?>
			<?php $title = ( isset($match->title) && !empty($match->title) ) ? $match->title : $team_list[$match->home_team]['title'] . " - " . $team_list[$match->away_team]['title']; ?>
			<?php $title = apply_filters( 'leaguemanager_matchtitle_'.$league->sport, $title, $match, $team_list ); ?>

			<tr class="<?php echo $class2 ?>">
				<th scope="row" class="check-column"><input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" /><input type="hidden" name="home_team[<?php echo $match->id ?>]" value="<?php echo $match->home_team ?>" /><input type="hidden" name="away_team[<?php echo $match->id ?>]" value="<?php echo $match->away_team ?>" /><input type="checkbox" value="<?php echo $match->id ?>" name="match[<?php echo $match->id ?>]" /></th>
				<td><?php echo $match->id ?></td>
				<td><?php echo ( substr($match->date, 0, 10) == '0000-00-00' ) ? 'N/A' : mysql2date(get_option('date_format'), $match->date) ?></td>
				<?php if ( !empty($league->groups) ) : ?><td class="num"><?php echo $match->group ?></td><?php endif; ?>
				<td><a href="admin.php?page=leaguemanager&amp;subpage=match&amp;edit=<?php echo $match->id ?>&amp;season=<?php echo $season['name'] ?>"><?php echo $title ?></a></td>
				<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
				<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
				<td>
					<input class="points" type="text" size="2" id="home_points_<?php echo $match->id ?>_regular" name="home_points[<?php echo $match->id ?>]" value="<?php echo $match->home_points ?>" /> : <input class="points" type="text" size="2" id="away_points[<?php echo $match->id ?>]" name="away_points[<?php echo $match->id ?>]" value="<?php echo $match->away_points ?>" />
				</td>
				<?php do_action( 'matchtable_columns_'.$league->sport, $match ) ?>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>

		<?php do_action ( 'leaguemanager_match_administration_descriptions' ) ?>	

		<?php if ( $matches ) : ?>
			<input type="hidden" name="league_id" value="<?php echo $league->id ?>" />
			<input type="hidden" name="updateLeague" value="results" />
			<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Update Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
		<?php endif; ?>
	</form>
</div>
