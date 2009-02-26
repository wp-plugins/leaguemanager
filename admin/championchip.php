<?php
$league_id = $_GET['league_id'];
$num_groups = $leaguemanager->getNumMatchDays( $league_id );
$num_advance = 2; // First and Second of each group qualify for finals
$num_first_round = $num_groups * $num_advance; // number of teams in first final round -> determines number of finals

$num_teams = 2; // start with final on top
?>

<?php echo "This feature has not been implemented yet" ?>


<div class="wrap">
	<h2><?php _e( 'Championchip Finals', 'leaguemanager' ) ?></h2>
	
	<form method="post" name="finals" action="">
	<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
	
	<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><?php _e( 'Round', 'leaguemanger' ) ?></th>
		<td><?php _e( 'Matches', 'leaguemanager' ) ?></td>
	</tr>
	<tbody id="the-list" class="form-table">
	<?php while ( $num_teams <= $num_first_round ) : $num_matches = $num_teams/2; $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
		<tr class="<?php echo $class ?>">
			<th scope="row"><?php echo $leaguemanager->getFinalName($num_teams) ?></th>
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
	
	<p class="submit"><input type="submit" name="updateResults" value="<?php _e( 'Save Finals','leaguemanager' ) ?> &raquo;" class="button" /></p>
	</form>
</div>