<?php
$league_id = $_GET['league_id'];
$num_groups = $leaguemanager->getNumMatchDays( $league_id );
$num_advance = 2; // First and Second of each group qualify for finals
$num_teams_final_start = $num_groups * $num_advance; // number of teams in first final round -> determines number of finals
?>

<?php $num_teams = $num_teams_final_start ?>
<?php while ( $num_teams > 2 ) : ?>
	<?php $num_matches = $num_teams/2; ?>
	
	<?php $num_teams = $num_matches; ?>
<?php endwhile ?>