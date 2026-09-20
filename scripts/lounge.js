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

	var ALERT_SOUND = 'musics/events/ctalert.mp3';
	var ALERT_STORAGE_KEY = 'lounge.alerts';
	var announcedStatus = null;
	var unloadGuarded = false;
	var announcedConfirm = false;
	// Set once this client has seen the recap - what the lineup settled on, before the room
	// opens. Someone who opens the lounge to find their mogi already running has not, and is
	// most likely late for it, so they get told it is on and sent straight there instead.
	var sawRecap = false;
	var announcedStart = false;
	var leavingForRoom = false;

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
		// Joining needs a character, and the only place to pick one is the game itself. So on
		// the standalone page - reached from the home page's leaderboard link - Queue Up is the
		// way back into online.php rather than a tier list nobody could join from. Only when it
		// would show that tier list: a player already queued, or reading their results, opened
		// this page to see exactly that, and sending them to the game would drop them out of it.
		var inGame = (window.top !== window.self);
		for (var i = 0; i < tabs.length; i++) {
			if (tabs[i].tagName.toLowerCase() === 'a') continue;
			tabs[i].addEventListener('click', onTabClick);
		}
		function onTabClick() {
			var target = this.getAttribute('data-tab');
			if ((target === 'queueup') && !inGame && (view === 'tiers')) {
				location.href = 'ranked.php';
				return;
			}
			activateLoungeTab(target);
			if (target === 'leaderboard')
				loadLeaderboard();
		}

		var requested = (location.search.match(/[?&]tab=([a-z]+)/) || [])[1];
		if (requested) {
			for (var k = 0; k < tabs.length; k++) {
				if (tabs[k].getAttribute('data-tab') === requested) {
					onTabClick.call(tabs[k]);
					break;
				}
			}
		}
	}

	function activateLoungeTab(target) {
		var tabs = document.querySelectorAll('.lounge-tab');
		var panels = document.querySelectorAll('.lounge-tabpanel');
		for (var i = 0; i < tabs.length; i++)
			tabs[i].classList.toggle('is-active', tabs[i].getAttribute('data-tab') === target);
		for (var j = 0; j < panels.length; j++)
			panels[j].classList.toggle('is-active', panels[j].getAttribute('data-panel') === target);
	}

	function renderPlayerStrip(player) {
		var strip = $('lounge-playerstrip');
		if (!strip) return;
		strip.innerHTML = '';

		if (player.rank) {
			var rankBadge = document.createElement('span');
			rankBadge.className = 'lounge-stat';
			rankBadge.innerHTML = '<span class="lounge-stat-label">'+ toLanguage('Rank','Rang') +'</span>';
			rankBadge.appendChild(rankChip(player.rank));
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
		return rank.label;
	}

	// The ladder's own rank colours run from near-black (Master) to near-white (Silver), so
	// none of them can be ink on the page: the colour is the chip, and the label on it is
	// black or white depending on how dark the chip is.
	function rankChip(rank) {
		var chip = document.createElement('span');
		chip.className = 'lounge-rank';
		chip.textContent = rankLabel(rank);
		if (!rank || !rank.color) return chip;
		chip.style.backgroundColor = rank.color;
		var hex = rank.color.replace('#', '');
		if (hex.length === 3)
			hex = hex.charAt(0) + hex.charAt(0) + hex.charAt(1) + hex.charAt(1) + hex.charAt(2) + hex.charAt(2);
		var rgb = parseInt(hex, 16);
		var luma = 0.299 * ((rgb >> 16) & 255) + 0.587 * ((rgb >> 8) & 255) + 0.114 * (rgb & 255);
		chip.style.color = (luma > 150) ? '#000' : '#fff';
		return chip;
	}

	var LB_TOP_PLAYERS = 20;
	var LB_RECENT_MATCHES = 10;
	var LB_ALL_PLAYERS = 500;
	var LB_ALL_MATCHES = 100;

	// The game's own team colours, so a table read after the mogi names the same sides the
	// player just raced against. mk.js's light variants: the primaries are ink on a dark page.
	var LB_TEAM_COLORS = ['#69f', '#f96', '#9f6', '#ff7', '#fa4', '#f8f'];

	function lbContainer() {
		return $('lounge-leaderboard');
	}

	function lbBar(en, fr) {
		var bar = $('lounge-lb-bar');
		if (bar) bar.textContent = toLanguage(en, fr);
	}

	function lbLoading() {
		var container = lbContainer();
		if (!container) return null;
		container.innerHTML = '';
		var loading = document.createElement('span');
		loading.className = 'lounge-loading';
		loading.textContent = toLanguage('Loading...', 'Chargement...');
		container.appendChild(loading);
		return container;
	}

	function lbEmpty(container, en, fr) {
		var empty = document.createElement('p');
		empty.className = 'lounge-empty';
		empty.textContent = toLanguage(en, fr);
		container.appendChild(empty);
	}

	function lbCell(tag, className, content) {
		var el = document.createElement(tag);
		if (className) el.className = className;
		if (content === null || content === undefined) el.textContent = '–';
		else if (typeof content === 'object') el.appendChild(content);
		else el.textContent = content;
		return el;
	}

	function lbRow(className, cells) {
		var tr = document.createElement('tr');
		if (className) tr.className = className;
		for (var i = 0; i < cells.length; i++)
			tr.appendChild(cells[i]);
		return tr;
	}

	function lbTable(className, headings) {
		var table = document.createElement('table');
		table.className = className;
		if (!headings) return table;
		var head = document.createElement('tr');
		for (var i = 0; i < headings.length; i++)
			head.appendChild(lbCell('th', headings[i].className, headings[i].label));
		table.appendChild(head);
		return table;
	}

	function lbButton(className, label, onclick) {
		var button = document.createElement('button');
		button.type = 'button';
		button.className = className;
		button.textContent = label;
		button.addEventListener('click', onclick);
		return button;
	}

	function lbCard(titleEn, titleFr, viewAll) {
		var card = document.createElement('div');
		card.className = 'lounge-lb-card';
		var head = document.createElement('div');
		head.className = 'lounge-lb-cardhead';
		var title = document.createElement('h3');
		title.textContent = toLanguage(titleEn, titleFr);
		head.appendChild(title);
		if (viewAll)
			head.appendChild(lbButton('lounge-lb-viewall', toLanguage('View all', 'Tout voir'), viewAll));
		card.appendChild(head);
		return card;
	}

	function lbBack(en, fr, onclick) {
		var back = lbButton('lounge-lb-back', '‹ ' + toLanguage(en, fr), onclick);
		return back;
	}

	// Coarse on purpose: these read "when was this", not "how long exactly". Seconds come from
	// the server, so a clock that disagrees with it cannot turn a fresh mogi into a future one.
	function timeAgo(seconds) {
		if ((seconds === null) || (seconds === undefined)) return '';
		if (seconds < 60) return toLanguage('now', 'à l\'instant');
		var minutes = Math.floor(seconds / 60);
		if (minutes < 60) return minutes + toLanguage('min', 'min');
		var hours = Math.floor(minutes / 60);
		if (hours < 24) return hours + toLanguage('h', 'h');
		var days = Math.floor(hours / 24);
		if (days < 31) return days + toLanguage('d', 'j');
		var months = Math.floor(days / 30);
		if (months < 12) return months + toLanguage('mo', 'mois');
		return Math.floor(days / 365) + toLanguage('y', 'an');
	}

	function ratio(part, whole) {
		if (!whole) return null;
		return (Math.round(1000 * part / whole) / 10) + '%';
	}

	function signed(value) {
		if ((value === null) || (value === undefined)) return null;
		return (value > 0 ? '+' : '') + value;
	}

	// Green up, red down: the one piece of Lorenzi's colouring the staff asked for by name.
	function deltaEl(value) {
		var span = document.createElement('span');
		if ((value === null) || (value === undefined)) {
			span.className = 'lounge-lb-pending';
			span.textContent = toLanguage('pending', 'en attente');
			return span;
		}
		span.className = 'lounge-delta ' + ((value > 0) ? 'is-up' : ((value < 0) ? 'is-down' : 'is-flat'));
		span.textContent = signed(value);
		return span;
	}

	function arrowEl(value) {
		var span = document.createElement('span');
		span.className = 'lounge-arrow ' + ((value > 0) ? 'is-up' : ((value < 0) ? 'is-down' : 'is-flat'));
		span.textContent = (value > 0) ? '▲' : ((value < 0) ? '▼' : '—');
		return span;
	}

	function placeEl(place) {
		return (place === null) ? '–' : ('#' + place);
	}

	function playerLink(player) {
		var link = document.createElement('a');
		link.className = 'lounge-lb-playerlink';
		link.href = '#';
		link.textContent = player.name;
		link.addEventListener('click', function(e) {
			e.preventDefault();
			// A name clicked in a mogi's own table is on the queue panel, not this one.
			activateLoungeTab('leaderboard');
			showLoungePlayer(player.id);
		});
		return link;
	}

	function teamSwatch(team) {
		var swatch = document.createElement('span');
		swatch.className = 'lounge-teamswatch';
		swatch.style.backgroundColor = LB_TEAM_COLORS[team % LB_TEAM_COLORS.length];
		return swatch;
	}

	// A side is named by who was in it, the way the staff reads a results post. Past a pair the
	// names stop fitting on a line, so the rest become a count.
	function teamName(names) {
		if (!names || !names.length) return '';
		if (names.length <= 2) return names.join(' + ');
		return names[0] + ' +' + (names.length - 1);
	}

	function matchScoreChips(match) {
		var wrap = document.createElement('span');
		wrap.className = 'lounge-matchchips';
		if (!match.teams.length) {
			var chip = document.createElement('span');
			chip.className = 'lounge-matchchip';
			chip.appendChild(lbCell('span', 'lounge-matchchip-mode', match.mode));
			chip.appendChild(lbCell('span', 'lounge-matchchip-score', match.total));
			wrap.appendChild(chip);
			return wrap;
		}
		for (var i = 0; i < match.teams.length; i++) {
			var team = match.teams[i];
			var teamChip = document.createElement('span');
			teamChip.className = 'lounge-matchchip is-team';
			teamChip.style.borderColor = LB_TEAM_COLORS[team.team % LB_TEAM_COLORS.length];
			teamChip.appendChild(lbCell('span', 'lounge-matchchip-mode', teamName(team.names)));
			teamChip.appendChild(lbCell('span', 'lounge-matchchip-score', team.score));
			wrap.appendChild(teamChip);
		}
		return wrap;
	}

	function matchListEl(matches, me, showMine) {
		var list = document.createElement('table');
		list.className = 'lounge-matchlist';
		for (var i = 0; i < matches.length; i++) {
			var match = matches[i];
			var cells = [
				lbCell('td', 'lounge-match-id', '#' + match.id),
				lbCell('td', 'lounge-match-chips', matchScoreChips(match))
			];
			if (showMine) {
				var mine = null;
				for (var j = 0; j < match.players.length; j++) {
					if (match.players[j].id === me) mine = match.players[j];
				}
				cells.push(lbCell('td', 'lounge-match-mine', mine ? deltaEl(mine.mmr_delta) : null));
				cells.push(lbCell('td', 'lounge-match-mmr', mine ? mine.mmr_after : null));
				cells.push(lbCell('td', 'lounge-match-place', mine ? placeEl(mine.place_after) : null));
			}
			cells.push(lbCell('td', 'lounge-match-ago', timeAgo(match.ended_ago)));
			var line = lbRow('lounge-match-row', cells);
			line.setAttribute('data-match', match.id);
			line.setAttribute('role', 'button');
			line.setAttribute('tabindex', '0');
			line.addEventListener('click', onMatchRowClick);
			line.addEventListener('keydown', onMatchRowKey);
			list.appendChild(line);
		}
		return list;
	}

	function onMatchRowClick() {
		showLoungeMatch(parseInt(this.getAttribute('data-match'), 10));
	}

	function onMatchRowKey(e) {
		if ((e.key !== 'Enter') && (e.key !== ' ')) return;
		e.preventDefault();
		onMatchRowClick.call(this);
	}

	function topPlayersTable(players, me) {
		var table = lbTable('lounge-leaderboard-table', [
			{ className: 'lounge-lb-place', label: toLanguage('Place', 'Place') },
			{ className: 'lounge-lb-name', label: toLanguage('Player', 'Joueur') },
			{ className: 'lounge-lb-mmr', label: 'MMR' },
			{ className: 'lounge-lb-rank', label: toLanguage('Rank', 'Rang') }
		]);
		for (var i = 0; i < players.length; i++) {
			var p = players[i];
			table.appendChild(lbRow(
				'lounge-leaderboard-row' + ((p.id === me) ? ' is-self' : ''),
				[
					lbCell('td', 'lounge-lb-place', p.place),
					lbCell('td', 'lounge-lb-name', playerLink(p)),
					lbCell('td', 'lounge-lb-mmr', p.mmr),
					lbCell('td', 'lounge-lb-rank', p.rank ? rankChip(p.rank) : null)
				]
			));
		}
		return table;
	}

	function showLoungeOverview() {
		var container = lbLoading();
		if (!container) return;
		lbBar('Season leaderboard', 'Classement de la saison');
		var split = document.createElement('div');
		split.className = 'lounge-lb-split';
		var playersCard = lbCard('Top players', 'Meilleurs joueurs', showLoungePlayers);
		var matchesCard = lbCard('Recent matches', 'Derniers mogis', showLoungeMatches);
		split.appendChild(playersCard);
		split.appendChild(matchesCard);
		container.innerHTML = '';
		container.appendChild(split);

		postJSON('lounge/leaderboard.php', 'limit=' + LB_TOP_PLAYERS, function(data) {
			if (!data || data.error || !data.players) return;
			if (!data.players.length) {
				lbEmpty(playersCard,
					'No mogi has been played yet this season.',
					'Aucun mogi n\'a encore été joué cette saison.');
				return;
			}
			playersCard.appendChild(topPlayersTable(data.players, data.me));
		});
		postJSON('lounge/matches.php', 'limit=' + LB_RECENT_MATCHES, function(data) {
			if (!data || data.error || !data.matches) return;
			if (!data.matches.length) {
				lbEmpty(matchesCard, 'No mogi yet.', 'Aucun mogi pour le moment.');
				return;
			}
			matchesCard.appendChild(matchListEl(data.matches, data.me, false));
		});
	}

	// Every column the staff kept off Lorenzi's table, in their order. "Avg Rating Gain" and
	// "Total Points" are the two they struck out: they are not computed here either.
	function statsColumns() {
		return [
			{ en: 'Ranking', fr: 'Rang', className: 'lounge-lb-place',
				value: function(p) { return placeEl(p.place); } },
			{ en: 'Name', fr: 'Nom', className: 'lounge-lb-name',
				value: function(p) { return playerLink(p); } },
			{ en: 'Rating', fr: 'MMR', className: 'lounge-lb-mmr is-strong',
				value: function(p) { return p.mmr; } },
			{ en: 'Tier', fr: 'Tier', className: 'lounge-lb-rank',
				value: function(p) { return p.rank ? rankChip(p.rank) : null; } },
			{ en: 'Matches Played', fr: 'Mogis joués', className: 'lounge-lb-num',
				value: function(p) { return p.games; } },
			{ en: 'Wins', fr: 'Victoires', className: 'lounge-lb-num is-up',
				value: function(p) { return p.wins; } },
			{ en: 'Losses', fr: 'Défaites', className: 'lounge-lb-num is-down',
				value: function(p) { return p.games - p.wins; } },
			{ en: 'Win Ratio', fr: 'Ratio', className: 'lounge-lb-num',
				value: function(p) { return ratio(p.wins, p.games); } },
			{ en: 'Best Ranking', fr: 'Meilleur rang', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? placeEl(p.stats.best_place) : null; } },
			{ en: 'Worst Ranking', fr: 'Pire rang', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? placeEl(p.stats.worst_place) : null; } },
			{ en: 'Max Rating', fr: 'MMR max', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? p.stats.max_mmr : p.peak_mmr; } },
			{ en: 'Min Rating', fr: 'MMR min', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? p.stats.min_mmr : null; } },
			{ en: 'Max Rating Gain', fr: 'Meilleur gain', className: 'lounge-lb-num is-up',
				value: function(p) { return p.stats ? signed(p.stats.max_gain) : null; } },
			{ en: 'Max Rating Loss', fr: 'Pire perte', className: 'lounge-lb-num is-down',
				value: function(p) { return p.stats ? signed(p.stats.max_loss) : null; } },
			{ en: 'Max Points Gain', fr: 'Meilleur score', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? p.stats.max_score : null; } },
			{ en: 'Avg Points Gain', fr: 'Score moyen', className: 'lounge-lb-num',
				value: function(p) { return p.avg_score; } },
			{ en: 'Last Played', fr: 'Dernier mogi', className: 'lounge-lb-num',
				value: function(p) { return p.stats ? timeAgo(p.stats.last_played_ago) : null; } }
		];
	}

	function showLoungePlayers() {
		var container = lbLoading();
		if (!container) return;
		lbBar('Season standings', 'Classement complet');
		postJSON('lounge/leaderboard.php', 'limit=' + LB_ALL_PLAYERS + '&full=1', function(data) {
			if (!data || data.error || !data.players) return;
			container.innerHTML = '';
			container.appendChild(lbBack('Back to the leaderboard', 'Retour au classement', showLoungeOverview));
			if (!data.players.length) {
				lbEmpty(container,
					'No mogi has been played yet this season.',
					'Aucun mogi n\'a encore été joué cette saison.');
				return;
			}
			var columns = statsColumns();
			var headings = [];
			for (var c = 0; c < columns.length; c++)
				headings.push({ className: columns[c].className, label: toLanguage(columns[c].en, columns[c].fr) });
			var table = lbTable('lounge-leaderboard-table lounge-lb-stats', headings);
			for (var i = 0; i < data.players.length; i++) {
				var cells = [];
				for (var j = 0; j < columns.length; j++)
					cells.push(lbCell('td', columns[j].className, columns[j].value(data.players[i])));
				table.appendChild(lbRow(
					'lounge-leaderboard-row' + ((data.players[i].id === data.me) ? ' is-self' : ''),
					cells
				));
			}
			var scroller = document.createElement('div');
			scroller.className = 'lounge-lb-scroll';
			scroller.appendChild(table);
			container.appendChild(scroller);
		});
	}

	function showLoungeMatches() {
		var container = lbLoading();
		if (!container) return;
		lbBar('Recent matches', 'Derniers mogis');
		postJSON('lounge/matches.php', 'limit=' + LB_ALL_MATCHES, function(data) {
			if (!data || data.error || !data.matches) return;
			container.innerHTML = '';
			container.appendChild(lbBack('Back to the leaderboard', 'Retour au classement', showLoungeOverview));
			if (!data.matches.length) {
				lbEmpty(container, 'No mogi yet.', 'Aucun mogi pour le moment.');
				return;
			}
			container.appendChild(matchListEl(data.matches, data.me, false));
		});
	}

	function statLine(labelEn, labelFr, value) {
		var line = document.createElement('div');
		line.className = 'lounge-profile-stat';
		line.appendChild(lbCell('span', 'lounge-profile-statlabel', toLanguage(labelEn, labelFr)));
		line.appendChild(lbCell('span', 'lounge-profile-statvalue', value));
		return line;
	}

	// The rating history, drawn over the rank bands it crossed - which is what makes a climb
	// read as a climb rather than as a line going up. Ranks come down highest-first.
	function historyChart(history, ranks) {
		var width = 600, height = 150;
		var low = history[0], high = history[0];
		for (var i = 1; i < history.length; i++) {
			if (history[i] < low) low = history[i];
			if (history[i] > high) high = history[i];
		}
		var pad = Math.max(40, Math.round((high - low) * 0.15));
		low -= pad;
		high += pad;
		var span = (high - low) || 1;
		function y(value) {
			return Math.round(1000 * (height * (high - value) / span)) / 1000;
		}

		var svgNS = 'http://www.w3.org/2000/svg';
		var svg = document.createElementNS(svgNS, 'svg');
		svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);
		svg.setAttribute('preserveAspectRatio', 'none');
		svg.setAttribute('class', 'lounge-profile-chart');

		for (var r = 0; r < ranks.length; r++) {
			var top = (r === 0) ? high : ranks[r - 1].min_mmr;
			var bottom = ranks[r].min_mmr;
			if ((bottom >= high) || (top <= low)) continue;
			var band = document.createElementNS(svgNS, 'rect');
			band.setAttribute('x', 0);
			band.setAttribute('width', width);
			band.setAttribute('y', y(Math.min(top, high)));
			band.setAttribute('height', Math.max(0, y(Math.max(bottom, low)) - y(Math.min(top, high))));
			band.setAttribute('fill', ranks[r].color);
			band.setAttribute('opacity', '0.35');
			svg.appendChild(band);
		}

		var points = [];
		for (var p = 0; p < history.length; p++) {
			var x = (history.length > 1) ? Math.round(1000 * width * p / (history.length - 1)) / 1000 : 0;
			points.push(x + ',' + y(history[p]));
		}
		var line = document.createElementNS(svgNS, 'polyline');
		line.setAttribute('points', points.join(' '));
		line.setAttribute('fill', 'none');
		line.setAttribute('stroke', '#FFF');
		line.setAttribute('stroke-width', '2');
		line.setAttribute('vector-effect', 'non-scaling-stroke');
		svg.appendChild(line);

		var wrap = document.createElement('div');
		wrap.className = 'lounge-profile-chartbox';
		wrap.appendChild(svg);
		wrap.appendChild(lbCell('span', 'lounge-profile-charttop', high - pad));
		wrap.appendChild(lbCell('span', 'lounge-profile-chartbottom', low + pad));
		return wrap;
	}

	function showLoungePlayer(playerId) {
		var container = lbLoading();
		if (!container) return;
		postJSON('lounge/player.php', 'player=' + encodeURIComponent(playerId), function(data) {
			if (!data || data.error) return;
			container.innerHTML = '';
			container.appendChild(lbBack('Back to the leaderboard', 'Retour au classement', showLoungeOverview));
			if (!data.player) {
				lbBar('Player stats', 'Statistiques du joueur');
				lbEmpty(container,
					'This player has not played a ranked mogi this season.',
					'Ce joueur n\'a pas joué de mogi classé cette saison.');
				return;
			}
			var player = data.player;
			lbBar('Player stats', 'Statistiques du joueur');

			var head = document.createElement('div');
			head.className = 'lounge-profile-head';
			head.appendChild(lbCell('h2', 'lounge-profile-name', player.name));
			if (player.rank) head.appendChild(rankChip(player.rank));
			container.appendChild(head);

			var stats = player.stats;
			var grid = document.createElement('div');
			grid.className = 'lounge-profile-grid';
			grid.appendChild(statLine('Rating', 'MMR', player.mmr));
			grid.appendChild(statLine('Ranking', 'Rang',
				placeEl(player.place) + toLanguage(' of ', ' sur ') + player.ladder_size));
			grid.appendChild(statLine('Best / worst', 'Meilleur / pire',
				stats ? (placeEl(stats.best_place) + ' / ' + placeEl(stats.worst_place)) : null));
			grid.appendChild(statLine('Max / min rating', 'MMR max / min',
				stats ? (stats.max_mmr + ' / ' + stats.min_mmr) : null));
			grid.appendChild(statLine('Initial rating', 'MMR de départ', player.initial_mmr));
			grid.appendChild(statLine('Matches played', 'Mogis joués', player.games));
			grid.appendChild(statLine('Wins / losses', 'Victoires / défaites',
				player.wins + ' / ' + (player.games - player.wins)));
			grid.appendChild(statLine('Win ratio', 'Ratio', ratio(player.wins, player.games)));
			grid.appendChild(statLine('Max gain / loss', 'Gain / perte max',
				stats ? (signed(stats.max_gain) + ' / ' + signed(stats.max_loss)) : null));
			grid.appendChild(statLine('Points', 'Points', player.total_score));
			grid.appendChild(statLine('Max points gain', 'Meilleur score', stats ? stats.max_score : null));
			grid.appendChild(statLine('Avg points gain', 'Score moyen', player.avg_score));
			grid.appendChild(statLine('Last played', 'Dernier mogi', stats ? timeAgo(stats.last_played_ago) : null));
			grid.appendChild(statLine('First played', 'Premier mogi', stats ? timeAgo(stats.first_played_ago) : null));
			container.appendChild(grid);

			if (player.history.length > 1 && data.ranks)
				container.appendChild(historyChart(player.history, data.ranks));

			if (player.matches.length) {
				var card = lbCard('Matches played', 'Mogis joués', null);
				card.appendChild(matchListEl(player.matches, player.id, true));
				container.appendChild(card);
			}
		});
	}

	function showLoungeMatch(matchId) {
		var container = lbLoading();
		if (!container) return;
		postJSON('lounge/match.php', 'match=' + encodeURIComponent(matchId), function(data) {
			if (!data || data.error) return;
			container.innerHTML = '';
			container.appendChild(lbBack('Back to the leaderboard', 'Retour au classement', showLoungeOverview));
			if (!data.match) {
				lbBar('Mogi results', 'Résultats du mogi');
				lbEmpty(container, 'This mogi does not exist.', 'Ce mogi n\'existe pas.');
				return;
			}
			var match = data.match;
			lbBar('Mogi results', 'Résultats du mogi');

			var head = document.createElement('div');
			head.className = 'lounge-profile-head';
			head.appendChild(lbCell('h2', 'lounge-profile-name', toLanguage('Match #', 'Mogi n°') + match.id));
			head.appendChild(lbCell('span', 'lounge-profile-when', timeAgo(match.ended_ago)));
			container.appendChild(head);
			container.appendChild(matchSummaryEl(match));
			container.appendChild(matchTableEl(match, data.me));
			container.appendChild(ratingUpdatesEl(match, data.me));
		});
	}

	function matchSummaryEl(match) {
		var sub = document.createElement('p');
		sub.className = 'lounge-results-sub';
		sub.textContent = match.tier_label + ' — ' + match.mode + ' — '
			+ match.races + ' ' + toLanguage('races', 'courses');
		// Otherwise a voided mogi is a table of ratings that never arrive, with nothing on the
		// page to say they never will.
		if (match.cancelled_reason) {
			sub.appendChild(document.createElement('br'));
			sub.appendChild(document.createTextNode((match.cancelled_reason === 'no_show')
				? toLanguage('Voided: the lineup never turned up', 'Annulé : l\'effectif ne s\'est pas présenté')
				: toLanguage('Voided: the mogi was abandoned', 'Annulé : le mogi a été abandonné')));
		}
		return sub;
	}

	// The standings, as one table whatever the mode: in a team mogi each side gets a header
	// row carrying its colour and its total, and its members sit under it. That is the shape
	// the staff asked for, and it is the same table the mogi ends on.
	function matchTableEl(match, me) {
		var table = lbTable('lounge-results-table', [
			{ className: 'lounge-results-place', label: toLanguage('Place', 'Place') },
			{ className: 'lounge-results-name', label: toLanguage('Player', 'Joueur') },
			{ className: 'lounge-results-score', label: toLanguage('Score', 'Score') },
			{ className: 'lounge-results-races', label: toLanguage('Races', 'Courses') }
		]);
		if (!match.teams.length) {
			appendMatchPlayers(table, match, match.players, me);
			var total = lbRow('lounge-results-total', [
				lbCell('td', 'lounge-results-totallabel', toLanguage('Total', 'Total')),
				lbCell('td', 'lounge-results-score', match.total)
			]);
			total.firstChild.colSpan = 2;
			total.lastChild.colSpan = 2;
			table.appendChild(total);
			return table;
		}
		for (var i = 0; i < match.teams.length; i++) {
			var team = match.teams[i];
			var name = document.createElement('span');
			name.appendChild(teamSwatch(team.team));
			name.appendChild(document.createTextNode(team.names.join(' + ')));
			var header = lbRow('lounge-results-teamhead', [
				lbCell('td', 'lounge-results-place', '#' + (i + 1)),
				lbCell('td', 'lounge-results-teamname', name),
				lbCell('td', 'lounge-results-score', team.score)
			]);
			header.childNodes[1].colSpan = 2;
			table.appendChild(header);
			var members = [];
			for (var j = 0; j < match.players.length; j++) {
				if (match.players[j].team === team.team) members.push(match.players[j]);
			}
			appendMatchPlayers(table, match, members, me);
		}
		return table;
	}

	function appendMatchPlayers(table, match, players, me) {
		for (var i = 0; i < players.length; i++) {
			var p = players[i];
			var races = lbCell('td', 'lounge-results-races',
				// Zero is "no attendance was recorded", not "raced none of it" - a mogi played
				// before attendance was tracked has it for everyone.
				p.races_played ? (p.races_played + '/' + match.races) : null);
			if (p.races_played && (p.races_played < match.races)) {
				races.className += ' is-short';
				races.title = toLanguage('A bot raced in their place', 'Un bot a couru à sa place');
			}
			table.appendChild(lbRow(
				'lounge-results-row' + ((p.id === me) ? ' is-self' : '')
					+ (match.teams.length ? ' is-teamed' : ''),
				[
					lbCell('td', 'lounge-results-place', p.position),
					lbCell('td', 'lounge-results-name', playerLink(p)),
					lbCell('td', 'lounge-results-score', p.score),
					races
				]
			));
		}
	}

	// Lorenzi's "Rating Updates" block: where each player stood, what the mogi moved, and
	// where that left them. The before was the whole point of the staff's request - a delta
	// on its own never says what it was applied to.
	function ratingUpdatesEl(match, me) {
		var box = document.createElement('div');
		box.className = 'lounge-ratings';
		box.appendChild(lbCell('h3', 'lounge-ratings-title', toLanguage('Rating updates', 'Évolution du MMR')));
		var table = document.createElement('table');
		table.className = 'lounge-ratings-table';
		for (var i = 0; i < match.players.length; i++) {
			var p = match.players[i];
			var move = document.createElement('span');
			move.className = 'lounge-placemove';
			move.appendChild(lbCell('span', 'lounge-placemove-from', placeEl(p.place_before)));
			move.appendChild(arrowEl((p.place_before === null || p.place_after === null)
				? 0 : (p.place_before - p.place_after)));
			move.appendChild(lbCell('span', 'lounge-placemove-to', placeEl(p.place_after)));

			var delta = lbCell('td', 'lounge-ratings-delta', deltaEl(p.mmr_delta));
			if (p.mmr_penalty) {
				var penalty = document.createElement('div');
				penalty.className = 'lounge-results-penalty';
				penalty.textContent = '(' + p.mmr_penalty + ')';
				penalty.title = toLanguage('Absence penalty', 'Pénalité d\'absence');
				delta.appendChild(penalty);
			}
			table.appendChild(lbRow(
				'lounge-ratings-row' + ((p.id === me) ? ' is-self' : ''),
				[
					lbCell('td', 'lounge-ratings-move', move),
					lbCell('td', 'lounge-ratings-name', playerLink(p)),
					lbCell('td', 'lounge-ratings-before', p.mmr_before),
					delta,
					lbCell('td', 'lounge-ratings-arrow', arrowEl(p.mmr_delta)),
					lbCell('td', 'lounge-ratings-after', p.mmr_after),
					lbCell('td', 'lounge-ratings-rank', p.rank ? rankChip(p.rank) : null)
				]
			));
		}
		box.appendChild(table);
		return box;
	}

	// A link straight to a player or a mogi, so a result can be pasted into the Discord and
	// open on the thing being talked about rather than on the leaderboard.
	var lbRouted = false;

	function loadLeaderboard() {
		if (!lbRouted) {
			lbRouted = true;
			var match = location.search.match(/[?&]match=(\d+)/);
			if (match) return showLoungeMatch(parseInt(match[1], 10));
			var player = location.search.match(/[?&]player=(\d+)/);
			if (player) return showLoungePlayer(parseInt(player[1], 10));
			var requested = location.search.match(/[?&]view=(players|matches)/);
			if (requested)
				return (requested[1] === 'players') ? showLoungePlayers() : showLoungeMatches();
		}
		showLoungeOverview();
	}

	function tierLabel(tier) {
		return tier.label;
	}

	function tierRangeLabel(tier) {
		if (tier.code === 'all') return toLanguage('Open to everyone', 'Ouvert à tous');
		if (tier.max_mmr === null) return 'MMR ' + tier.min_mmr + '+';
		return 'MMR ' + tier.min_mmr + '–' + tier.max_mmr;
	}

	// Staff want everyone to have seen the rules once before their first queue, and a player
	// who cannot enter at all should learn why here rather than after clicking Join. Both
	// take over the tier screen, so neither can be clicked past.
	function renderTierScreen(data) {
		var container = $('lounge-tiers');
		if (!container) return;
		if (!data.rules_accepted) {
			renderRulesGate(container);
			return;
		}
		if (data.access_error && data.access_error !== 'rules_not_accepted') {
			renderAccessBlock(container, data);
			return;
		}
		renderTiers(data.tiers);
	}

	function renderRulesGate(container) {
		// the poll keeps ticking behind this screen; rebuilding it would clear the tick box
		// under the reader every few seconds
		if (container.querySelector('.lounge-rules-gate')) return;
		container.innerHTML = '';
		var box = document.createElement('div');
		box.className = 'lounge-rules-gate';

		// cloned from the "?" panel so the gate can never drift from the published rules
		var bar = document.querySelector('[data-panel="howitworks"] .lounge-bar');
		if (bar) box.appendChild(bar.cloneNode(true));
		var body = document.createElement('div');
		body.className = 'lounge-rules-body';
		var panel = document.querySelector('.lounge-rules');
		body.innerHTML = panel ? panel.innerHTML : '';
		box.appendChild(body);

		var label = document.createElement('label');
		label.className = 'lounge-rules-check';
		var check = document.createElement('input');
		check.type = 'checkbox';
		var text = document.createElement('span');
		text.textContent = toLanguage(
			'I have read and accept the lounge rules.',
			'J\'ai lu et j\'accepte les règles du lounge.'
		);
		label.appendChild(check);
		label.appendChild(text);
		box.appendChild(label);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'lounge-rules-accept';
		btn.disabled = true;
		btn.textContent = toLanguage('Continue', 'Continuer');
		check.addEventListener('change', function() {
			btn.disabled = !check.checked;
		});
		btn.addEventListener('click', function() {
			if (actionInFlight || !check.checked) return;
			actionInFlight = true;
			btn.disabled = true;
			postJSON('lounge/accept-rules.php', '', function() {
				actionInFlight = false;
				pollOnce();
			});
		});
		box.appendChild(btn);
		container.appendChild(box);
	}

	function renderAccessBlock(container, data) {
		container.innerHTML = '';
		var box = document.createElement('div');
		box.className = 'lounge-access-block';
		var title = document.createElement('h2');
		title.textContent = toLanguage('Not open to you yet', 'Pas encore accessible');
		box.appendChild(title);

		var list = document.createElement('ul');
		var req = data.requirements || {};
		if (req.min_vs_points) {
			list.appendChild(requirementRow(
				toLanguage(
					req.min_vs_points + ' points in online VS mode',
					req.min_vs_points + ' points en mode en ligne VS'
				),
				toLanguage('you have ' + data.vs_points, 'vous en avez ' + data.vs_points),
				data.vs_points >= req.min_vs_points
			));
		}
		if (req.min_account_age_days) {
			var age = data.account_age_days;
			list.appendChild(requirementRow(
				toLanguage(
					'an account at least ' + req.min_account_age_days + ' days old',
					'un compte d\'au moins ' + req.min_account_age_days + ' jours'
				),
				age === null ? '' : toLanguage(age + ' days', age + ' jours'),
				(age === null) || (age >= req.min_account_age_days)
			));
		}
		box.appendChild(list);

		if (data.access_error === 'site_banned') {
			var banned = document.createElement('p');
			banned.className = 'lounge-access-note';
			banned.textContent = toLanguage('Your account is banned.', 'Votre compte est banni.');
			box.appendChild(banned);
		}
		container.appendChild(box);
	}

	function requirementRow(what, have, met) {
		var li = document.createElement('li');
		li.className = met ? 'is-met' : 'is-unmet';
		li.textContent = what + (have ? ' — ' + have : '');
		return li;
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
			case 'not_enough_points': return toLanguage('You do not have enough online VS points for ranked.', 'Vous n\'avez pas assez de points en ligne VS pour le classé.');
			case 'rules_not_accepted': return toLanguage('You must accept the lounge rules first.', 'Vous devez d\'abord accepter les règles du lounge.');
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
		// the start is announced off the recap rather than off the status, so it lands when
		// the lineup is told what it is playing rather than a beat later
		if (!wasKnown || (status === 'launched') || !STATUS_ALERTS[status] || !alertsEnabled()) return;
		fireAlert(status);
	}

	// The recap is where a waiting player finds out the mogi is on, so it carries the alert the
	// launch used to. A player who was not waiting gets no alert, the way they never did.
	function announceStart(queue, wasWatching) {
		if (announcedStart) return;
		var settled = (queue.status === 'launched')
			|| ((queue.status === 'drafting') && queue.draft && !queue.draft.available.length);
		if (!settled) return;
		announcedStart = true;
		sawRecap = (queue.status !== 'launched');
		if (wasWatching && alertsEnabled())
			fireAlert('launched');
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
			'Still here? Please confirm before ' + formatCountdown(left) + ' or you\'ll be dropped',
			'Toujours là ? Confirmez sous ' + formatCountdown(left) + ' ou vous serez retiré de la liste'
		);
		box.appendChild(text);

		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'lounge-confirm-btn';
		btn.textContent = toLanguage('Keep me in', 'Je reste !');
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

	function renderAlertControls() {
		var controls = document.createDocumentFragment();
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
		toggle.querySelector('.lounge-alerts-label').textContent = toLanguage('Match alerts', 'Notifications');
		toggle.querySelector('.lounge-alerts-state').textContent = alertStateLabel(on);
		toggle.addEventListener('click', onAlertToggle);
		controls.appendChild(toggle);

		if (on && mkNotify.permission() === 'denied') {
			var warn = document.createElement('span');
			warn.className = 'lounge-alerts-hint';
			warn.textContent = toLanguage(
				'Notifications are blocked for this site — only the sound and the tab title will alert you.',
				'Les notifications sont bloquées pour ce site — seuls le son et le titre de l\'onglet vous alerteront.'
			);
			controls.appendChild(warn);
		}
		return controls;
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
		if (currentQueue) renderWaiting(currentQueue);
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

		var wasWatching = (announcedStatus !== null);
		announceStatus(queue.status);
		announceStart(queue, wasWatching);
		setUnloadGuard(queue.status !== 'launched');

		if (queue.status === 'launched' && queue.privgame_key) {
			// the recap is already on screen and says all of this, so it stays there until the
			// room takes over rather than blinking through a second announcement
			if (sawRecap)
				goToRoom(queue);
			else
				renderLaunching(container, queue);
			return;
		}

		container.innerHTML = '';

		var header = document.createElement('div');
		header.className = 'lounge-waiting-header';
		var label = queue.tier_label;
		header.innerHTML = '<h2>' + label + '</h2>'
			+ '<p class="lounge-waiting-count">' + queue.members.length + ' / ' + queue.ready_threshold + ' ' + toLanguage('players', 'joueurs') + '</p>';
		container.appendChild(header);

		var status = document.createElement('div');
		status.className = 'lounge-waiting-status';
		var statusText = document.createElement('p');
		statusText.className = 'lounge-waiting-text';
		status.appendChild(statusText);
		if (queue.status === 'open') {
			statusText.textContent = toLanguage(
				'Waiting for more players… you can still drop.',
				'En attente d\'autres joueurs… vous pouvez encore quitter.'
			);
		} else if (queue.status === 'locked') {
			// a lineup that divides into nothing has FFA as its only option, so there is no
			// vote to announce - it starts as soon as the wait is over
			var onlyMode = (queue.allowed_modes.length === 1) ? queue.allowed_modes[0] : null;
			var lockLeft = queue.lock_seconds_left;
			if (lockLeft === null) {
				statusText.textContent = onlyMode
					? toLanguage(
						'Queue locked. ' + onlyMode + ' starts soon.',
						'File verrouillée. ' + onlyMode + ' commence bientôt.'
					)
					: toLanguage(
						'Queue locked. Voting starts soon.',
						'File verrouillée. Le vote commence bientôt.'
					);
			} else if (onlyMode) {
				statusText.textContent = toLanguage(
					'Queue locked. ' + onlyMode + ' starts in ' + formatCountdown(lockLeft) + '.',
					'File verrouillée. ' + onlyMode + ' commence dans ' + formatCountdown(lockLeft) + '.'
				);
			} else {
				statusText.textContent = toLanguage(
					'Queue locked. Voting starts in ' + formatCountdown(lockLeft) + '.',
					'File verrouillée. Le vote commence dans ' + formatCountdown(lockLeft) + '.'
				);
			}
		} else if (queue.status === 'voting') {
			var voteLeft = queue.vote_seconds_left;
			statusText.textContent = toLanguage(
				'Vote the game mode (' + formatCountdown(voteLeft) + ' left)',
				'Votez pour le mode de jeu (' + formatCountdown(voteLeft) + ' restant)'
			);
		} else if (queue.status === 'drafting') {
			// what was settled is announced below, in the size it deserves; this line is only
			// what happens next
			statusText.textContent = (queue.draft && queue.draft.current_captain)
				? toLanguage(
					'The captains are picking their teams.',
					'Les capitaines composent leurs équipes.'
				)
				: toLanguage('The race is about to start.', 'La course va commencer.');
		}
		announceConfirm(queue);
		// above the status card: missing this one drops you from the lineup
		if (queue.confirm_due)
			container.appendChild(renderConfirmPrompt(queue));
		status.appendChild(renderAlertControls());
		container.appendChild(status);
		if (queue.status === 'drafting')
			container.appendChild(renderModeVerdict(queue));

		// the team columns already account for everyone; an FFA recap has none, so it keeps the
		// lineup it was already showing
		var showLineup = (queue.status !== 'drafting') || !queue.draft || !queue.draft.teams.length;
		var list = document.createElement('ol');
		list.className = 'lounge-member-list';
		for (var i = 0; showLineup && (i < queue.members.length); i++) {
			var m = queue.members[i];
			var li = document.createElement('li');
			li.className = 'lounge-member' + (m.id === mId ? ' is-self' : '');
			li.innerHTML = '<span class="lounge-member-name"></span> <span class="lounge-member-mmr">MMR ' + m.mmr + '</span>';
			li.querySelector('.lounge-member-name').textContent = m.name;
			list.appendChild(li);
		}
		// the empty seats say what the lineup is still waiting for, rather than the count alone
		for (var j = queue.members.length; showLineup && (queue.status === 'open') && (j < queue.lock_threshold); j++) {
			var slot = document.createElement('li');
			slot.className = 'lounge-slot';
			slot.textContent = toLanguage('Waiting for a player…', 'En attente d\'un joueur…');
			list.appendChild(slot);
		}
		if (showLineup)
			container.appendChild(list);

		if (queue.status === 'voting') {
			container.appendChild(renderVoteSection(queue));
		} else if (queue.status === 'drafting') {
			if (queue.draft && queue.draft.teams.length)
				container.appendChild(renderDraft(queue.draft));
		} else {
			container.appendChild(renderQueueActions(queue));
		}
	}

	// "2v2" is the ladder's name for pairs, however many pairs that makes, so a lineup of eight
	// is told it is playing 2v2v2v2 rather than left to count the columns.
	function modeShape(queue) {
		var teamCount = (queue.draft && queue.draft.teams.length) ? queue.draft.teams.length : 0;
		var teamSize = parseInt(queue.mode, 10);
		if (!teamCount || !teamSize) return queue.mode;
		var parts = [];
		for (var i = 0; i < teamCount; i++) parts.push(teamSize);
		return parts.join('v');
	}

	// The vote's answer, at the size of an answer. It used to be half of a sentence in the
	// status line, which is not where anyone looks to find out what they are about to play.
	function renderModeVerdict(queue) {
		var box = document.createElement('div');
		box.className = 'lounge-verdict';
		var mode = document.createElement('p');
		mode.className = 'lounge-verdict-mode';
		mode.textContent = modeShape(queue);
		box.appendChild(mode);

		var note = '';
		if (queue.draft && !queue.draft.current_captain) {
			if (!queue.draft.teams.length)
				note = toLanguage('Everyone for themselves.', 'Chacun pour soi.');
			else if (queue.draft.captains.length)
				note = toLanguage('The captains have picked.', 'Les capitaines ont choisi.');
			else
				note = toLanguage('Teams drawn at random.', 'Équipes tirées au sort.');
		}
		if (note) {
			var line = document.createElement('p');
			line.className = 'lounge-verdict-note';
			line.textContent = note;
			box.appendChild(line);
		}
		return box;
	}

	// A drafted side is named after its captain; a drawn one has no captain to name it after.
	function renderDraftTeam(captain, members, index) {
		var col = document.createElement('section');
		col.className = 'lounge-draft-team';
		var title = document.createElement('h3');
		title.textContent = captain
			? toLanguage('Team ' + captain.name, 'Équipe ' + captain.name)
			: toLanguage('Team ' + (index + 1), 'Équipe ' + (index + 1));
		col.appendChild(title);
		var list = document.createElement('ol');
		for (var i = 0; i < members.length; i++) {
			var li = document.createElement('li');
			li.className = (captain && (members[i].id === captain.id)) ? 'is-captain' : '';
			li.textContent = members[i].name;
			list.appendChild(li);
		}
		col.appendChild(list);
		return col;
	}

	function renderDraft(draft) {
		var section = document.createElement('div');
		section.className = 'lounge-draft';

		var myTurn = draft.current_captain && (draft.current_captain.id === mId);
		var turn = document.createElement('p');
		turn.className = 'lounge-draft-turn' + (myTurn ? ' is-mine' : '');
		if (!draft.current_captain) {
			// the verdict above already says the teams are settled and how
			turn = null;
		} else if (myTurn) {
			turn.textContent = toLanguage(
				'Your pick — ' + formatCountdown(draft.seconds_left) + ' left',
				'À vous de choisir — ' + formatCountdown(draft.seconds_left) + ' restant'
			);
		} else {
			turn.textContent = toLanguage(
				draft.current_captain.name + ' is picking (' + formatCountdown(draft.seconds_left) + ' left)',
				draft.current_captain.name + ' choisit (' + formatCountdown(draft.seconds_left) + ' restant)'
			);
		}
		if (turn)
			section.appendChild(turn);

		var teams = document.createElement('div');
		teams.className = 'lounge-draft-teams';
		for (var t = 0; t < draft.teams.length; t++)
			teams.appendChild(renderDraftTeam(draft.captains[t], draft.teams[t], t));
		section.appendChild(teams);

		if (draft.available.length) {
			var pool = document.createElement('div');
			pool.className = 'lounge-draft-pool';
			for (var i = 0; i < draft.available.length; i++) {
				var m = draft.available[i];
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'lounge-draft-pick';
				btn.setAttribute('data-player', m.id);
				btn.disabled = !myTurn;
				btn.innerHTML = '<span class="lounge-draft-pick-name"></span>'
					+ '<span class="lounge-draft-pick-mmr">MMR ' + m.mmr + '</span>';
				btn.querySelector('.lounge-draft-pick-name').textContent = m.name;
				btn.addEventListener('click', onDraftPickClick);
				pool.appendChild(btn);
			}
			section.appendChild(pool);

			var hint = document.createElement('p');
			hint.className = 'lounge-draft-hint';
			hint.textContent = toLanguage(
				'A captain who runs out of time gets the highest-rated player left.',
				'Un capitaine à court de temps reçoit le joueur le mieux classé restant.'
			);
			section.appendChild(hint);
		}
		return section;
	}

	function onDraftPickClick(e) {
		if (actionInFlight) return;
		var target = e.currentTarget.getAttribute('data-player');
		actionInFlight = true;
		var buttons = document.querySelectorAll('.lounge-draft-pick');
		for (var i = 0; i < buttons.length; i++) buttons[i].disabled = true;
		postJSON('lounge/draft.php', 'player=' + encodeURIComponent(target), function(data) {
			actionInFlight = false;
			if (data && data.queue) {
				currentQueue = data.queue;
				renderWaiting(currentQueue);
			}
		});
	}

	function renderQueueActions(queue) {
		var actions = document.createElement('div');
		actions.className = 'lounge-waiting-actions';
		if (queue.status === 'open') {
			var dropBtn = document.createElement('button');
			dropBtn.type = 'button';
			dropBtn.className = 'lounge-drop';
			// rule 3a: no flickering in and out of a gathering list
			var dropWait = queue.drop_seconds_left || 0;
			dropBtn.disabled = dropWait > 0;
			dropBtn.textContent = dropWait
				? toLanguage('Drop (' + dropWait + 's)', 'Quitter (' + dropWait + 's)')
				: toLanguage('Drop', 'Quitter');
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
			'If the timer runs out, the mode with the most votes wins. A tie is drawn at random,'
				+ ' and so is Random.',
			'À l\'expiration du chrono, le mode le plus voté l\'emporte. Une égalité est tirée au sort,'
				+ ' comme le vote Random.'
		);
		section.appendChild(hint);
		section.appendChild(renderModeVote(queue));
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
		var modes = queue.allowed_modes.concat(['Random']);
		for (var i = 0; i < modes.length; i++) {
			var mode = modes[i];
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

	// Only for a player who never saw the recap: they have walked in on a mogi that is already
	// running, so they are told it is on and taken there without a countdown.
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
		goToRoom(queue, 1200);
	}

	function goToRoom(queue, delay) {
		if (leavingForRoom) return;
		leavingForRoom = true;
		var url = 'online.php?mid=' + queue.multicup_id + '&ranked&key=' + queue.privgame_key;
		setTimeout(function() {
			if (window.parent && window.parent !== window) {
				window.parent.location.href = url;
			} else {
				window.location.href = url;
			}
		}, delay || 0);
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
		var body = 'mode=' + encodeURIComponent(mode);
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
			if (data.error === 'drop_too_soon') {
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
		announcedStatus = null;
		announcedConfirm = false;
		announcedStart = false;
		sawRecap = false;
		setUnloadGuard(false);
		mkNotify.clear();
	}

	function renderResults(match) {
		var container = $('lounge-results');
		if (!container) return;
		container.innerHTML = '';

		var header = document.createElement('div');
		header.className = 'lounge-results-header';
		header.appendChild(lbCell('h2', null, toLanguage('Mogi results', 'Résultats du mogi')));
		header.appendChild(matchSummaryEl(match));
		container.appendChild(header);

		// The same table and rating block the match page draws, so a result read on the way out
		// of a mogi and the same result looked up a week later are one screen.
		container.appendChild(matchTableEl(match, mId));
		container.appendChild(ratingUpdatesEl(match, mId));

		var actions = document.createElement('div');
		actions.className = 'lounge-results-actions';

		var back = document.createElement('button');
		back.type = 'button';
		back.className = 'lounge-results-back';
		back.textContent = toLanguage('Back to the lounge', 'Retour au lounge');
		back.addEventListener('click', function() {
			// re-enter through the ranked flow so the character gets picked again
			(window.top || window).location.href = 'ranked.php';
		});
		actions.appendChild(back);

		var discord = discordLink();
		if (discord)
			actions.appendChild(discord);
		container.appendChild(actions);
	}

	// The results are where a mogi ends, which is where the Discord has something to offer:
	// the post-match talk happens there. Cloned off the rules panel so the invite and the logo
	// are spelled once.
	function discordLink() {
		var source = document.querySelector('.lounge-rules .lounge-discord');
		if (!source) return null;
		var link = source.cloneNode(false);
		var logo = source.querySelector('svg');
		if (logo) link.appendChild(logo.cloneNode(true));
		link.appendChild(document.createTextNode(
			toLanguage('Continue on Discord', 'Continuer sur Discord')
		));
		return link;
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

	// The overlay's close button lives in the parent window, which can see none of this. It
	// needs to know whether the player is in a lineup, because goToRoom() navigates the parent
	// - a lounge that has been thrown away can never take anyone to their race.
	function reportStateToParent() {
		if (!window.parent || (window.parent === window)) return;
		window.parent.postMessage({
			mkpcLounge: true,
			queued: !!currentQueue,
			status: currentQueue ? currentQueue.status : null,
			players: currentQueue ? currentQueue.members.length : 0,
			threshold: currentQueue ? currentQueue.lock_threshold : 0,
			strikes: lastPlayerState ? (lastPlayerState.strikes || 0) : 0
		}, location.origin);
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
				renderTierScreen(data);
				reportStateToParent();
				pollTimer = setTimeout(pollOnce, POLL_INTERVAL_TIERS);
			});
		} else {
			postJSON('lounge/poll.php', '', function(data) {
				if (!data || data.error) {
					pollTimer = setTimeout(pollOnce, POLL_INTERVAL_WAITING);
					return;
				}
				if (data.player) {
					lastPlayerState = data.player;
					renderPlayerStrip(data.player);
				}
				if (!data.queue) {
					leaveQueueState();
					switchView('tiers');
					reportStateToParent();
					return;
				}
				currentQueue = data.queue;
				renderWaiting(currentQueue);
				reportStateToParent();
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
