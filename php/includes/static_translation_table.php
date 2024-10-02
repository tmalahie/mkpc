<?php

// Some rules:
// - each entry must be exactly in the format '"key" => array(),'
//   yes, the comma at the end is needed
// - use only double quotes for the strings
// - "en" and "fr" translations are mandatory
// - if you use parameters for formatting, ensure the key has the parameter in its name
//   e.g. kMESSAGE_WITH_NAME for "Hello from {name}
// - if using plurals, you must provide #one and #other variants of your translations
// - if using plurals, the parameter "count" must be used to indicate the number

define(
	"TRANSLATION_TABLE",
	array(
		"kHTML_TAG_LANG_ATTRIBUTE" => array(
			/* The 'lang' attribute for <html> tag */
			"en" => "en",
			"fr" => "fr",
		),
		"kA_MARIO_KART_GAME_FOR_BROWSER" => array(
			"en" => "A Mario Kart Game for browser",
			"fr" => "Un jeu de Mario Kart sur navigateur",
		),
		"kCLICK_ON_THE_GAME_BOX_TO_BEGIN" => array(
			"en" => "Click on the game box to begin",
			"fr" => "Cliquez sur la boîte du jeu pour commencer",
		),
		"kA_COMPUTER_VERSION_OF_THE_FAMOUS_RACING_GAME" => array(
			"en" => "A computer version of the famous racing game by Nintendo.",
			"fr" => "Une version ordi du célèbre jeu de course de Nintendo.",
		),
		"k_THIS_GAME_IS_COMPLETELY_FREE" => array(
			"en" => "This game is <strong>completely free</strong> and does not require <strong>any downloads</strong>. All you need is a web browser!",
			"fr" => "Ce jeu est <strong>entièrement gratuit</strong> et ne requiert <strong>aucun téléchargement</strong>, un simple navigateur internet suffit !",
		),
		"kCRAZY_RACES_FULL_OF_FUN" => array(
			"en" => "Crazy races full of fun!",
			"fr" => "Des courses acharnées et pleines de fun !",
		),
		"kTRY_TO_BE_THE_FASTEST" => array(
			"en" => "Try to be the fastest while avoiding items!",
            "fr" => "Tentez d'être le plus rapide tout en évitant les objets !",
		),
		"kRACE_ON_ALL_THE_56_TRACKS" => array(
			"en" => "Race on all the <strong>56 tracks</strong> from the original games <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> and <strong>Mario Kart DS</strong>.",
            "fr" => "Retrouvez l'intégralité des <strong>56 circuits</strong> repris du jeu original <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> et <strong>Mario Kart DS</strong>.",
        ),
		"kWIN_ALL_THE_CUPS" => array(
			"en" => "Win all the cups!",
			"fr" => "Remportez tous les grand prix !",
		),
		"kFACE_OFF_WITH_THE_CPUS_ON_14_GRAND_PRIX" => array(
			"en" => "Face off with the CPUs on the <strong>14 grand prix</strong> tournaments and try to win the gold trophy!",
			"fr" => "Affrontez les ordis sur les <strong>14 grand prix</strong> et tentez de gagner la coupe en or !",
		),
		"kWIN_ENOUGH_CUPS_TO_UNLOCK_SECRET_CHARACTERS" => array(
			"en" => "Win enough cups to unlock the <strong>15 secret characters</strong>!",
			"fr" => "Remportez suffisament de coupes pour débloquer les <strong>15 persos secrets</strong> !",
		),
		"kCREATE_YOUR_OWN_TRACKS" => array(
			"en" => "Create your own tracks!",
			"fr" => "Créez vos propres circuits !",
		),
		"kWITH_THE_TRACK_BUILDER_THE_POSSIBILITIES_ARE_ENDLESS" => array(
			"en" => "With the <strong>track builder</strong>, the possibilities are endless; the only limit is your imagination!",
			"fr" => "Avec l'<strong>éditeur de circuits</strong> et d'arènes, les possibilités sont infinies  ; votre imagination est la seule limite !",
		),
		"kYOU_CAN_SHARE_YOUR_TRACKS" => array(
			"en" => "You can <strong>share</strong> your tracks or try other people's creations!",
			"fr" => "Essayez les créations des autres grâce à l'<strong>outil de partage intégré</strong>!",
		),
		"kFACE_PLAYERS_FROM_AROUND_THE_WORLD" => array(
			"en" => "Face players from around the world!",
			"fr" => "Affrontez les joueurs du monde entier !",
		),
		"kRACE_AND_BATTLE_IN_ONLINE_MODE" => array(
			"en" => "Race and battle in <strong>online mode</strong>!",
			"fr" => "Battez-vous contre d'autres joueurs avec le <strong>mode en ligne</strong> !",
		),
		"kWIN_AS_MANY_RACES_AS_POSSIBLE_AND_CLIMB_IN_THE_OFFICIAL_RANKING" => array(
			"en" => "Win as many races as possible and <strong>climb in the official ranking</strong>!",
			"fr" => "Remportez un maximum de course afin de <strong>grimper dans le classement</strong> officiel !",
		),
		"kMAKE_THE_BEST_SCORES_IN_TIME_TRIAL" => array(
			"en" => "Make the best scores in time trial!",
			"fr" => "Réalisez les meilleurs temps en contre-la-montre !",
		),
		"kFINISH_THE_RACE_TRACK_AS_FAST_AS_YOU_CAN" => array(
			"en" => "<strong>Finish the race track</strong> as fast as you can!",
			"fr" => "<strong>Bouclez les 3 tours</strong> le plus rapidement possible !",
		),
		"kCOMPARE_YOUR_SCORES_WITH_THE_COMMUNITY" => array(
			"en" => "<strong>Compare your scores</strong> with the community, and face other players' ghosts!",
			"fr" => "<strong>Comparez votre score</strong> avec la communauté, et affrontez les fantômes des autres joueurs !",
		),
		"kRELEASE_YOUR_FIGHTER_TALENT" => array(
			"en" => "Release your fighter talents!",
			"fr" => "Montrez vos talents de combattant !",
		),
		"kDESTROY_YOUR_OPPONENTS_BALLOONS" => array(
			"en" => "<strong>Destroy your opponents</strong>' balloons with items, without getting hit by their items!",
			"fr" => "<strong>Détruisez les ballons</strong> de votre adversaire en évitant de vous faire toucher !",
		),
		"kTHE_LAST_PLAYER_STANDING_WINS" => array(
			"en" => "The last player standing wins!",
			"fr" => "Soyez le dernier survivant pour remporter la partie !",
		),
		"kFACE_OFF_YOUR_FRIENDS_WITH_THE_LOCAL_MULTIPLAYER_MODE" => array(
			"en" => "Face off your friends with the local multiplayer mode!",
			"fr" => "Affrontez vos amis grâce au mode multijoueur !",
		),
		"kPROVE_TO_YOUR_FRIENDS_THAT_YOU_RE_THE_BEST" => array(
			"en" => "Prove to your friends that you're the best!",
			"fr" => "Montrez à vos amis que vous êtes le meilleur !",
		),
		"kFACE_THEM_IN_MULTIPLAYER_IN_VS_RACES" => array(
			"en" => "Face them in <strong>multiplayer</strong> in VS races or in battle mode.",
			"fr" => "Affrontez-les en <strong>multijoueur</strong> en course VS ou sur les batailles de ballons.",
		),
        "kSTART_GAME" => array(
            "en" => "Start game",
            "fr" => "Accéder au jeu",
        ),
		"kWHAT_S_MARIO_KART_PC" => array(
			"en" => "What's Mario Kart PC?",
			"fr" => "Mario Kart PC, c'est quoi ?",
		),
		"kYOU_MIGHT_KNOW_MARIO_KART" => array(
			"en" => "You might know Mario Kart, the most fun racing game series of all time! Mario Kart PC uses the same base as the original games but is playable on your browser, and <strong>for free</strong>.",
			"fr" => "Vous connaissez certainement Mario Kart, le jeu de course le plus fun de tous les temps ! Mario Kart PC reprend les mêmes principes que le jeu original mais il est jouable sur navigateur, et <strong>gratuitement</strong>.",
		),
		"kMOST_OF_THE_MODES_HAVE_BEEN_INCLUDED" => array(
			"en" => "Most of the modes from Mario Kart have been included: Grand Prix, VS, Battle mode, Time Trials, and more!",
			"fr" => "La plupart des modes issus de Mario Kart ont été repris : Grand Prix, courses VS, batailles de ballons, contre-la-montre, et d'autres !",
		),
		"kTHERE_S_ALSO_A_BRAND_NEW_MODE_THE_TRACK_BUILDER" => array(
			"en" => "There's also a brand new mode: the <strong>track builder</strong>! Place straight lines and turns, add items, boost panels and more! Everything is customizable! The only limit is your own imagination!",
			"fr" => "Et un dernier mode inédit : l'<strong>éditeur de circuits</strong> ! Placez les lignes droites et les virages, ajoutez les objets, insérez des accélérateurs...Tout est personnalisable ! Votre imagination est la seule limite !",
		),
		"kYOU_CAN_SHARE_YOUR_TRACKS_PARAM_URL" => array(
			"en" => "You can share your tracks, and try other people's tracks thanks to the <a href=\"{url}\">sharing tool</a>. Thousands of custom tracks are already available!",
			"fr" => "Vous pouvez également partager vos créations et essayer celles des autres grâce à l'<a href=\"{url}\">outil de partage</a>. Plusieurs milliers de circuits ont déjà été partagés !",
		),
		"kYOU_CAN_FACE_PLAYERS_FROM_THE_WHOLE_WORD_PARAM_URL" => array(
			"en" => "Finally, you can face players from the whole world thanks to the <strong>multiplayer online mode</strong>! Climb the <a href=\"{url}\">rankings</a> and become world champion!",
			"fr" => "Enfin, il est possible d'affronter les joueurs du monde entier grâce au <strong>mode multijoueurs en ligne</strong> ! Grimpez dans le <a href=\"{url}\">classement</a> et devenez champion du monde !",
		),
		"kSOME_SCREENSHOTS" => array(
			"en" => "Some screenshots",
			"fr" => "Quelques screenshots",
		),
		"kHERE_ARE_SOME_SCREENSHOTS" => array(
			"en" => "Here are some screenshots of the game to give you a quick preview of what it looks like:",
			"fr" => "Une image vaut mieux qu'un long discours, voici donc quelques captures d'écran issues du jeu afin que vous ayez un aperçu de ce à quoi ça ressemble :",
		),
		"kSPECIAL_THANKS" => array(
			"en" => "Special thanks",
			"fr" => "Remerciements",
		),
		"kA_BIG_THANK_TO_NINTENDO" => array(
			"en" => "A big thanks to Nintendo, these three sites and these artists without which Mario Kart PC would have probably never existed !",
			"fr" => "Un grand merci à Nintendo, ces 3 sites et ces artistes sans lesquels Mario Kart PC n'aurait probablement jamais existé !",
		),
		"kTHANKS_NIHILOGIC_PARAM_URL_MAIN_SITE_URL_MARIO_KART" => array(
			"en" => "<a href=\"{url_main_site}\">Nihilogic</a> for the <a href=\"{url_mario_kart}\">basic Mario Kart</a>",
			"fr" => "<a href=\"{url_main_site}\">Nihilogic</a> pour le <a href=\"{url_mario_kart}\">Mario Kart de départ</a>",
		),
		"kTHANKS_SNES_MAP_PARAM_URL_MAIN_SITE_URL_MARIO_KART" => array(
			"en" => "<a href=\"{url_main_site}\">SNESMaps</a> for the <a href=\"{url_mario_kart}\">track images</a>",
			"fr" => "<a href=\"{url_main_site}\">SNESMaps</a> pour les <a href=\"{url_mario_kart}\">images des circuits</a>",
		),
		"kTHANKS_KHINSIDER_PARAM_URL_MAIN_SITE_URL_MARIO_KART" => array(
			"en" => "a href=\"{url_main_site}\">Khinsider</a> for the <a href=\"{url_mario_kart}\">musics</a>",
			"fr" => "<a href=\"{url_main_site}\">Khinsider</a> for the <a href=\"{url_mario_kart}\">musics</a>",
		),
		"kTHANKS_OTHERS_PARAM_URL" => array(
			"en" => "And <a href=\"{url}\">many more</a>!",
			"fr" => "Et <a href=\"{url}\">bien d'autres</a> !",
		),
		"kFOLLOW_US" => array(
			"en" => "Follow us",
			"fr" => "Nous suivre",
		),
		"kDISCORD_SERVER_PARAM_URL" => array(
			"en" => "<a href=\"{url}\">Discord Server</a> of the site: join it to chat with the community and be informed about updates and events.",
			"fr" => "<a href=\"{url}\">Serveur Discord</a> du site : rejoignez-le pour discuter avec la communauté et être informé des mises à jours et événements.",
		),
		"kOFFICIAL_YOUTUBE_CHANNEL_PARAM_URL_YOUTUBE_URL_TOPIC" => array(
			"en" => "<a href=\"{url_youtube}\">Official Youtube Channel</a>: find videos on the game and information about the website and its events. The channel is maintained by members, if you want to participate, tell it on the <a href=\"{url_topic}\">official topic</a>.",
			"fr" => "<a href=\"{url_youtube}\">Chaîne Youtube Officielle</a> : retrouvez des vidéos sur le jeu et des informations sur le site et ses évenements. La chaîne est alimentée par les membres, si vous voulez participez, parlez-en sur <a href=\"{url_topic}\">le topic officiel</a>.",
		),
		"kGITHUB_REPO_PARAM_URL" => array(
			"en" => "<a href=\"{url}\">Github repo</a> of the site. Follow all the ongoing developments here, and if you can code, don't hesitate to contribute to the project!",
			"fr" => "<a href=\"{url}\">Repo Github</a> du site : suivez ici tous les développements en cours, et si vous avez des connaissances en code, n'hésitez pas à venir contribuer !",
		),
		"kMKPC_WIKI_PARAM_URL_WIKI_URL_TOPIC" => array(
			"en" => "<a href=\"{url_wiki}\">MKPC Wiki</a>: find out all the information about the game and its history. This site is maintained by the community, if you want to contribute, tell it on <a href=\"{url_topic}\">this topic</a>!",
			"fr" => "<a href=\"{url_wiki}\">Wiki MKPC</a>: retrouvez toutes les informations sur le jeu et son histoire. Ce site est maintenu par les membres, si vous voulez contribuer, parlez-en sur <a href=\"{url_topic}\">ce topic</a>&nbsp;!",
		),
		"kFORUM_TRANSLATION_TOPIC_PARAM_URL_TOPIC" => array(
			"en" => "This site is mostly maintained by French members, if you see some translation errors in the game or the site, don't hesitate to report them on this <a href=\"{url_topic}\">forum topic</a>",
			"fr" => "<span style=\"display: none\"><a href=\"{url_topic}\">Topic de traduction</a></span>",
		),
		"kGO_TO_THE_GAME" => array(
			"en" => "Go to the game",
			"fr" => "Accéder au jeu",
		),
		"kTO_START_PLAYING_CLICK_HERE" => array(
			"en" => "To start playing, it's very simple, just click on &quot;Play game&quot; in the menu above. Or more simply, click here:",
			"fr" => "Pour commencer à jouer, c'est très simple, cliquez sur &quot;Le jeu&quot; dans le menu en haut. Ou plus simplement, cliquez là:",
		),
		"kSTART_PLAYING_NOW" => array(
			"en" => "Start playing now &gt;",
			"fr" => "Commencer à jouer &gt;",
		),
		"kRECEIVED_WARNING_INAPPROPRIATE_BEHAVIOR_PARAM_URL" => array(
			"en" => "You have received a warning for inappropriate behavior. Please <a href=\"{url}\">click here</a> to find it out.",
			"fr" => "Vous avez reçu un avertissement pour comportement inapproprié. <a href=\"{url}\">Cliquez ici</a> pour en prendre connaissance.",
		),
		"kHAPPY_BIRTHDAY_TO" => array(
			"en" => "Happy birthday to",
			"fr" => "C'est l'anniversaire de",
		),
		"kBIRTHDAY_AND" => array(
			"en" => " and ",
			"fr" => " et ",
		),
		"kFINAL_EXCLAMATION_POINT_IN_SENTENCE" => array(
			"en" => "!",
			"fr" => "&nbsp;!",
		),
		"kLATEST_TOPICS" => array(
			"en" => "Latest topics",
			"fr" => "Derniers topics",
		),
		"kLATEST_MESSAGE_BY_PARAM_NAME" => array(
			"en" => "Latest message by <strong>{name}</strong>",
			"fr" => "Dernier message par <strong>{name}</strong>",
		),
		"kLATEST_MESSAGE" => array(
			"en" => "Latest message",
			"fr" => "Dernier message",
		),
		"kCOMMENTS_MESSAGES_WITH_COUNT" => array(
			"en#one" => "{count} message",
			"en#other" => "{count} messages",
			"fr#one" => "{count} message",
			"fr#other" => "{count} messages",
		),
	)
);