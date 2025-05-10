<?php
require_once('language.php');
require_once('getRights.php');

/**
 * Retrieves the rights associated with a specific player.
 *
 * @param string $playerId The ID of the player.
 * @return array An associative array of rights where keys are the right names and values are true.
 */
function getUserRights($playerId) {
	$res = [];
	if (!$playerId)
		return $res;

	$getRights = mysql_query(<<<SQL
		SELECT privilege FROM `mkrights` WHERE player="$playerId"
	SQL);

	while ($getRight = mysql_fetch_array($getRights)) {
		$res[$getRight['privilege']] = true;
	}

	if (isset($res['admin'])) {
		$res['moderator'] = true;
		$res['organizer'] = true;
	}

	if (isset($res['moderator']) || isset($res['organizer'])) {
		$res['manager'] = true;
	}

	return $res;
}

/** @var array|null $hasRight Cached rights for the current user. */
$hasRight = null;

/**
 * Checks whether the current user has a specific right.  
 * Check the `mkrights` table for the full list of rights.  
 * @param string $key The name of the right to check.
 * @return bool True if the user has the specified right, false otherwise.
 */
function hasRight($key) {
	global $hasRight, $id;

	if ($hasRight === null)
		$hasRight = getUserRights($id);

	return isset($hasRight[$key]);
}


/**
 * Check if the user has the required rank to access the page.
 * Exits the script if the user does not have the requested rank.
 * @param string $rank The required rank.
 * @return void Echoes an error message if the user does not have the requested rank.
 */
function requireRank($rank) {
	$rankf = $rank;
    switch ($rank) {
        case 'manager':
            $rankf = 'moderator';
            break;
        case 'organizer':
            $rankf = 'event host';
            break;
        case 'clvalidator':
            $rankf = 'challenge validator';
            break;
        case 'admin':
            $rankf = 'administrator';
            break;
        case 'publisher':
            $rankf = 'news publisher';
            break;
    }

	global $id;
	if (!$id) {
		echo _("You are not logged in!");
		exit;
	}
	if (!hasRight($rank)) {
		echo F_("You do not have the {rankName} rank!", rankName: $rankf);
		exit;
	}
}
?>
