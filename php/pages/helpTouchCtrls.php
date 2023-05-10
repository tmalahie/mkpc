<?php
include('../includes/language.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>"> 
	<head> 
		<title><?php echo $language ? 'Help: touch controls':'Aide : Contrôles tactiles'; ?></title> 
		<meta charset="utf-8" /> 
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
		<style type="text/css">
			body {
				background-color: black;
				color: white;
				text-align: center;
				font-family: Helvetica, arial, sans-serif;
				max-width: 500px;
				margin-left: auto;
				margin-right: auto;
			}
			h2 {
				margin-bottom: 0.4em;
			}
			.screen {
				position: relative;
				width: 150px;
				height: 250px;
				margin: 0.5em auto;
				border: solid 0.5em #666;
				border-radius: 0.5em;
				background-color: #333;
				overflow: hidden;
			}
			.mkscreen {
				position: relative;
				margin-left: 5px;
				margin-top: 5px;
				width: 140px;
				height: 70px;
				background-color: white;
				overflow: hidden;
			}
			.mkscreen .item {
				position: absolute;
				left: 3px;
				top: 2px;
				width: 12px;
				height: 10px;
				background-color: black;
				border: solid 1px #999;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			.mkscreen .item img {
				height: 100%;
			}
			.mkscreen .bg {
				position: absolute;
				left: 0;
				top: 0;
				width: 100%;
				height: 25%;
				background-color: #ccf;
				background-image: url('images/map_bg/pine.png');
				background-size: auto 100%;
			}
			.mkscreen .road {
				position: absolute;
				left: 0;
				top: 25%;
				width: 100%;
				height: 75%;
				background-color: #090;
			}
			.player {
				position: absolute;
				left: 58px;
				bottom: 6px;
				width: 24px;
				height: 24px;
				overflow: hidden;
			}
			.player > img {
				position: absolute;
				left: 0;
				top: 0;
				height: 24px;
			}
			.finger {
				position: absolute;
				left: -100px;
				top: -100px;
				width: 20px;
				height: 20px;
				background-color: #666;
				border: solid 2px #ccc;
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			.finger.touch {
				background-color: #bbb;
			}
		</style>
	</head>
	<body>
		<main>
			<?php
			function printScreen($eltId) {
				?>
				<div class="screen" id="<?php echo $eltId; ?>">
					<div class="mkscreen">
						<div class="bg"></div>
						<div class="road"></div>
						<div class="player">
							<img src="images/sprites/sprite_mario.png" alt="Mario" />
						</div>
						<div class="item">
							<img src="images/items/banane.png" alt="banana" />
						</div>
					</div>
					<div class="finger">
					</div>
				</div>
				<?php
			}
			?>
			<h1><?php echo $language ? 'Help: touch controls':'Aide : contrôles tactiles'; ?></h1>
			<div class="explain">
				<?php
				if ($language)
					echo "The touch controls work pretty much like <em>Mario Kart Tour</em>: touch the screen to start turning and move your finger in the direction you want. More precisely:";
				else
					echo "Les contrôles tactiles fonctionnent de manière assez proche de <em>Mario Kart Tour</em> : touchez l'écran pour commencer à tourner et déplacez votre doigt dans la direction que vous souhaitez. Plus précisément :";
				?>
			</div>
			<h2><?php echo $language ? 'Turn' : 'Tourner'; ?></h2>
			<div class="explain">
				<?php
				if ($language)
					echo "To turn, touch the screen <strong>below the game area</strong> and move your finger left or right depending on the direction you want.";
				else
					echo "Pour tourner, touchez l'écran <strong>en-dessous de la zone de jeu</strong> et déplacez votre doigt à gauche ou à droite en fonction de la direction souhaitée.";
				?>
			</div>
			<?php
			printScreen('screen-turn');
			?>
			<h2><?php echo $language ? 'Jump / Drift' : 'Sauter / Déraper'; ?></h2>
			<div class="drift">
				<?php
				if ($language)
					echo "To jump, <strong>double-tap on the screen</strong>. To drift, move your finger in the desired direction.";
				else
					echo "Pour sauter, tapez <strong>2 fois sur l'écran</strong>. Pour déraper, déplacez votre doigt dans la direction souhaitée.";
				?>
			</div>
			<?php
			printScreen('screen-drift');
			?>
			<h2><?php echo $language ? 'Use item' : 'Utiliser un objet'; ?></h2>
			<div class="drift">
				<?php
				if ($language) {
					echo "If the option in question is enabled, <strong>touch the game screen</strong> and move your finger in the desired direction (up to send forwards, down to send backwards).<br />";
					echo "If the option is disabled, touch the item area at the top left.";
				}
				else {
					echo "Si l'option en question est activée, <strong>touchez l'écran de jeu</strong> et déplacez votre doigt dans la direction souhaitée (en haut pour envoyer en avant, en bas pour envoyer en arrière).<br />";
					echo "Si l'option est désactivée, touchez la zone des objets en haut à gauche.";
				}
				?>
			</div>
			<?php
			printScreen('screen-item');
			?>
			<h2><?php echo $language ? 'Move backwards' : 'Reculer'; ?></h2>
			<div class="backwards">
				<?php
				if ($language) {
					echo "To move backwards, touch the screen <strong>below the game area</strong> and move your finger down.<br />";
					echo "To move forward again, move your finger up.";
				}
				else {
					echo "Pour reculer, touchez l'écran <strong>en-dessous de la zone de jeu</strong> et déplacez votre doigt vers le bas.<br />";
					echo "Pour avancer de nouveau, déplacer votre doigt vers le haut.";
				}
				?>
			</div>
			<?php
			printScreen('screen-back');
			?>
		</main>
		<script type="text/javascript">
			var FINGER_W = 24, PLAYER_W = 24, PLAYER_Y = 6, SCREEN_W = 150;
			function sleep(ms) {
				return new Promise(resolve => setTimeout(resolve, ms));
			}
			function placeFinger($screen, pos) {
				var $finger = $screen.querySelector(".finger");
				var w = SCREEN_W;
				$finger.style.left = Math.round(pos.x*SCREEN_W - FINGER_W/2) +"px";
				$finger.style.top = Math.round(pos.y*SCREEN_W - FINGER_W/2) +"px";
				return pos;
			}
			async function moveFinger($screen, start, end, dt, options) {
				var currentPos = start;
				var SPF = 67;
				var nbSteps = Math.round(dt/SPF);
				options = options || {};
				var frameEvents = options.frameEvents || [];
				var lastT = 0;
				var currentT = 0;
				for (var i=0;i<nbSteps;i++) {
					var t = i/(nbSteps-1);
					var pos = {
						x: start.x + t * (end.x - start.x),
						y: start.y + t * (end.y - start.y)
					};
					placeFinger($screen, pos);
					for (var j=0;j<frameEvents.length;j++) {
						var fT = frameEvents[j].t;
						if ((fT === undefined) || (lastT < fT && currentT >= fT))
							frameEvents[j].callback(t);
					}
					lastT = currentT;
					currentT += SPF;
					await sleep(SPF);
				}
				return end;
			}
			function markTouched($screen) {
				var $finger = $screen.querySelector(".finger");
				$finger.classList.add("touch");
			}
			function markReleased($screen) {
				var $finger = $screen.querySelector(".finger");
				$finger.classList.remove("touch");
			}
			function setPlayerState($screen, state) {
				var $player = $screen.querySelector(".player img");
				$player.style.left = -(PLAYER_W*state) +"px";
			}
			async function setPlayerPos($screen, y) {
				var $player = $screen.querySelector(".player");
				$player.style.bottom = (PLAYER_Y+y) +"px";
			}
			function setBgPos($screen, pos) {
				var $bg = $screen.querySelector(".bg");
				$bg.style.backgroundPosition = Math.round(-pos*100) + "px 0";
			}
			async function animateTurn() {
				var $screen = document.getElementById("screen-turn");
				while (true) {
					var pos = placeFinger($screen, {
						x: 0.5,
						y: 0.7
					});
					await sleep(300);
					markTouched($screen);
					await sleep(200);
					pos = await moveFinger($screen, pos, {
						x: 0.2,
						y: pos.y
					}, 500, {
						frameEvents: [{
							t: 100,
							callback: function() {
								setPlayerState($screen, 23);
							}
						}, {
							callback: function(t) {
								setBgPos($screen, -t*0.1);
							}
						}]
					});
					await sleep(100);
					markReleased($screen);
					setPlayerState($screen, 0);
					await sleep(600);
					setBgPos($screen, 0);
				}
			}
			animateTurn();

			async function animateDrift() {
				var $screen = document.getElementById("screen-drift");
				while (true) {
					var pos = placeFinger($screen, {
						x: 0.5,
						y: 0.7
					});
					await sleep(300);
					markTouched($screen);
					await sleep(100);
					markReleased($screen);
					await sleep(100);
					markTouched($screen);
					setPlayerPos($screen, 4);
					pos = await moveFinger($screen, pos, {
						x: 0.2,
						y: pos.y
					}, 500, {
						frameEvents: [{
							t: 50,
							callback: function() {
								setPlayerState($screen, 18);
							}
						}, {
							t: 100,
							callback: function() {
								setPlayerPos($screen, 0);
							}
						}, {
							callback: function(t) {
								setBgPos($screen, -t*0.1);
							}
						}]
					});
					await sleep(100);
					markReleased($screen);
					setPlayerState($screen, 0);
					await sleep(600);
					setBgPos($screen, 0);
				}
			}
			animateDrift();

			async function animateItem() {
				var $screen = document.getElementById("screen-item");
				while (true) {
					var pos = placeFinger($screen, {
						x: 0.3,
						y: 0.3
					});
					await sleep(300);
					markTouched($screen);
					var $banana = $screen.querySelector(".item img");
					await sleep(100);
					markReleased($screen);
					$banana.style.position = "absolute";
					$banana.style.left = Math.round(SCREEN_W/2 - 14) +"px";
					$banana.style.top = "52px";
					await sleep(500);
					markTouched($screen);
					pos = await moveFinger($screen, pos, {
						x: pos.x,
						y: 0.7
					}, 300, {
						frameEvents: [{
							t: 200,
							callback: function() {
								$banana.style.top = "60px";
							}
						}]
					});
					await sleep(100);
					markReleased($screen);
					await sleep(600);
					$banana.style.position = "";
				}
			}
			animateItem();

			async function animateBack() {
				var $screen = document.getElementById("screen-back");
				while (true) {
					var pos = placeFinger($screen, {
						x: 0.5,
						y: 0.7
					});
					await sleep(300);
					markTouched($screen);
					pos = await moveFinger($screen, pos, {
						x: pos.x,
						y: 1
					}, 300, {
						frameEvents: [{
							t: 100,
							callback: function() {
								setPlayerPos($screen, -2);
							}
						}]
					});
					await sleep(100);
					markReleased($screen);
					await sleep(600);
					markTouched($screen);
					pos = await moveFinger($screen, pos, {
						x: pos.x,
						y: 0.7
					}, 300, {
						frameEvents: [{
							t: 100,
							callback: function() {
								setPlayerPos($screen, 0);
							}
						}]
					});
					await sleep(100);
					markReleased($screen);
					setPlayerPos($screen, 0);
					await sleep(600);
				}
			}
			animateBack();
		</script>
	</body> 
</html>
