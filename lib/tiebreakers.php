<?php
/**
 * tiebreaks.php
 *
 * @author 		LaMonte Forthun
 * @copyright 	Copyright 2013
 * @version 	0.1
 *
 */

/*
* Update Team Rank and status
*/

function updateRanking( $league_id, $season, $group, $teams, $teamsTmp, $update=true )
{
	global $leaguemanager, $wpdb;

    if ( $update ) {
        $rank = 0;
        $incr = 1;
        $was_tie = false;
        foreach ( $teams AS $key => $team ) {
            $old = $team->rank;
            $oldRank = $team->rank;
            $rank = $key + 1;
            
            if ( $oldRank != 0 ) {
                if ( $rank == $oldRank ){
                    $status = '&#8226;';
                }
                elseif ( $rank < $oldRank ){
                    $status = '&#8593;';
                }
                else{
                    $status = '&#8595;';
                }
            } else {
                $status = '&#8226;';
            }

            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->leaguemanager_teams} SET `rank` = '%d', `status` = '%s' WHERE `id` = '%d'", $rank, $status, $team->id ) );

        }
    }
}


/**
 * Tie-Breaker Rules
 *
 * series of rules to determine tie-breakers in standings
 */


/**
 * determine if two teams are tied based on Head to Head Matches
 *
 * @param object $team
 * @param object $teamKey
 * @param object $team2
 * @param object $team2Key
 * @return int
 */
function headToHeadTwoTeams( $team, $teamKey, $team2, $team2Key )
{
    global $leaguemanager;
    $team1_id = $team->id;
    $team2_id = $team2->id;

    $team1Wins=$team2Wins=0;
    $hthMatches = $leaguemanager->getMatches( "`home_team` = '".$team1_id."' or `away_team` = '".$team1_id."'" );
    foreach ( $hthMatches AS $match ) {
        if ( $match->home_team==$team2_id ) {
            if ( $match->home_points > $match->away_points ) {
                $team2Wins++;
            } else {
                $team1Wins++;
            }
        } elseif ( $match->away_team==$team2_id ) {
            if ( $match->home_points > $match->away_points ) {
                $team1Wins++;
            } else {
                $team2Wins++;
            }
        }
    }

    if( $team1Wins < $team2Wins ) {
        $team->rank = $team2Key;
        $team2->rank = $teamKey;
        $isTied = 0;
    } elseif( $team1Wins > $team2Wins )  {
        $team->rank = $teamKey;
        $team2->rank = $team2Key;
        $isTied = 0;
    } elseif( $team1Wins = $team2Wins )  {
        $isTied = 1;
    }
    return $isTied;
}

/**
 * determine if two teams are tied based on WinPercentage
 *
 * @param object $team
 * @param object $teamKey
 * @param object $team2
 * @param object $team2Key
 * @return int
 */
function winPercentage( $team, $teamKey, $team2, $team2Key )
{
    global $leaguemanager;
    $team1_id = $team->id;
    $team2_id = $team2->id;

    foreach ( $teams AS $key => $row ) {
        $WinPercTemp =  round(((($row->won_matches)+($row->draw_matches)) > 0 ? (($row->done_matches) > 0 ? (((($row->won_matches)+(($row->draw_matches)/2))/($row->done_matches))) : 100) : 0),3);
        $WinPerc[$key] =  number_format((float)($WinPercTemp == 1 ? 1.000 : $WinPercTemp),3,'.','');
    }

    $team1Wins=$team2Wins=0;
    $hthMatches = $leaguemanager->getMatches( "`home_team` = '".$team1_id."' or `away_team` = '".$team1_id."'" );
    foreach ( $hthMatches AS $match ) {
        if ( $match->home_team==$team2_id ) {
            if ( $match->home_points > $match->away_points ) {
                $team2Wins++;
            } else {
                $team1Wins++;
            }
        } elseif ( $match->away_team==$team2_id ) {
            if ( $match->home_points > $match->away_points ) {
                $team1Wins++;
            } else {
                $team2Wins++;
            }
        }
    }

    if( $team1Wins < $team2Wins ) {
        $team->rank = $team2Key;
        $team2->rank = $teamKey;
        $isTied = 0;
    } elseif( $team1Wins > $team2Wins )  {
        $team->rank = $teamKey;
        $team2->rank = $team2Key;
        $isTied = 0;
    } elseif( $team1Wins = $team2Wins )  {
        $isTied = 1;
    }
    return $isTied;
}

?>