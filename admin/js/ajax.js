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


Leaguemanager.ajaxSaveShotGoals = function(match_id) {
}

Leaguemanager.ajaxSaveCards = function(match_id) {
}

Leaguemanager.ajaxSavePlayerExchanges = function(match_id) {
}