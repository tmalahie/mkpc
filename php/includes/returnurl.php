<?php
// Pages reached mid-flow (registering while trying to start a game, for instance) can be
// entered as `inscription.php?online.php?mid=15355&ranked` so the visitor is sent back
// exactly where they came from. The target is read from the raw query string rather than
// $_GET, because it is a whole URL rather than a set of parameters.
//
// Only a bare page name on this site is accepted: a scheme, a host, or any kind of path
// separator fails the pattern, so this cannot be turned into an open redirect.
// Returns null when there is no usable target, leaving the caller's own default in charge.
function getReturnUrl() {
	if (empty($_SERVER['QUERY_STRING']))
		return null;
	$target = rawurldecode($_SERVER['QUERY_STRING']);
	if (!preg_match('#^[A-Za-z0-9_\-]+\.php(\?[A-Za-z0-9_\-=&.]*)?$#', $target))
		return null;
	return $target;
}
