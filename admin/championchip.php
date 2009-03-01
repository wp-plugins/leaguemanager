<?php
$league_id = $_GET['league_id'];
$leaguemanager->setLeagueID( $league_id );
$league = $leaguemanager->getLeague( $league_id );

$num_groups = 8;//$leaguemanager->getNumMatchDays( $league_id );
$num_advance = 2; // First and Second of each group qualify for finals
$num_first_round = $num_groups * $num_advance; // number of teams in first final round -> determines number of finals

$num_teams = 2; // start with final on top
$finals = array(); // initialize array of finals for later adding links

?>

<p>This feature is still in development and not functional yet</p>

<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="admin.php?page=leaguemanager"><?php _e( 'Leaguemanager', 'leaguemanager' ) ?></a> &raquo; <a href="admin.php?page=leaguemanager&amp;subpage=show-league&amp;id=<?php echo $league->id ?>"><?php echo $league->title ?></a> &raquo; <?php _e( 'Championchip Finals', 'leaguemanager') ?></p>
	<h2><?php _e( 'Championchip Finals', 'leaguemanager' ) ?></h2>
	
	<form method="post" name="finals" action="">
	<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
	
	<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php _e( 'Round', 'leaguemanger' ) ?></th>
		<th scope="col" colspan="<?php echo $num_first_round - 1 ?>" style="text-align: center;"><?php _e( 'Matches', 'leaguemanager' ) ?></td>
	</tr>
	<tbody id="the-list" class="form-table">
	<?php while ( $num_teams <= $num_first_round ) : ?>
	<?php
		$num_matches = $num_teams/2;
		$class = ( 'alternate' == $class ) ? '' : 'alternate';
		$finalkey = $leaguemanager->getFinalKey($num_teams);
		
		$matches = $leaguemanager->getMatches("`league_id` = '".$league->id."' AND `final` = '".$finalkey."'");
		$teams = $leaguemanager->getTeams( "league_id = '".$league->id."', 'ARRAY' );
		$teams_tmp = $leaguemanager->getFinalTeams( $num_matches, $num_first_round, 'ARRAY' );
		else
			$teams = $leagueamanger->getFinalTeams( $num_matches, $num_first_round );
	?>
		<tr class="<?php echo $class ?>">
			<th scope="row"><strong><?php echo $leaguemanager->getFinalName($num_teams, num_first_round) ?></strong></th>
			<?php for ( $i = 0; $i <= $num_matches-1; $i++ ) : ?>
			<td colspan="<?php echo $num_first_round / $num_matches ?>" style="text-align: center;">
			<?php if ( $matches ) : ?>
				<?php if ( isset($teams[$matches[$i]->home_team]) && isset($teams[$matches[$i]->away_team]) ) : ?>
					<?php echo $teams[$matches[$i]->home_team]['title'] . " <input type='text' size='2' name='home_points[][<?php echo $finalkey ?>][<?php echo $i ?>]' id=home_points_<?php echo $finalkey ?>_<?php echo $i ?>' /> &#8211; <input type='text' size='2' name='away_points[][<?php echo $finalkey ?>][<?php echo $i ?>]' id=away_points_<?php echo $finalkey ?>_<?php echo $i ?>' /> " . $teams[$matches[$i]->away_team]['title'] ?>
				<?php else : ?>
					<?php echo $teams_tmp[$matches[$i]->home_team] . " &#8211; " . $teams_tmp[$matches[$i]->away_team] ?>
				<?php endif; ?>
			<?php else : ?>
				&#8211;
			<?php endif; ?>
			</td>
			<?php endfor; ?>
		</tr>
		<?php $num_teams = $num_teams * 2; ?>
		<?php if ( $num_teams > 2 ) $finals[] = array( 'key' => finalkey, 'name' => $leaguemanager->getFinalName($num_teams), 'num_matches' => $num_matches ); ?>
	<?php endwhile ?>
	</tbody>
	</table>
	
	<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Save Finals Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
	
	<h3><?php _e( 'Add Final Matches' ) ?></h3>
	<ul class="subsubsub">
	<?php foreach ( $finals AS $final ) : ?>
		<li><a href="admin.php?page=leaguemaanger&amp;subpage=match&amp;league_id=<?php echo $league->id ?>&amp;final=<?php echo $final['key'] ?>&amp;num_matches=<?php echo $final['num_matches']"><?php echo $final['name'] ?></a></li>
	<?php endforeach; ?>
	</ul>
</div>