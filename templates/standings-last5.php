<?php
/**
Template page for the standings table in extended form (default)

The following variables are usable:
	
	$league: contains data about the league
	$teams: contains all teams of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>

<?php if ( isset($_GET['team']) && !$widget ) : ?>
	<?php leaguemanager_team($_GET['team']); ?>
<?php else : ?>

<?php if ( $teams ) : ?>

<table class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _c( 'Pos|Position', 'leaguemanager' ) ?></th>
	<th class="num">&#160;</th>
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>
	
	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<?php if ( 1 == $league->standings['pld'] ) : ?>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['won'] ) : ?>
	<th class="num"><?php echo _c( 'W|Won','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['tie'] ) : ?>
	<th class="num"><?php echo _c( 'T|Tied','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['lost'] ) : ?>
	<th class="num"><?php echo _c( 'L|Lost','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php do_action( 'leaguemanager_standings_header_'.$league->sport ) ?>
	<th class="num"><?php _e( 'Pts', 'leaguemanager' ) ?></th>
	<th width="100" class="last5"><?php _e( 'Last 5', 'leaguemanager' ) ?></th>
</tr>
<?php if ( $teams ) : ?>
<?php foreach( $teams AS $team ) : ?>

<tr class='<?php echo $team->class ?>'>
	<td class='rank'><?php echo $team->rank ?></td>
	<td class="num"><?php echo $team->status ?></td>
	<?php if ( $league->show_logo ) : ?>
	<td class="logo">
		<?php if ( $team->logo != '' ) : ?>
		<img src='<?php echo $team->logoURL ?>' alt='<?php _e('Logo','leaguemanager') ?>' title='<?php _e('Logo','leaguemanager')." ".$team->title ?>' />
		<?php endif; ?>
	</td>
	<?php endif; ?>
	
	<td><a href="<?php echo $team->pageURL ?>"><?php echo $team->title ?></a></td>
	<?php if ( 1 == $league->standings['pld'] ) : ?>
	<td class='num'><?php echo $team->done_matches ?></td>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['won'] ) : ?>
	<td class='num'><?php echo $team->won_matches ?></td>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['tie'] ) : ?>
	<td class='num'><?php echo $team->draw_matches ?></td>
	<?php endif; ?>
	<?php if ( 1 == $league->standings['lost'] ) : ?>
	<td class='num'><?php echo $team->lost_matches ?></td>
	<?php endif; ?>
	<?php do_action( 'leaguemanager_standings_columns_'.$league->sport, $team, $league->point_rule ) ?>
	<td class='num'><?php echo $team->points ?></td>

<?php
// Show latest results if enabled
// Open the td tag
    $last5 = '';

// Get Next Match
    $next_results = get_next_match($team->id, 1);
    $last5 = '<td style="text-align: right;" class="last5Icon">';
    if ( $next_results ) {
        foreach ($next_results as $next_result)
        {
            $last5 .= '<a href="?match='."$next_result->id".'"  class="N last5-bg">&nbsp;</a>';
        }
    } else {
        $last5 .= '<a class="N last5-bg">&nbsp;</a>';
    }
    
    // Get the latest results
    $results = get_latest_results($team->id, 5);
    foreach ($results as $result)
    {
        if ($team->id == $result->home_team)
        {
            if ($result->home_points > $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="W last5-bg">&nbsp;</a>';
            }
            elseif ($result->home_points < $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="L last5-bg">&nbsp;</a>';
            }
            elseif ($result->home_points == $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="D last5-bg">&nbsp;</a>';
            }
        }
        elseif ($team->id == $result->away_team)
        {
            if ($result->home_points < $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="W last5-bg">&nbsp;</a>';
            }
            elseif ($result->home_points > $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="L last5-bg">&nbsp;</a>';
            }
            elseif ($result->home_points == $result->away_points)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="D last5-bg">&nbsp;</a>';
            }
        }
    }

    // Close the td tag
    $last5 .= '</td>';
    echo $last5;
?>


</tr>
<?php endforeach; ?>
<?php endif; ?>
</table>

<?php endif; ?>
<?php endif; ?>