<?php
/**
Template page for a single match

The following variables are usable:
	
	$match: contains data of displayed match
	$league: contains data of current league
	
	You can check the content of a variable when you insert the tag <?php var_dump($variable) ?>
*/
?>
<?php if ( $match ) : ?>
<?php print_r($match) ?>
<?php endif; ?>