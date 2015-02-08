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

<table style="width: 100%" class="leaguemanager standingstable" summary="" title="<?php _e( 'Standings', 'leaguemanager' ) .' '.$league->title ?>">
<tr>
	<th class="num"><?php echo _e( 'Pos', 'leaguemanager' ) ?></th>
	<th class="num">&#160;</th>
	<?php if ( $league->show_logo ) : ?>
	<th class="logo">&#160;</th>
	<?php endif; ?>

	<th><?php _e( 'Team', 'leaguemanager' ) ?></th>
	<?php if ( isset($league->standings['pld']) && 1 == $league->standings['pld'] ) : ?>
	<th class="num"><?php _e( 'Pld', 'leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['won']) && 1 == $league->standings['won'] ) : ?>
	<th class="num"><?php echo _e( 'W','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['tie']) && 1 == $league->standings['tie'] ) : ?>
	<th class="num"><?php echo _e( 'T','leaguemanager' ) ?></th>
	<?php endif; ?>
	<?php if ( isset($league->standings['lost']) && 1 == $league->standings['lost'] ) : ?>
	<th class="num"><?php echo _e( 'L','leaguemanager' ) ?></th>
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
	<?php if ( isset($league->standings['pld']) && 1 == $league->standings['pld'] ) : ?>
	<td class='num'><?php echo $team->done_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['won']) && 1 == $league->standings['won'] ) : ?>
	<td class='num'><?php echo $team->won_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['tie']) && 1 == $league->standings['tie'] ) : ?>
	<td class='num'><?php echo $team->draw_matches ?></td>
	<?php endif; ?>
	<?php if ( isset($league->standings['lost']) && 1 == $league->standings['lost'] ) : ?>
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
            $homeTeam = $leaguemanager->getTeam( $next_result->home_team );
            $awayTeam = $leaguemanager->getTeam( $next_result->away_team );
            $homeTeamName = $homeTeam->title;
            $awayTeamName = $awayTeam->title;
            $myMatchDate = mysql2date(get_option('date_format'), $next_result->date);
            $tooltipTitle = 'Next Match: '.$homeTeamName.' - '.$awayTeamName.' ['.$myMatchDate.']';
            $last5 .= '<a href="?match='."$next_result->id".'"  class="N last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
        }
    } else {
        $last5 .= '<a class="N last5-bg" title="Next Match: No Game Scheduled">&nbsp;</a>';
    }

    // Get the latest results
    $results = get_latest_results($team->id, 5);
    foreach ($results as $result)
    {
		$result->hadPenalty = ( isset($result->penalty) && $result->penalty['home'] != '' && $result->penalty['away'] != '' ) ? true : false;
		$result->hadOvertime = ( isset($result->overtime) && $result->overtime['home'] != '' && $result->overtime['away'] != '' ) ? true : false;
		if ( $result->hadPenalty ) {
			$result->homeScore = $result->penalty['home']+$result->overtime['home'];
			$result->awayScore = $result->penalty['away']+$result->overtime['away'];
		} elseif ( $result->hadOvertime ) {
			$result->homeScore = $result->overtime['home'];
			$result->awayScore = $result->overtime['away'];
		} else {
			$result->homeScore = $result->home_points;
			$result->awayScore = $result->away_points;
		}

        $homeTeam = $leaguemanager->getTeam( $result->home_team );
        $awayTeam = $leaguemanager->getTeam( $result->away_team );
        $homeTeamName = $homeTeam->title;
        $awayTeamName = $awayTeam->title;
        $homeTeamScore = $result->homeScore;
        $awayTeamScore = $result->awayScore;
        $myMatchDate = mysql2date(get_option('date_format'), $result->date);
        $tooltipTitle = $homeTeamScore.':'.$awayTeamScore. ' - '.$homeTeamName.' - '.$awayTeamName.' ['.$myMatchDate.']';
        if ($team->id == $result->home_team)
        {
            if ($result->homeScore > $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="W last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
            }
            elseif ($result->homeScore < $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="L last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
            }
            elseif ($result->homeScore == $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="D last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
            }
        }
        elseif ($team->id == $result->away_team)
        {
            if ($result->homeScore < $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="W last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
            }
            elseif ($result->homeScore > $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="L last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
            }
            elseif ($result->homeScore == $result->awayScore)
            {
                $last5 .= '<a href="?match='."$result->id".'"  class="D last5-bg" title="'.$tooltipTitle.'">&nbsp;</a>';
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