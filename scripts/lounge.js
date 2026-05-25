(function() {
	if (typeof mId !== 'number') return;

	var POLL_INTERVAL_TIERS = 5000;
	var POLL_INTERVAL_WAITING = 3000;

	var view = 'tiers';
	var currentQueue = null;
	var lastPlayerState = null;
	var pollTimer = null;
	var actionInFlight = false;

	function toLanguage(en, fr) {
		return language ? en : fr;
	}

	function $(id) {
		return document.getElementById(id);
	}

	function postJSON(endpoint, body, cb) {
		xhr(endpoint, body, function(res) {
			var data;
			try { data = JSON.parse(res); }
			catch (e) { return false; }
			cb(data);
			return true;
		});
	}

	function setupTabs() {
		var tabs = document.querySelectorAll('.lounge-tab');
		var panels = document.querySelectorAll('.lounge-tabpanel');
		for (var i = 0; i < tabs.length; i++) {
			tabs[i].addEventListener('click', onTabClick);
		}
		function onTabClick() {
			var target = this.getAttribute('data-tab');
			for (var i = 0; i < tabs.length; i++)
				tabs[i].classList.toggle('is-active', tabs[i].getAttribute('data-tab') === target);
			for (var j = 0; j < panels.length; j++)
				panels[j].classList.toggle('is-active', panels[j].getAttribute('data-panel') === target);
		}
	}

	function renderPlayerStrip(player) {
		var strip = $('lounge-playerstrip');
		if (!strip) return;
		strip.innerHTML = '';

		var mmrLabel = document.createElement('span');
		mmrLabel.className = 'lounge-stat';
		mmrLabel.innerHTML = '<span class="lounge-stat-label">MMR</span> <span class="lounge-stat-value">'+ player.mmr +'</span>';
		strip.appendChild(mmrLabel);

		var gamesLabel = document.createElement('span');
		gamesLabel.className = 'lounge-stat';
		gamesLabel.innerHTML = '<span class="lounge-stat-label">'+ toLanguage('Games','Parties') +'</span> <span class="lounge-stat-value">'+ player.games +'</span>';
		strip.appendChild(gamesLabel);

		if (player.strikes > 0) {
			var strikesLabel = document.createElement('span');
			strikesLabel.className = 'lounge-stat lounge-stat--warn';
			strikesLabel.innerHTML = '<span class="lounge-stat-label">Strikes</span> <span class="lounge-stat-value">'+ player.strikes +'</span>';
			strip.appendChild(strikesLabel);
		}

		if (player.banned_until) {
			var ban = document.createElement('span');
			ban.className = 'lounge-stat lounge-stat--ban';
			ban.textContent = toLanguage('Banned until ', 'Banni jusqu\'au ') + player.banned_until;
			strip.appendChild(ban);
		}
	}

	function tierLabel(tier) {
		return language ? tier.label_en : tier.label_fr;
	}

	function tierRangeLabel(tier) {
		if (tier.code === 'all') return toLanguage('Open to everyone', 'Ouvert à tous');
		if (tier.max_mmr === null) return 'MMR ' + tier.min_mmr + '+';
		return 'MMR ' + tier.min_mmr + '–' + tier.max_mmr;
	}

	function renderTiers(tiers) {
		var container = $('lounge-tiers');
		if (!container) return;
		container.innerHTML = '';

		for (var i = 0; i < tiers.length; i++) {
			var tier = tiers[i];
			var card = document.createElement('div');
			card.className = 'lounge-tier' + (tier.eligible ? '' : ' is-locked');

			var title = document.createElement('h3');
			title.className = 'lounge-tier-title';
			title.textContent = tierLabel(tier);
			card.appendChild(title);

			var range = document.createElement('p');
			range.className = 'lounge-tier-range';
			range.textContent = tierRangeLabel(tier);
			card.appendChild(range);

			var count = document.createElement('p');
			count.className = 'lounge-tier-count';
			count.textContent = tier.queue_count + ' / 8 ' + toLanguage('in queue', 'en file');
			card.appendChild(count);

			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'lounge-tier-join';
			btn.setAttribute('data-tier', tier.id);
			if (!tier.eligible) {
				btn.disabled = true;
				btn.textContent = toLanguage('Locked', 'Verrouillé');
			} else {
				btn.textContent = toLanguage('Join', 'Rejoindre');
				btn.addEventListener('click', onJoinClick);
			}
			card.appendChild(btn);

			container.appendChild(card);
		}
	}

	function onJoinClick() {
		if (actionInFlight) return;
		var tierId = this.getAttribute('data-tier');
		actionInFlight = true;
		this.disabled = true;
		postJSON('lounge/join.php', 'tier=' + encodeURIComponent(tierId), function(data) {
			actionInFlight = false;
			if (data.error) {
				alert(joinErrorMessage(data));
				return;
			}
			currentQueue = data.queue;
			switchView('waiting');
		});
	}

	function joinErrorMessage(data) {
		switch (data.error) {
			case 'not_eligible': return toLanguage('Your MMR is not in this tier\'s range.', 'Votre MMR n\'est pas dans la plage de ce tier.');
			case 'already_queued': return toLanguage('You are already in a queue.', 'Vous êtes déjà dans une file.');
			case 'banned': return toLanguage('You are banned from ranked until ', 'Vous êtes banni du classé jusqu\'au ') + (data.banned_until || '');
			case 'tier_not_found': return toLanguage('That tier no longer exists.', 'Ce tier n\'existe plus.');
			default: return toLanguage('Could not join queue.', 'Impossible de rejoindre la file.');
		}
	}

	function renderWaiting(queue) {
		var container = $('lounge-queueup');
		if (!container) return;
		container.innerHTML = '';

		var header = document.createElement('div');
		header.className = 'lounge-waiting-header';
		var label = language ? queue.tier_label_en : queue.tier_label_fr;
		header.innerHTML = '<h2>' + label + '</h2>'
			+ '<p class="lounge-waiting-count">' + queue.members.length + ' / ' + queue.ready_threshold + ' ' + toLanguage('players', 'joueurs') + '</p>';
		container.appendChild(header);

		var status = document.createElement('p');
		status.className = 'lounge-waiting-status';
		if (queue.status === 'open') {
			status.textContent = toLanguage(
				'Waiting for more players… you can still drop.',
				'En attente d\'autres joueurs… vous pouvez encore quitter.'
			);
		} else if (queue.status === 'locked') {
			status.textContent = toLanguage(
				'Queue locked. The game will start soon.',
				'File verrouillée. La partie va bientôt commencer.'
			);
		} else if (queue.status === 'voting') {
			status.textContent = toLanguage(
				'Voting on the game mode…',
				'Vote du mode de jeu en cours…'
			);
		}
		container.appendChild(status);

		var list = document.createElement('ol');
		list.className = 'lounge-member-list';
		for (var i = 0; i < queue.members.length; i++) {
			var m = queue.members[i];
			var li = document.createElement('li');
			li.className = 'lounge-member' + (m.id === mId ? ' is-self' : '');
			li.innerHTML = '<span class="lounge-member-name"></span> <span class="lounge-member-mmr">MMR ' + m.mmr + '</span>';
			li.querySelector('.lounge-member-name').textContent = m.name;
			list.appendChild(li);
		}
		container.appendChild(list);

		var actions = document.createElement('div');
		actions.className = 'lounge-waiting-actions';
		if (queue.status === 'open') {
			var dropBtn = document.createElement('button');
			dropBtn.type = 'button';
			dropBtn.className = 'lounge-drop';
			dropBtn.textContent = toLanguage('Drop', 'Quitter');
			dropBtn.addEventListener('click', onDropClick);
			actions.appendChild(dropBtn);
		} else {
			var note = document.createElement('p');
			note.className = 'lounge-waiting-note';
			note.textContent = toLanguage(
				'You can no longer drop. Leaving now will count as a strike.',
				'Vous ne pouvez plus quitter. Partir maintenant comptera comme un strike.'
			);
			actions.appendChild(note);
		}
		container.appendChild(actions);
	}

	function onDropClick() {
		if (actionInFlight) return;
		actionInFlight = true;
		this.disabled = true;
		postJSON('lounge/leave.php', '', function(data) {
			actionInFlight = false;
			if (data.error === 'queue_locked') {
				currentQueue = data.queue;
				renderWaiting(currentQueue);
				return;
			}
			currentQueue = null;
			switchView('tiers');
		});
	}

	function switchView(next) {
		view = next;
		var queueUp = $('lounge-queueup');
		var tiers = $('lounge-tiers');
		if (!queueUp || !tiers) return;
		if (view === 'tiers') {
			queueUp.style.display = 'none';
			tiers.style.display = '';
		} else {
			tiers.style.display = 'none';
			queueUp.style.display = '';
		}
		scheduleNextPoll(0);
	}

	function scheduleNextPoll(delay) {
		if (pollTimer) clearTimeout(pollTimer);
		if (delay <= 0) {
			pollOnce();
		} else {
			pollTimer = setTimeout(pollOnce, delay);
		}
	}

	function pollOnce() {
		if (view === 'tiers') {
			postJSON('lounge/tiers.php', '', function(data) {
				if (!data || data.error) {
					pollTimer = setTimeout(pollOnce, POLL_INTERVAL_TIERS);
					return;
				}
				lastPlayerState = data.player;
				renderPlayerStrip(data.player);
				renderTiers(data.tiers);
				pollTimer = setTimeout(pollOnce, POLL_INTERVAL_TIERS);
			});
		} else {
			postJSON('lounge/poll.php', '', function(data) {
				if (!data || data.error) {
					pollTimer = setTimeout(pollOnce, POLL_INTERVAL_WAITING);
					return;
				}
				if (data.player) renderPlayerStrip(data.player);
				if (!data.queue) {
					currentQueue = null;
					switchView('tiers');
					return;
				}
				currentQueue = data.queue;
				renderWaiting(currentQueue);
				pollTimer = setTimeout(pollOnce, POLL_INTERVAL_WAITING);
			});
		}
	}

	function init() {
		setupTabs();
		postJSON('lounge/poll.php', '', function(data) {
			if (data && data.player) renderPlayerStrip(data.player);
			if (data && data.queue) {
				currentQueue = data.queue;
				switchView('waiting');
			} else {
				switchView('tiers');
			}
		});
	}

	if (document.readyState === 'loading')
		document.addEventListener('DOMContentLoaded', init);
	else
		init();
})();
