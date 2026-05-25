(function() {
	if (typeof mId !== 'number') return;

	var loungeState = null;

	function toLanguage(en, fr) {
		return language ? en : fr;
	}

	function $(id) {
		return document.getElementById(id);
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
			strikesLabel.innerHTML = '<span class="lounge-stat-label">'+ toLanguage('Strikes','Strikes') +'</span> <span class="lounge-stat-value">'+ player.strikes +'</span>';
			strip.appendChild(strikesLabel);
		}

		if (player.banned_until) {
			var ban = document.createElement('span');
			ban.className = 'lounge-stat lounge-stat--ban';
			ban.textContent = toLanguage('Banned until ', 'Banni jusqu\'au ') + player.banned_until;
			strip.appendChild(ban);
		}
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

			var label = (language ? tier.label_en : tier.label_fr) || tier.code;

			var title = document.createElement('h3');
			title.className = 'lounge-tier-title';
			title.textContent = label;
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
			if (!tier.eligible) {
				btn.disabled = true;
				btn.textContent = toLanguage('Locked', 'Verrouillé');
			} else {
				btn.disabled = true;
				btn.textContent = toLanguage('Join (coming soon)', 'Rejoindre (bientôt)');
			}
			card.appendChild(btn);

			container.appendChild(card);
		}
	}

	function loadTiers() {
		xhr('lounge/tiers.php', '', function(res) {
			var data;
			try { data = JSON.parse(res); }
			catch (e) { return false; }
			if (!data || data.error) return true;
			loungeState = data;
			renderPlayerStrip(data.player);
			renderTiers(data.tiers);
			return true;
		});
	}

	function init() {
		setupTabs();
		loadTiers();
	}

	if (document.readyState === 'loading')
		document.addEventListener('DOMContentLoaded', init);
	else
		init();
})();
