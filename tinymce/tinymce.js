function addAttributes () {
	document.getElementById('match_tag').setAttribute("onChange", "LeagueManagerAjaxShowMatchDateForm('match_date_form', getSelectedValue('match_tag'))", 1);
}
function getSelectedValue( el_id ) {
 	return document.getElementById(el_id).value;
}
function LeagueManagerAjaxShowMatchDateForm( el_id, leagueId ) {
	var ajax = new sack(LeagueManagerAjaxL10n.requestUrl);
	ajax.execute = 1;
	ajax.method = 'POST';
	ajax.setVar( "action", "leaguemanager_show_match_date_selection" );
	ajax.setVar( "el_id", el_id );
	ajax.setVar( "league_id", leagueId );
	ajax.onError = function() { alert('Ajax error on saving group'); };
	ajax.onCompletion = function() { return true; };
	ajax.runAJAX();

	return true;
}


function init() {
	tinyMCEPopup.resizeToInnerSize();
}


function LeagueManagergetCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertLeagueManagerLink() {
	
	var tagtext;
	
	var table = document.getElementById('table_panel');
	var matches = document.getElementById('match_panel');
	var crosstable = document.getElementById('crosstable_panel');
	
	// who is active?
	if (table.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('table_tag').value;
		
		if (leagueId != 0)
			tagtext = "[leaguestandings=" + leagueId + "]";
		else
			tinyMCEPopup.close();
	}
	
	if (matches.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('match_tag').value;
		var match_date = document.getElementById('match_date').value;
		
		if (leagueId != 0)
			tagtext = "[leaguematches=" + leagueId + "," + match_date + "]";
		else
			tinyMCEPopup.close();
	}
		
	if (crosstable.className.indexOf('current') != -1) {
		var leagueId = document.getElementById('crosstable_tag').value;
		var showtype = LeagueManagergetCheckedValue(document.getElementsByName('crosstable_showtype'));
		
		if (leagueId != 0)
			tagtext = "[leaguecrosstable=" + leagueId + "," + showtype + "]";
		else
			tinyMCEPopup.close();
	}

	if(window.tinyMCE) {
		window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
		//Peforms a clean up of the current editor HTML. 
		//tinyMCEPopup.editor.execCommand('mceCleanup');
		//Repaints the editor. Sometimes the browser has graphic glitches. 
		tinyMCEPopup.editor.execCommand('mceRepaint');
		tinyMCEPopup.close();
	}
	return;
}