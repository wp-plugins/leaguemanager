<?php
$league_id = $_GET['league_id'];
$league = $leaguemanager->getLeague( $league_id );

$num_groups = 8;//$leaguemanager->getNumMatchDays( $league_id );
$num_advance = 2; // First and Second of each group qualify for finals
$num_first_round = $num_groups * $num_advance; // number of teams in first final round -> determines number of finals

$num_teams = 2; // start with final on top
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
	<?php while ( $num_teams <= $num_first_round ) : $num_matches = $num_teams/2; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row"><strong><?php echo $leaguemanager->getFinalName($num_teams) ?></strong></th>
			<?php for ( $i = 1; $i <= $num_matches; $i++ ) : ?>
			<td colspan="<?php echo $num_first_round / $num_matches ?>" style="text-align: center;">
				<?php echo $leaguemanager->getFinalName($num_teams) . " No. " . $i ?>
			</td>
			<?php endfor; ?>
		</tr>
		<?php $num_teams = $num_teams * 2; ?>
	<?php endwhile ?>
	</tbody>
	</table>
	
	<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Save Finals Results','leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>