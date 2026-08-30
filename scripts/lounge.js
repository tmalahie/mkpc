(function() {
	if (typeof mId !== 'number') return;

	var POLL_INTERVAL_TIERS = 5000;
	var POLL_INTERVAL_WAITING = 3000;

	var view = 'tiers';
	var currentQueue = null;
	var lastPlayerState = null;
	var pollTimer = null;
	var actionInFlight = false;
	// Kept out of the DOM so a poll-driven re-render does not wipe a pending choice.
	var powChoice = null;

	var ALERT_SOUND = 'musics/events/ctalert.mp3';
	var ALERT_STORAGE_KEY = 'lounge.alerts';
	var announcedStatus = null;
	var unloadGuarded = false;
	var announcedConfirm = false;

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
			if (target === 'leaderboard')
				loadLeaderboard();
		}
	}

	function renderPlayerStrip(player) {
		var strip = $('lounge-playerstrip');
		if (!strip) return;
		strip.innerHTML = '';

		if (player.rank) {
			var rankBadge = document.createElement('span');
			rankBadge.className = 'lounge-stat';
			rankBadge.innerHTML = '<span class="lounge-stat-label">'+ toLanguage('Rank','Rang') +'</span> <span class="lounge-stat-value"></span>';
			var rankValue = rankBadge.querySelector('.lounge-stat-value');
			rankValue.textContent = rankLabel(player.rank);
			rankValue.style.color = player.rank.color;
			strip.appendChild(rankBadge);
		}

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

	function rankLabel(rank) {
		if (!rank) return '';
		return language ? rank.label_en : rank.label_fr;
	}

	function loadLeaderboard() {
		var container = $('lounge-leaderboard');
		if (!container) return;
		postJSON('lounge/leaderboard.php', '', function(data) {
			if (!data || data.error || !data.players) return;
			container.innerHTML = '';
			if (!data.players.length) {
				var empty = document.createElement('p');
				empty.className = 'lounge-empty';
				empty.textContent = toLanguage(
					'No mogi has been played yet this season.',
					'Aucun mogi n\'a encore été joué cette saison.'
				);
				container.appendChild(empty);
				return;
			}

			var table = document.createElement('table');
			table.className = 'lounge-leaderboard-table';
			var head = document.createElement('tr');
			head.innerHTML = '<th></th><th></th><th></th><th></th><th></th><th></th>';
			var cells = head.querySelectorAll('th');
			cells[0].textContent = toLanguage('Place', 'Place');
			cells[1].textContent = toLanguage('Player', 'Joueur');
			cells[2].textContent = toLanguage('Rank', 'Rang');
			cells[3].textContent = 'MMR';
			cells[4].textContent = toLanguage('Mogis', 'Mogis');
			cells[5].textContent = toLanguage('Avg. score', 'Score moyen');
			table.appendChild(head);

			for (var i = 0; i < data.players.length; i++) {
				var p = data.players[i];
				var row = document.createElement('tr');
				row.className = 'lounge-leaderboard-row' + (p.id === data.me ? ' is-self' : '');
				row.innerHTML = '<td class="lounge-lb-place"></td><td class="lounge-lb-name"></td>'
					+ '<td class="lounge-lb-rank"></td><td class="lounge-lb-mmr"></td>'
					+ '<td class="lounge-lb-games"></td><td class="lounge-lb-avg"></td>';
				row.querySelector('.lounge-lb-place').textContent = p.place;
				row.querySelector('.lounge-lb-name').textContent = p.name;
				var rankCell = row.querySelector('.lounge-lb-rank');
				rankCell.textContent = rankLabel(p.rank);
				if (p.rank) rankCell.style.color = p.rank.color;
				row.querySelector('.lounge-lb-mmr').textContent = p.mmr;
				row.querySelector('.lounge-lb-games').textContent = p.games + ' (' + p.wins + 'W)';
				row.querySelector('.lounge-lb-avg').textContent = (p.avg_score === null) ? '–' : p.avg_score;
				table.appendChild(row);
			}
			container.appendChild(table);
		});
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
			count.textContent = tier.queue_count + ' / 8 ' + toLanguage('in queue', 'en file')
				+ ' · ' + toLanguage(
					tier.min_players + ' needed to start',
					tier.min_players + ' requis pour lancer'
				);
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
		var body = 'tier=' + encodeURIComponent(tierId);
		if (mPerso)
			body += '&perso=' + encodeURIComponent(mPerso);
		requestAlertPermission();
		postJSON('lounge/join.php', body, function(data) {
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
			case 'site_banned': return toLanguage('Your account is banned.', 'Votre compte est banni.');
			case 'account_too_new': return toLanguage('Your account is too new for ranked.', 'Votre compte est trop récent pour le classé.');
			default: return toLanguage('Could not join queue.', 'Impossible de rejoindre la file.');
		}
	}

	// Closing the tab mid-queue leaves a ghost in the lineup, so warn on the way out. The
	// handler goes on the game page too: this runs in an overlay iframe, and Chrome only
	// raises the dialog for a frame the player has actually interacted with.
	function unloadGuardTargets() {
		var targets = [window];
		try {
			if (window.top !== window && window.top.document) targets.push(window.top);
		}
		catch (e) {}
		return targets;
	}

	function onBeforeUnload(e) {
		var message = toLanguage(
			'You are queued for a ranked mogi. Leaving now will drop you from the lineup.',
			'Vous êtes en file pour un mogi classé. Partir maintenant vous retirera de la partie.'
		);
		e.preventDefault();
		e.returnValue = message;
		return message;
	}

	function setUnloadGuard(on) {
		if (on === unloadGuarded) return;
		unloadGuarded = on;
		var targets = unloadGuardTargets();
		for (var i = 0; i < targets.length; i++) {
			if (on) targets[i].addEventListener('beforeunload', onBeforeUnload);
			else targets[i].removeEventListener('beforeunload', onBeforeUnload);
		}
	}

	// the overlay can be closed without unloading the game page, which would strand the
	// handler we put on it
	window.addEventListener('pagehide', function() { setUnloadGuard(false); });

	function alertsEnabled() {
		try { return localStorage.getItem(ALERT_STORAGE_KEY) !== '0'; }
		catch (e) { return true; }
	}

	function setAlertsEnabled(on) {
		try { localStorage.setItem(ALERT_STORAGE_KEY, on ? '1' : '0'); } catch (e) {}
	}

	function alertVolume() {
		try {
			var settings = JSON.parse(localStorage.getItem('settings.vol'));
			if (settings && settings.sfx != null) return settings.sfx;
		} catch (e) {}
		return 1;
	}

	var STATUS_ALERTS = {
		locked: {
			title: ['Lineup complete', 'File complète'],
			body: ['Everyone is here — the mode vote is about to open.', 'Tout le monde est là — le vote du mode va s\'ouvrir.']
		},
		voting: {
			title: ['Time to vote!', 'À vous de voter !'],
			body: ['Pick the game mode before the timer runs out.', 'Choisissez le mode de jeu avant la fin du chrono.']
		},
		confirm: {
			title: ['Still there?', 'Toujours là ?'],
			body: ['Confirm you are still queuing, or you will be taken out of the list.', 'Confirmez que vous êtes toujours en file, sinon vous en serez retiré.']
		},
		launched: {
			title: ['The race is starting!', 'La course commence !'],
			body: ['Your mogi is launching — get back to the game.', 'Votre mogi se lance — revenez sur le jeu.']
		}
	};

	function announceStatus(status) {
		if (announcedStatus === status) return;
		var wasKnown = (announcedStatus !== null);
		announcedStatus = status;
		if (!wasKnown || !STATUS_ALERTS[status] || !alertsEnabled()) return;
		fireAlert(status);
	}

	function fireAlert(key) {
		var alertData = STATUS_ALERTS[key];
		if (!alertData) return;
		mkNotify.fire({
			title: 'CT Lounge — ' + toLanguage(alertData.title[0], alertData.title[1]),
			body: toLanguage(alertData.body[0], alertData.body[1]),
			flash: '\u25B6 ' + toLanguage(alertData.title[0], alertData.title[1]),
			tag: 'lounge-queue',
			sound: ALERT_SOUND,
			volume: alertVolume()
		});
	}

	// "tout les 10-15 min on reçoit un message d'alerte demandant si on est encore dans la
	// queue": polling alone cannot tell a player apart from a tab they walked away from.
	function renderConfirmPrompt(queue) {
		var box = document.createElement('div');
		box.className = 'lounge-confirm';
		var text = document.createElement('p');
		text.className = 'lounge-confirm-text';
		var left = queue.confirm_seconds_left;
		text.textContent = toLanguage(
			'Are you still in the queue? You will be removed in ' + formatCountdown(left) + '.',
			'Êtes-vous toujours en file ? Vous en serez retiré dans ' + formatCountdown(left) + '.'
		);
		box.appendChild(text);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'lounge-confirm-btn';
		btn.textContent = toLanguage('I am still here', 'Je suis toujours là');
		btn.addEventListener('click', onConfirmClick);
		box.appendChild(btn);
		return box;
	}

	function onConfirmClick() {
		if (actionInFlight) return;
		actionInFlight = true;
		this.disabled = true;
		postJSON('lounge/confirm.php', '', function(data) {
			actionInFlight = false;
			announcedConfirm = false;
			mkNotify.clear();
			if (data && data.queue) {
				currentQueue = data.queue;
				renderWaiting(currentQueue);
			}
		});
	}

	function announceConfirm(queue) {
		if (!queue.confirm_due) {
			announcedConfirm = false;
			return;
		}
		if (announcedConfirm || !alertsEnabled()) return;
		announcedConfirm = true;
		fireAlert('confirm');
	}

	function renderAlertToggle() {
		var row = document.createElement('div');
		row.className = 'lounge-alerts';
		var toggle = document.createElement('button');
		var on = alertsEnabled();
		toggle.type = 'button';
		toggle.className = 'lounge-alerts-toggle' + (on ? ' is-on' : '');
		toggle.setAttribute('aria-pressed', on ? 'true' : 'false');
		toggle.title = toLanguage(
			'Plays a sound, shows a notification and flashes the tab title when the queue moves on.',
			'Joue un son, affiche une notification et fait clignoter le titre de l\'onglet quand la file avance.'
		);
		toggle.innerHTML = '<span class="lounge-alerts-icon" aria-hidden="true"></span>'
			+ '<span class="lounge-alerts-label"></span>'
			+ '<span class="lounge-alerts-state"></span>'
			+ '<span class="lounge-alerts-switch" aria-hidden="true"></span>';
		toggle.querySelector('.lounge-alerts-label').textContent = toLanguage('Match alerts', 'Alertes de partie');
		toggle.querySelector('.lounge-alerts-state').textContent = alertStateLabel(on);
		toggle.addEventListener('click', onAlertToggle);
		row.appendChild(toggle);

		if (on && mkNotify.permission() === 'denied') {
			var warn = document.createElement('span');
			warn.className = 'lounge-alerts-hint';
			warn.textContent = toLanguage(
				'Notifications are blocked for this site — only the sound and the tab title will alert you.',
				'Les notifications sont bloquées pour ce site — seuls le son et le titre de l\'onglet vous alerteront.'
			);
			row.appendChild(warn);
		}
		return row;
	}

	function alertStateLabel(on) {
		if (on) return toLanguage('On', 'Activées');
		return toLanguage('Off', 'Désactivées');
	}

	function onAlertToggle() {
		var on = !alertsEnabled();
		setAlertsEnabled(on);
		// synchronously, while the click still counts as the user gesture a prompt needs
		if (on) requestAlertPermission();
		else mkNotify.clear();
		// rebuilt rather than patched, so the "blocked" hint follows the new state too
		var row = this.parentNode;
		if (row && row.parentNode)
			row.parentNode.replaceChild(renderAlertToggle(), row);
	}

	function requestAlertPermission() {
		if (!alertsEnabled()) return;
		mkNotify.request(function() {
			if (currentQueue) renderWaiting(currentQueue);
		});
	}

	function renderWaiting(queue) {
		var container = $('lounge-queueup');
		if (!container) return;

		announceStatus(queue.status);
		setUnloadGuard(queue.status !== 'launched');

		if (queue.status === 'launched' && queue.privgame_key) {
			renderLaunching(container, queue);
			return;
		}

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
			var lockLeft = queue.lock_seconds_left;
			if (lockLeft !== null) {
				status.textContent = toLanguage(
					'Queue locked. Voting starts in ' + formatCountdown(lockLeft) + '.',
					'File verrouillée. Le vote commence dans ' + formatCountdown(lockLeft) + '.'
				);
			} else {
				status.textContent = toLanguage(
					'Queue locked. Voting starts soon.',
					'File verrouillée. Le vote commence bientôt.'
				);
			}
		} else if (queue.status === 'voting') {
			var voteLeft = queue.vote_seconds_left;
			status.textContent = toLanguage(
				'Vote the game mode (' + formatCountdown(voteLeft) + ' left)',
				'Votez pour le mode de jeu (' + formatCountdown(voteLeft) + ' restant)'
			);
		}
		container.appendChild(status);
		announceConfirm(queue);
		if (queue.confirm_due)
			container.appendChild(renderConfirmPrompt(queue));
		container.appendChild(renderAlertToggle());

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

		if (queue.status === 'voting') {
			container.appendChild(renderVoteSection(queue));
		} else {
			container.appendChild(renderQueueActions(queue));
		}
	}

	function renderQueueActions(queue) {
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
		return actions;
	}

	function renderVoteSection(queue) {
		var section = document.createElement('div');
		section.className = 'lounge-vote';
		var hint = document.createElement('p');
		hint.className = 'lounge-vote-hint';
		hint.textContent = toLanguage(
			'If the timer runs out, the majority of the votes cast decides.',
			'Si le chrono expire, la majorité des votes exprimés décide.'
		);
		section.appendChild(hint);
		section.appendChild(renderModeVote(queue));
		section.appendChild(renderPowVote(queue));
		return section;
	}

	function voteGroup(titleEn, titleFr) {
		var group = document.createElement('section');
		group.className = 'lounge-vote-group';
		var title = document.createElement('h3');
		title.className = 'lounge-vote-group-title';
		title.textContent = toLanguage(titleEn, titleFr);
		group.appendChild(title);
		return group;
	}

	function renderModeVote(queue) {
		var group = voteGroup('Game mode', 'Mode de jeu');
		var btns = document.createElement('div');
		btns.className = 'lounge-vote-buttons';
		for (var i = 0; i < queue.allowed_modes.length; i++) {
			var mode = queue.allowed_modes[i];
			var voteCount = queue.votes && queue.votes[mode] ? queue.votes[mode] : 0;
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'lounge-vote-btn'
				+ (queue.my_vote === mode ? ' is-selected' : '');
			btn.setAttribute('data-mode', mode);
			btn.innerHTML = '<span class="lounge-vote-label"></span>'
				+ '<span class="lounge-vote-count"></span>';
			btn.querySelector('.lounge-vote-label').textContent = mode;
			btn.querySelector('.lounge-vote-count').textContent = voteCount + ' '
				+ (voteCount === 1 ? toLanguage('vote', 'vote') : toLanguage('votes', 'votes'));
			btn.addEventListener('click', onVoteClick);
			btns.appendChild(btn);
		}
		group.appendChild(btns);
		return group;
	}

	// Rule 3h: the POW Block only goes in when the whole lineup agreed, so this is an
	// opt-in that has to be set before the mode vote is accepted.
	function renderPowVote(queue) {
		var group = voteGroup('Items', 'Objets');
		if (powChoice === null && queue.my_pow_vote !== null && queue.my_pow_vote !== undefined)
			powChoice = queue.my_pow_vote;

		var total = queue.members ? queue.members.length : 0;
		var agreed = queue.pow_votes || 0;
		var unanimous = (total > 0 && agreed === total);

		var toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'lounge-pow-toggle' + (powChoice ? ' is-selected' : '');
		toggle.setAttribute('aria-pressed', powChoice ? 'true' : 'false');
		toggle.innerHTML = '<span class="lounge-pow-name"></span>'
			+ '<span class="lounge-pow-tally"></span>'
			+ '<span class="lounge-pow-switch" aria-hidden="true"></span>';
		toggle.querySelector('.lounge-pow-name').textContent = toLanguage('POW Block', 'POW Block');
		toggle.querySelector('.lounge-pow-tally').textContent = agreed + ' / ' + total + ' '
			+ toLanguage('agreed', 'd\'accord');
		toggle.addEventListener('click', onPowToggle);
		group.appendChild(toggle);

		var note = document.createElement('p');
		note.className = 'lounge-pow-note' + (unanimous ? ' is-unanimous' : '');
		note.textContent = unanimous
			? toLanguage(
				'Everyone agreed — the POW Block will be in the item distribution.',
				'Tout le monde est d\'accord — le POW Block sera dans la distribution d\'objets.'
			)
			: toLanguage(
				'The POW Block is only added if every player agrees.',
				'Le POW Block n\'est ajouté que si tous les joueurs sont d\'accord.'
			);
		group.appendChild(note);
		return group;
	}

	function currentPowChoice() {
		return powChoice ? 1 : 0;
	}

	function onPowToggle() {
		if (actionInFlight) return;
		powChoice = powChoice ? 0 : 1;
		this.classList.toggle('is-selected', !!powChoice);
		this.setAttribute('aria-pressed', powChoice ? 'true' : 'false');
		// only meaningful once a mode has been picked; otherwise it rides along with it
		if (!currentQueue || !currentQueue.my_vote) return;
		sendVote(currentQueue.my_vote);
	}

	function renderLaunching(container, queue) {
		container.innerHTML = '';
		var box = document.createElement('div');
		box.className = 'lounge-launching';
		box.innerHTML = '<h2></h2><p></p>';
		box.querySelector('h2').textContent = toLanguage('Match found!', 'Partie trouvée !');
		box.querySelector('p').textContent = toLanguage(
			'Launching the game…',
			'Lancement de la partie…'
		);
		container.appendChild(box);

		var url = 'online.php?mid=' + queue.multicup_id + '&ranked&key=' + queue.privgame_key;
		setTimeout(function() {
			if (window.parent && window.parent !== window) {
				window.parent.location.href = url;
			} else {
				window.location.href = url;
			}
		}, 1200);
	}

	function formatCountdown(seconds) {
		if (seconds === null || seconds === undefined) return '–';
		if (seconds < 60) return seconds + 's';
		var m = Math.floor(seconds / 60);
		var s = seconds % 60;
		return m + ':' + (s < 10 ? '0' : '') + s;
	}

	function onVoteClick() {
		sendVote(this.getAttribute('data-mode'));
	}

	function sendVote(mode) {
		if (actionInFlight) return;
		actionInFlight = true;
		var buttons = document.querySelectorAll('.lounge-vote-btn');
		for (var i = 0; i < buttons.length; i++) buttons[i].disabled = true;
		var body = 'mode=' + encodeURIComponent(mode) + '&pow=' + currentPowChoice();
		postJSON('lounge/vote.php', body, function(data) {
			actionInFlight = false;
			if (data && data.queue) {
				currentQueue = data.queue;
				renderWaiting(currentQueue);
			}
		});
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
			leaveQueueState();
			switchView('tiers');
		});
	}

	function leaveQueueState() {
		currentQueue = null;
		powChoice = null;
		announcedStatus = null;
		announcedConfirm = false;
		setUnloadGuard(false);
		mkNotify.clear();
	}

	function renderResults(match) {
		var container = $('lounge-results');
		if (!container) return;
		container.innerHTML = '';

		var header = document.createElement('div');
		header.className = 'lounge-results-header';
		var label = language ? match.tier_label_en : match.tier_label_fr;
		header.innerHTML = '<h2></h2><p class="lounge-results-sub"></p>';
		header.querySelector('h2').textContent = toLanguage('Mogi results', 'Résultats du mogi');
		header.querySelector('.lounge-results-sub').textContent =
			label + ' — ' + match.mode + ' — ' + match.races + ' ' + toLanguage('races', 'courses');
		container.appendChild(header);

		var table = document.createElement('table');
		table.className = 'lounge-results-table';
		var head = document.createElement('tr');
		head.innerHTML = '<th></th><th></th><th></th><th></th>';
		var headCells = head.querySelectorAll('th');
		headCells[0].textContent = toLanguage('Place', 'Place');
		headCells[1].textContent = toLanguage('Player', 'Joueur');
		headCells[2].textContent = toLanguage('Score', 'Score');
		headCells[3].textContent = 'MMR';
		table.appendChild(head);

		for (var i = 0; i < match.players.length; i++) {
			var p = match.players[i];
			var row = document.createElement('tr');
			row.className = 'lounge-results-row' + (p.id === mId ? ' is-self' : '');
			row.innerHTML = '<td class="lounge-results-place"></td><td class="lounge-results-name"></td>'
				+ '<td class="lounge-results-score"></td><td class="lounge-results-mmr"></td>';
			row.querySelector('.lounge-results-place').textContent = (p.position === null) ? '–' : p.position;
			row.querySelector('.lounge-results-name').textContent = p.name;
			row.querySelector('.lounge-results-score').textContent = (p.score === null) ? '–' : p.score;
			row.querySelector('.lounge-results-mmr').textContent = formatMmrChange(p);
			table.appendChild(row);
		}
		container.appendChild(table);

		var back = document.createElement('button');
		back.type = 'button';
		back.className = 'lounge-results-back';
		back.textContent = toLanguage('Back to the lounge', 'Retour au lounge');
		back.addEventListener('click', function() {
			// re-enter through the ranked flow so the character gets picked again
			(window.top || window).location.href = 'ranked.php';
		});
		container.appendChild(back);
	}

	function formatMmrChange(player) {
		if (player.mmr_delta === null || player.mmr_after === null)
			return toLanguage('pending', 'en attente');
		return player.mmr_after + ' (' + (player.mmr_delta >= 0 ? '+' : '') + player.mmr_delta + ')';
	}

	function switchView(next) {
		view = next;
		var queueUp = $('lounge-queueup');
		var tiers = $('lounge-tiers');
		var results = $('lounge-results');
		if (!queueUp || !tiers) return;
		if (view === 'results') {
			queueUp.style.display = 'none';
			tiers.style.display = 'none';
			if (results) results.style.display = '';
			return;
		}
		if (results) results.style.display = 'none';
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
					leaveQueueState();
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
		if (mResultKey) {
			postJSON('lounge/result.php', 'key=' + encodeURIComponent(mResultKey), function(data) {
				if (data && data.player) renderPlayerStrip(data.player);
				if (data && data.match) {
					renderResults(data.match);
					switchView('results');
				} else {
					mResultKey = null;
					initQueueView();
				}
			});
			return;
		}
		initQueueView();
	}

	function initQueueView() {
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
