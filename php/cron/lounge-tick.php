<?php
// Runs the lounge's clock when nobody is on the lounge page to run it: members who stopped
// responding are dropped, vote and draft deadlines fire, finished mogis are rated and abandoned
// ones voided, and Discord is kept up to date. Every lounge poll still ticks it too, for the
// players waiting on it; this covers the stretches when there are none.
require_once(__DIR__ .'/bootstrap.php');
require_once(__DIR__ .'/../includes/lounge/common.php');
exit(cron_job('lounge-tick', 'lounge_tick'));
