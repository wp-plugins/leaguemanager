<?php
/** Widget class for the WordPress plugin LeagueManager
* 
* @author 	Kolja Schleich
* @package	LeagueManager
* @copyright 	Copyright 2008-2009
*/

class LeagueManagerWidget extends LeagueManager
{
	/**
	 * index for matches in widget
	 *
	 * @var array
	 */
	var $match_index = array( 'next' => 0, 'prev' => 0 );


	/**
	 * array of matches
	 *
	 * @var array
	 */
	var $matches = array( 'next' => false, 'prev' => false );


	/**
	 * initialize
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
		$this->loadOptions();
	}
	function LeagueManagerWidget()
	{
		$this->__construct();
	}
	
	
	/**
	 * register widget
	 *
	 * @param none
	 */
	function register()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		// Add options
		add_option( 'leaguemanager_widget', array(), 'Leaguemanager Widget Options', 'yes' );
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'leaguemanager' ) .' - '. $league['title'];
			$widget_id = sanitize_title($name);
			$widget_ops = array('classname' => 'widget_leaguemanager', 'description' => __('League results and upcoming matches at a glance', 'leaguemanager') );
			wp_register_sidebar_widget( sanitize_title($name), $name , array( &$this, 'display' ), $widget_ops );
			wp_register_widget_control( sanitize_title($name), $name, array( &$this, 'control' ), array('width' => 250, 'height' => 200), array( 'league_id' => $league_id, 'widget_id' => $widget_id ) );
		
			$this->options[$widget_id] = $league_id;
		}
		update_option( 'leaguemanager_widget', $this->options );
	}
	
	
	/**
	 * load Options
	 *
	 * @param none
	 * @return void
	 */
	function loadOptions()
	{
		$this->options = get_option( 'leaguemanager_widget' );
	}
	
	
	/**
	 * get index for current match
	 *
	 * @param string $type next|prev
	 * @return the index
	 */
	function getMatchIndex( $type )
	{
		return $this->match_index[$type];
	}
	
	
	/**
	 * set index for current match
	 *
	 * @param int $index
	 * @param string $type
	 * @return void
	 */
	function setMatchIndex( $index, $type )
	{
		$this->match_index[$type] = $index;
	}
	
		
	/**
	 * get matches from class
	 *
	 * @param string $index 'next' | 'prev'
	 * @return array of matches
	 */
	function getMatches( $index )
	{
		return $this->matches[$index];
	}
	
	
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function display( $args )
	{
		$widget_id = $args['widget_id'];
		
		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'league_id' => $this->options[$widget_id]
			
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$this->league_id = $league_id;
		$options = $this->options[$league_id];
		
		$league = parent::getLeague( $league_id );
		echo $before_widget . $before_title . $league->title . $after_title;
		
		echo "<div class='leaguemanager_widget'>";
		if ( $options['match_display'][0] != 'none' ) {
			$show_prev_matches = $show_next_matches = false;
			if ( $options['match_display'][0] == 'prev_matches' )
				$show_prev_matches = true;
			elseif ( $options['match_display'][0] == 'next_matches' )
				$show_next_matches = true;
			elseif ( $options['match_display'][0] == 'all' )
				$show_prev_matches = $show_next_matches = true;
			
			$match_limit = ( $options['match_display'][2] == 'all' && $options['match_limit'] > 0 ) ? $options['match_limit'] : false;			
			
			if ( $show_next_matches ) {
				echo "<div id='next_matches'>";
				$this->showNextMatchBox($this->league_id, $match_limit);
				echo "</div>";
			}

			if ( $show_prev_matches ) {
				echo "<div id='prev_matches'>";
				$this->showPrevMatchBox($this->league_id, $this->match_limit);
				echo "</div>";
			}
	
		}
		
		if ( $options['table_display'][0] != 'none' ) {
			$show_logo = ( $options['table_display'][1] == 1 ) ? true : false;
			$mode = $options['table_display'][0];
			echo "<h4 class='standings'>". __( 'Table', 'leaguemanager' ). "</h4>";
			$shortcodes = new LeagueManagerShortcodes();
			echo $shortcodes->showStandings( array('league_id' => $league_id, 'mode' => $mode, 'logo' => $show_logo) );
		}
		//if ( $options['info'] AND '' != $options['info'] )
		//	echo "<li class='info'><a href='".get_permalink( $options['info'] )."'>".__( 'More Info', 'leaguemanager' )."</a></li>";

		echo "</div>";
		echo $after_widget;
	}


	/**
	 * show next match box
	 *
	 * @param int $league_id
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showNextMatchBox($league_id, $match_limit, $echo = true)
	{
		$options = $this->options[$league_id];
		$search = "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) <= 0";
		if ( 'home' == $options['match_display'][2] )
			$search .= parent::buildHomeOnlyQuery($league_id);
			
		$matches = parent::getMatches( $search, $match_limit );
		if ( $matches ) {
			$this->teams = parent::getTeams( 'league_id = '.$league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('next');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			$logos = ( 1 == $options['match_display'][1] ) ? true : false;
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"next\", \"next\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"prev\", \"next\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
	
			$out = "<div id='next_match_box' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Next Match', 'leaguemanager' )."$next_link</h4>";
						
			$out .= "<div class='match' id='match-".$match->id."'>";
				
			if ( $logos && $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".parent::getImageUrl($this->teams[$match->home_team]['logo'])."' alt=".$this->teams[$match->home_team]['title']." />";
				$away_team = "<img src='".parent::getImageUrl($this->teams[$match->away_team]['logo'])."' alt=".$this->teams[$match->away_team]['title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['title'];
				$away_team = $this->teams[$match->away_team]['title'];
				$spacer = ' &#8211; ';
			}
							
			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			$out .= "<p class='match'>". $home_team . $spacer . $away_team."</p>";
							
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
			
			$time = ( '00:00' == $match->hour.":".$match->minutes ) ? '' : mysql2date(get_option('time_format'), $match->date);
			$out .= "<p class='date'>".mysql2date($options['date_format'], $match->date)." <span class='time'>".$time."</span> <span class='location'>".$match->location."</span></p>";
			
			$out .= "</div></div>";
		
	
			if ( $echo )
				echo $out;
				
			return $out;
		}
	}
	
	
	/**
	 * show previous match box
	 *
	 * @param int $league_id
	 * @param int $match_limit
	 * @param boolean $echo (optional)
	 * @return void
	 */
	function showPrevMatchBox($league_id, $match_limit, $echo = true)
	{
		$options = $this->options[$league_id];	
		$search = "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) > 0";
		if ( 'home' == $options['match_display'][2] )
			$search .= parent::buildHomeOnlyQuery($league_id);

		$matches = parent::getMatches( $search, $match_limit, 'DESC' );
		if ( $matches ) {
			$this->teams = parent::getTeams( 'league_id = '.$league_id, 'ARRAY' );
			$curr = $this->getMatchIndex('prev');
			$match = $matches[$curr];
			$match_limit_js = ( $match_limit ) ? $match_limit : 'false';
			
			$logos = ( 1 == $options['match_display'][1] ) ? true : false;
			
			$next_link = $prev_link = '';
			if ( $curr < count($matches) - 1 ) {
				$next_link = "<a class='next' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"next\", \"prev\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_right.png' alt='&raquo;' /></a>";
			}
			if ( $curr > 0 ) {
				$prev_link = "<a class='prev' href='#null' onclick='Leaguemanager.setMatchIndex(".$curr.", \"prev\", \"prev\", ".$league_id.", \"".$match_limit_js."\"); return false'><img src='".LEAGUEMANAGER_URL."/images/arrow_left.png' alt='&laquo;' /></a>";
			}
					
			$out = "<div id='prev_match_box' class='match_box'>";
			$out .= "<h4>$prev_link".__( 'Last Match', 'leaguemanager' )."$next_link</h4>";
										
			
			$out .= "<div class='match' id='match-".$match->id."'>";
			
			$match->hadOvertime = ( !empty($match->overtime) && !parent::isGymnasticsLeague($league_id) ) ? true : false;
			$match->hadPenalty = ( !empty($match->penalty) && !parent::isGymnasticsLeague($league_id) ) ? true : false;

			$match->overtime = maybe_unserialize($match->overtime);
			$match->penalty = maybe_unserialize($match->penalty);
					
			if ( $logos && $this->teams[$match->home_team]['logo'] != '' && $this->teams[$match->away_team]['logo'] != '' ) {
				$home_team = "<img src='".parent::getImageUrl($this->teams[$match->home_team]['logo'])."' alt=".$this->teams[$match->home_team]['title']." />";
				$away_team = "<img src='".parent::getImageUrl($this->teams[$match->away_team]['logo'])."' alt=".$this->teams[$match->away_team]['title']." />";
				$spacer = ' ';
			} else {
				$home_team = $this->teams[$match->home_team]['title'];
				$away_team = $this->teams[$match->away_team]['title'];
				$spacer = ' &#8211; ';
			}

			if ( $this->teams[$match->home_team]['website'] != '' )
				$home_team = "<a href='http://".$this->teams[$match->home_team]['website']."' target='_blank'>".$home_team."</a>";
			if ( $this->teams[$match->away_team]['website'] != '' )
				$away_team = "<a href='http://".$this->teams[$match->away_team]['website']."' target='_blank'>".$away_team."</a>";
								
			$out .= "<p class='match'>". $home_team . $spacer . $away_team."</p>";
			
			$out .= "<p class='match_day'>".sprintf(__("<strong>%d.</strong> Match Day", 'leaguemanager'), $match->match_day)."</p>";
		
			if ( $match->hadPenalty )
				$score = sprintf("%d - %d", $match->penalty['home'], $match->penalty['away'])." "._c( 'o.P.|on penalty', 'leaguemanager' );
			elseif ( $match->hadOvertime )
				$score = sprintf("%d - %d", $match->overtime['home'], $match->overtime['away'])." "._c( 'AET|after extra time', 'leaguemanager' );
			else
				$score = sprintf("%d - %d", $match->home_points, $match->away_points);
			$out .= "<p class='result'>".$score."</p>";
							
			if ( $match->post_id != 0 && $options['match_report'] == 1 )
				$out .=  "<p class='report'><a href='".get_permalink($match->post_id)."'>".__( 'Report', 'leaguemanager' )."&raquo;</a></p>";
					
			$out .= "</div></div>";
		
			if ( $echo )
				echo $out;
			
			return $out;
		}
	}
	
	
	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function control( $args )
	{
		extract( $args );
		
		$options = get_option( 'leaguemanager_widget' );
		$options[$widget_id] = $league_id;
		update_option( 'leaguemanager_widget', $options );
		
		echo '<p>'.sprintf(__( "The Widget Settings are controlled via the <a href='%s'>League Settings</a>", 'leaguemanager'), 'admin.php?page=leaguemanager/settings.php&league_id='.$league_id).'</p>';
	}
	
	
	/**
	 * get all active leagues
	 *
	 * @param none
	 * @return array
	 */
	function getActiveLeagues()
	{
		return ( parent::getLeagues( false, 'WHERE active = 1' ) );
	}
}

?>