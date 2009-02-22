var Leaguemanager = new Object();

Leaguemanager.saveStandings = function(ranking) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_team_standings" );
	ajax.setVar( "ranking", ranking );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.saveAddPoints = function(team_id) {
	Leaguemanager.isLoading('loading_' + team_id);
	var points = document.getElementById('add_points_' + team_id).value;
	
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_add_points" );
	ajax.setVar( "team_id", team_id );
	ajax.setVar( "points", points );
	ajax.onError = function() { alert('Ajax error on saving standings'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.isLoading = function(id) {
	document.getElementById(id).style.display = 'inline';
	document.getElementById(id).innerHTML="<img src='"+LeagueManagerAjaxL10n.pluginUrl+"/images/loading.gif' />";
}
Leaguemanager.doneLoading = function(id) {
	document.getElementById(id).style.display = 'none';
}


Leaguemanager.ajaxSaveGoals = function(match_id) {
	var goal_time = document.getElementsByName('goal_time_' + match_id);
	var goal_scorer = document.getElementsByName('goal_scorer_' + match_id);
	var goal_standing = document.getElementsByName('goal_standing_' + match_id);
	
	var goals = '';
	for ( i = 0; i < goal_time.length; i++ ) {
		if ( goal_time[i].value != '' && goal_scorer[i].value != '' && goal_standing[i].value != '' )
			goals += goal_time[i].value + ";" + goal_scorer[i].value + ";" + goal_standing[i].value + '-new-';
	}

	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_goals" );
	ajax.setVar( "match_id", match_id );
	ajax.setVar( "goals", goals );
	ajax.onError = function() { alert('Ajax error on saving goals'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.ajaxSaveCards = function(match_id) {
	var card_time = document.getElementsByName('card_time_' + match_id);
	var card_player = document.getElementsByName('card_player_' + match_id);
	var card_type = document.getElementsByName('card_type_' + match_id);
	
	var cards = '';
	for ( i = 0; i < card_time.length; i++ ) {
		if ( card_time[i].value != '' && card_player[i].value != '' && card_type[i].value != '' )
			cards += card_time[i].value + ";" + card_player[i].value + ";" + card_type[i].value + '-new-';
	}

	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_cards" );
	ajax.setVar( "match_id", match_id );
	ajax.setVar( "cards", cards );
	ajax.onError = function() { alert('Ajax error on saving cards'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}

Leaguemanager.ajaxSaveExchanges = function(match_id) {
	var exchange_time = document.getElementsByName('exchange_time_' + match_id);
	var exchange_in = document.getElementsByName('exchange_in_' + match_id);
	var exchange_out = document.getElementsByName('exchange_out_' + match_id);
	
	var exchanges = '';
	for ( i = 0; i < exchange_time.length; i++ ) {
		if ( exchange_time[i].value != '' && exchange_in[i].value != '' && exchange_out[i].value != '' )
			exchanges += exchange_time[i].value + ";" + exchange_in[i].value + ";" + exchange_out[i].value + '-new-';
	}

	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_save_exchanges" );
	ajax.setVar( "match_id", match_id );
	ajax.setVar( "exchanges", exchanges );
	ajax.onError = function() { alert('Ajax error on saving exchanges'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();
}