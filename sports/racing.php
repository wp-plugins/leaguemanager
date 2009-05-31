<?php
/**
 * Racing Class 
 * 
 * @author 	Kolja Schleich
 * @package	LeagueManager
 * @copyright 	Copyright 2008-2009
*/
class LeagueManagerRacing extends LeagueManager
{
	/**
	 * sports keys
	 *
	 * @var string
	 */
	var $key = 'racing';


	/**
	 * load specific settings
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		add_filter( 'leaguemanager_sports', array(&$this, 'sports') );
		add_filter( 'league_menu_'.$this->key, array(&$this, 'menu'), 10, 3 );

		add_action('leaguemanager_custom_standings_'.$this->key, array(&$this, 'standingsTable'));
		add_action('leaguemanager_custom_matches_'.$this->key, array(&$this, 'matchTable'));
		add_action('leaguemanager_edit_match_'.$this->key, array(&$this, 'matchForm'), 10, 18);
	}
	function LeagueManagerElectronic()
	{
		$this->__construct();
	}


	/**
	 * add sports to list
	 *
	 * @param array $sports
	 * @return array
	 */
	function sports( $sports )
	{
		$sports[$this->key] = __( 'Racing', 'leaguemanager' );
		return $sports;
	}


	/**
	 * add menu page
	 *
	 * @param array $menu
	 * @param int $league_id
	 * @param mixed $season
	 * @return void
	 */
	function menu( $menu, $league_id, $season )
	{
		$menu[$this->key] = array( 'title' => __( 'Race Results', 'leaguemanager' ), 'file' => false, 'show' => false, 'callback' => array(&$this, 'page') );
		return $menu;
	}


	/**
	 * custom standings table
	 *
	 * @param object $league
	 * @return void
	 */
	function standingsTable( $league )
	{
		global $leaguemanager;
		$season = $leaguemanager->getSeason(&$league);

		echo "<ul>";
		foreach ( $leaguemanager->getTeams("`league_id` = {$league->id} AND `season` = '".$season['name']."'") AS $team ) {
			echo "<li style='float: left; margin-left: 1em;'><a href='admin.php?page=leaguemanager&subpage=team&edit=".$team->id."'>".$team->title."</a></li>";
		}
		echo "</ul>";
		echo "<br style='clear: both;' />";
	}


	/**
	 * custom match table
	 *
	 * @param none
	 * @return void
	 */
	function matchTable( $league )
	{
		global $leaguemanager;
		$match_search = '`league_id` = "'.$league->id.'" AND `final` = ""';
		$season = $leaguemanager->getSeason(&$league);
?>
		<form id="competitions-filter" action="" method="post">
			<?php wp_nonce_field( 'matches-bulk' ) ?>
		
		<div class="tablenav" style="margin-bottom: 0.1em; clear: none;">
			<!-- Bulk Actions -->
			<select name="action2" size="1">
				<option value="-1" selected="selected"><?php _e('Bulk Actions') ?></option>
				<option value="delete"><?php _e('Delete')?></option>
			</select>
			<input type="submit" value="<?php _e('Apply'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
		</div>
		<table class="widefat" summary="" title="<?php _e( 'Match Plan','leaguemanager' ) ?>" style="margin-bottom: 2em;">
		<thead>
		<tr>
			<th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('competitions-filter'));" /></th>
			<th><?php _e( 'ID', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Date','leaguemanager' ) ?></th>
			<th><?php _e( 'Event', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Location','leaguemanager' ) ?></th>
			<th><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Begin','leaguemanager' ) ?></th>
			<th><?php _e( 'Results', 'leaguemanager' ) ?></th>
			<?php do_action( 'matchtable_header_'.$league->sport ); ?>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php if ( $matches = $leaguemanager->getMatches( $match_search ) ) : $class2 = ''; ?>
			<?php foreach ( $matches AS $match ) : $class2 = ( 'alternate' == $class2 ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class2 ?>">
				<th scope="row" class="check-column"><input type="hidden" name="matches[<?php echo $match->id ?>]" value="<?php echo $match->id ?>" /><input type="checkbox" value="<?php echo $match->id ?>" name="match[<?php echo $match->id ?>]" />	</th>
				<td><?php echo $match->id ?></td>
				<td><a href="admin.php?page=leaguemanager&amp;subpage=match&amp;edit=<?php echo $match->id ?>&amp;season=<?php echo $season['name'] ?>"><?php echo mysql2date(get_option('date_format'), $match->date) ?></a></td>
				<td><?php echo $match->title ?></a></td>
				<td><?php echo ( '' == $match->location ) ? 'N/A' : $match->location ?></td>
				<td><?php echo $match->racetype ?></td>
				<td><?php echo ( '00:00' == $match->hour.":".$match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date) ?></td>
				<td><a href="admin.php?page=leaguemanager&amp;subpage=<?php echo $this->key ?>&amp;league_id=<?php echo $match->league_id ?>&amp;season=<?php echo $season['name'] ?>&amp;match=<?php echo $match->id ?>"><?php _e( 'Results', 'leaguemanager' ) ?></a></td>
				<?php do_action( 'matchtable_columns_'.$league->sport, &$match ) ?>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		</tbody>
		</table>
		
		</form>
	<?php
	}


	/**
	 * custom match editing form
	 *
	 * @param object $league
	 * @param object $teams
	 * @param arrray $season
	 * @param int $max_matches
	 * @param array $m_day
	 * @param array $m_month
	 * @param array $m_year
	 * @param array $home_team
	 * @paramm array $away_team
	 * @param array $location
	 * @param array $begin_hour
	 * @param array $begin_minutes
	 * @param array $match_id
	 * @param string $mode
	 * @param string $final
	 * @param string $submit_title
	 * @return void
	 */
	function matchForm( $league, $teams, $season, $max_matches, $m_day, $m_month, $m_year, $home_team, $away_team, $location, $begin_hour, $begin_minutes, $match_id, $mode, $final, $submit_title, $custom, $edit  )
	{
		global $lmLoader;
		$admin = $lmLoader->getAdminPanel();
	?>
		<form action="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id?>&amp;season=<?php echo $season['name'] ?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<table class="widefat">
				<thead>
					<tr>
						<?php if ( !$edit ) : ?>
						<th scope="col"><?php _e( 'Add', 'leaguemanager' ) ?></th>
						<?php endif; ?>
						<th scope="col"><?php _e( 'Date', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Event', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Location','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Race Type', 'leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Begin','leaguemanager' ) ?></th>
						<th scope="col"><?php _e( 'Description', 'leaguemanger' ) ?></th>
					</tr>
				</thead>
				<tbody id="the-list" class="form-table">
				<?php for ( $i = 1; $i <= $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class; ?>">
					<?php if ( !$edit ) : ?>
					<td><input type="checkbox" name="add_match[<?php echo $i ?>]" id="add_match_<?php echo $i ?>" /></td>
					<?php endif; ?>
					<td><?php echo $admin->getDateSelection( $m_day[0], $m_month[0], $m_year[0], $i) ?></td>
					<td><input type="text" size="15" name="custom[<?php echo $i ?>][title]" id="title_<?php echo $i ?>" value="<?php echo $custom[$i]['title'] ?>" /></td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $location[$i] ?>" size="30" /></td>
					<td><input type="text" size="15" name="custom[<?php echo $i ?>][racetype]" id="racetype_<?php echo $i ?>" value="<?php echo $custom[$i]['racetype'] ?>" /></td>
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
					<td><textarea name="custom[<?php echo $i ?>][description]" id="description_<?php echo $i ?>" cols="20" rows="5"><?php echo $custom[$i]['description'] ?></textarea></td>
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
	<?php
	}


	/**
	 * display reslts managing page
	 *
	 * @param none
	 * @return void
	 */
	function page()
	{
		global $leaguemanager;

		$league_id = (int)$_GET['league_id'];
		$match_id = (int)$_GET['match'];
		$season = $_GET['season'];

		if ( isset($_POST['save_results']) ) {
			$this->saveResults( $_POST['racer'], $_POST['racer_name'], $_POST['category'], $_POST['result'], $_POST['info'], $match_id );
			$leaguemanager->printMessage();
		}
		$league = $leaguemanager->getLeague($league_id);
		$match = $leaguemanager->getMatch($match_id);

	?>
	<div class="wrap">
		<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;league_id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Race Results', 'leaguemanager' ) ?></p>
		<h2><?php printf(__( 'Racing Results - %s', 'leaguemanager' ), $match->title) ?></h2>

		<?php foreach ( $leaguemanager->getTeams("`league_id` = {$league_id} AND `season` = '".$season."'") AS $team ) : ?>
		<?php if ( isset($team->teamRoster) && !empty($team->teamRoster) ) : ?>

		<h3><?php echo $team->title ?></h3>

		<form action="" method="post">
		<table class="widefat">
		<thead>
		<tr>
			<th><?php _e( 'Name', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Category', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Result', 'leaguemanager' ) ?></th>
			<th><?php _e( 'Other Info', 'leaguemanager' ) ?></th>
		</tr>
		</thead>
		<tbody id="the-list" class="form-table">
		<?php foreach ( $team->teamRoster AS $roster ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<td><input type="hidden" name="racer[<?php echo $roster->id ?>]" value="<?php echo $roster->id ?>" /><input type="hidden" name="racer_name[<?php echo $roster->id ?>]" value="<?php echo $roster->name ?>" /><?php echo $roster->name ?></td>
			<td><input type="text" name="category[<?php echo $roster->id ?>]" id="category_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['category'] ?>" /></td>
			<td><input type="text" name="result[<?php echo $roster->id ?>]" id="result_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['result'] ?>" /></td>
			<td><input type="text" name="info[<?php echo $roster->id ?>]" id="info_<?php echo $roster->id ?>" value="<?php echo $match->raceresult[$roster->id]['info'] ?>" /></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>

		<input type="hidden" name="match_id" value="<?php echo $match->id ?>" />
		<p class="submit"><input type="submit" name="save_results" value="<?php _e( 'Save Team Results', 'leaguemanager' ) ?>" /></p>
		</form>

		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php
	}


	/**
	 * save race results for one team
	 *
	 * @param array $racer
	 * @param array $racer_name
	 * @param array $category
	 * @param array $results
	 * @param array $info
	 * @param int $match_id
	 * @return true
	 */
	function saveResults($racer, $racer_name, $category, $results, $info, $match_id)
	{
		global $wpdb, $leaguemanager;

		$match = $leaguemanager->getMatch( $match_id );
		$custom = $match->custom;

		$data = isset($custom['raceresult']) ? $custom['raceresult'] : array();
		while ( list($id) = each($racer) ) {
			$data[$id]['name'] = $racer_name[$id];
			$data[$id]['category'] = $category[$id];
			$data[$id]['result'] = $results[$id];
			$data[$id]['info'] = $info[$id];
		}

		$custom['raceresult'] = $data;

		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_matches} SET `custom` = '%s' WHERE `id` = '%d'", maybe_serialize($custom), $match_id ) );
		$leaguemanager->setMessage( __( 'Race Results Saved', 'leaguemanager' ) );
		return true;
	}
}

$racing = new LeagueManagerRacing();
?>
