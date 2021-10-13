<?php
include('language.php');
include('session.php');
include('initdb.php');
$isBattle = isset($_GET['battle']);
$game = $isBattle ? 'battle':'vs';
$pts_ = 'pts_'.$game;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Credits - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<style type="text/css">
main {
    padding-left: 3%;
    padding-right: 3%;
}
.author {
    font-weight: bold;
}
#credits strong {
    color: #A0300A;
}
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'home';
include('menu.php');
?>
<main>
    <h1><?php echo $language ? 'Mario Kart PC - Credits':'Mario Kart PC - Crédits'; ?></h1>
    <p><?php
    if ($language) {
        ?>
        Mario Kart PC uses a variety of online resources for the game.<br />
        This page gathers all the sites and people who provided these resources.<br />
        Many thanks to them!
        <?php
    }
    else {
        ?>
        Mario Kart PC utilise un certain nombre de resources en ligne pour le jeu.<br />
        Cette page regroupe l'ensemble des sites et personnes à l'origine de ces resources.<br />
        Un grand merci à eux !
        <?php
    }
    ?></p>
    <div id="credits">
    <?php
    $credits = array(
        ($language ? 'Official Mario Kart resources':'Ressources des Mario Kart officiels') => array(
            array(
                'author' => 'Nihilogic',
                'base_url' => 'https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/',
                'res_url' => 'https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'basic Mario Kart':'Mario Kart de départ'
            ),
            array(
                'author' => 'SNESMaps',
                'base_url' => 'http://www.snesmaps.com/',
                'res_url' => 'http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html',
                'for' => $language ? 'for the':'pour les',
                'description' => $language ? 'SNES track images':'images des circuits SNES'
            ),
            array(
                'author' => 'MarioWiki',
                'base_url' => 'https://www.mariowiki.com/',
                'res_url' => 'https://www.mariowiki.com/Gallery:Mario_Kart:_Super_Circuit#Maps',
                'for' => $language ? 'for the':'pour les',
                'description' => $language ? 'GBA track images':'images des circuits GBA'
            ),
            array(
                'author' => $language ? '<a class="author" href="http://www.mariouniverse.com/">Mario Universe</a> and <a class="author" href="profil.php?id=4576">Link-Triforce-8</a>':'<a class="author" href="http://www.mariouniverse.com/">Mario Universe</a> et <a class="author" href="profil.php?id=4576">Link-Triforce-8</a>',
                'base_url' => '',
                'res_url' => 'http://www.mariouniverse.com/maps-ds-mk/',
                'for' => $language ? 'for the':'pour les',
                'description' => $language ? 'DS track images':'images des circuits DS'
                ),
            array(
                'author' => 'Khinsider',
                'base_url' => 'https://downloads.khinsider.com/',
                'res_url' => 'https://downloads.khinsider.com/search?search=mario+kart',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'musics':'musiques'
            )
        ),
        ($language ? 'Other resources - Sprites':'Autres ressources - Sprites') => array(
            array(
                'author_raw' => $language ? '<strong>Racoon Sam</strong>, <strong>EdpR</strong> and <a>Red5Pizza</a>' : '<strong>Racoon Sam</strong>, <strong>EdpR</strong> et <a>Red5Pizza</a>',
                'base_url' => 'profil.php?id=8113',
                'res_url' => 'images/sprites/sprite_daisy.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Daisy':'sprite de Daisy'
            ),
            array(
                'author' => $language ? '<strong>SWN</strong> and <strong>BVX</strong>':'<strong>SWN</strong> et <strong>BVX</strong>',
                'res_url' => 'images/sprites/sprite_waluigi.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Waluigi':'sprite de Waluigi'
            ),
            array(
                'author' => '<strong>Devicho</strong>',
                'res_url' => 'images/sprites/sprite_bowser_jr.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Bowser Jr':'sprite de Bowser Jr'
            ),
            array(
                'author' => '<strong>Clutch</strong>',
                'res_url' => 'images/sprites/sprite_diddy-kong.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Diddy-Kong':'sprite de Diddy Kong'
            ),
            array(
                'author' => '<strong>Flare</strong>',
                'res_url' => 'images/sprites/sprite_birdo.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Birdo':'sprite de Birdo'
            ),
            array(
                'author' => '<strong>Frario</strong>',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? '<a href="images/sprites/sprite_donkey-kong.png">sprite of Donkey Kong</a> and <a href="images/sprites/sprite_wario.png">of Wario</a>':'<a href="images/sprites/sprite_donkey-kong.png">sprite de Donkey Kong</a> et <a href="images/sprites/sprite_wario.png">de Wario</a>'
            ),
            array(
                'author' => '<strong>Jex99</strong>',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? '<a href="images/sprites/sprite_funky-kong.png">sprite of Funky Kong</a> and <a href="images/sprites/sprite_frere_marto.png">of Hammer Bro</a>':'<a href="images/sprites/sprite_funky-kong.png">sprite de Funky Kong</a> et <a href="images/sprites/sprite_frere_marto.png">de Frère Marto</a>'
            ),
            array(
                'author' => '<strong>Darking</strong>',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'images/sprites/sprite_bowser_skelet.png',
                'description' => $language ? 'sprite of Dry Bowser':'sprite de Bowser Skelet'
            ),
            array(
                'author_raw' => '<strong>X Gamer 66</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'images/sprites/sprite_flora_piranha.png',
                'description' => $language ? 'sprite of Petey Piranha':'sprite de Flora Piranha'
            ),
            array(
                'author' => 'Red5Pizza',
                'base_url' => 'profil.php?id=8113',
                'for' => $language ? 'for the':'pour les',
                'description' => $language ? 'sprites of <a href="images/sprites/sprite_link.png">Link</a>, <a href="images/sprites/sprite_billball.png">Bill Ball</a>, <a href="images/sprites/sprite_yoshi.png">Yoshi</a>, <a href="images/sprites/sprite_peach.png">Peach</a>, <a href="images/sprites/sprite_harmonie.png">Rosalina</a>, and <a href="images/sprites/sprite_roi_boo.png">King Boo</a>':'sprites de <a href="images/sprites/sprite_link.png">Link</a>, <a href="images/sprites/sprite_billball.png">Bill Ball</a>, <a href="images/sprites/sprite_yoshi.png">Yoshi</a>, <a href="images/sprites/sprite_peach.png">Peach</a>, <a href="images/sprites/sprite_harmonie.png">Harmonie</a>, et <a href="images/sprites/sprite_roi_boo.png">Roi Boo</a>'
            ),
            array(
                'author' => '<strong>LISARTINO2009</strong>',
                'res_url' => 'images/sprites/sprite_toadette.png',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'sprite of Toadette':'sprite de Toadette'
            ),
        ),
        ($language ? 'Other resources - Musics':'Autres ressources - Musiques') => array(
            array(
                'author_raw' => '<strong>Teck</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_wario.mp3',
                'description' => $language ? 'theme of Wario':'thème de Wario'
            ),
            array(
                'author_raw' => '<strong>Jeff Daily</strong>, <strong>Mark7</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_daisy.mp3',
                'description' => $language ? 'theme of Daisy':'thème de Daisy'
            ),
            array(
                'author_raw' => '<strong>辰</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'description' => $language ? 'theme of <a href="musics/endings/ending_roi_boo.mp3">King Boo</a>, <a href="musics/endings/ending_bowser_skelet.mp3">Dry Bowser</a>, and <a href="musics/endings/ending_bowser_jr.mp3">Bowser Jr</a>':'thème de <a href="musics/endings/ending_roi_boo.mp3">Roi Boo</a>, <a href="musics/endings/ending_bowser_skelet.mp3">Bowser Skelet</a> et <a href="musics/endings/ending_bowser_jr.mp3">Bowser Jr</a>'
            ),
            array(
                'author_raw' => '<strong>ledinred</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_frere_marto.mp3',
                'description' => $language ? 'theme of Hammer Bro':'thème de Frère Marto'
            ),
            array(
                'author_raw' => '<strong>PianoMan547</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_flora_piranha.mp3',
                'description' => $language ? 'theme of Petey Piranha':'thème de Flora Piranha'
            ),
            array(
                'author_raw' => '<strong>Luigi P.</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_link.mp3',
                'description' => $language ? 'theme of Link':'thème de Link'
            ),
            array(
                'author_raw' => '<strong>Blue.Nocturne</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_harmonie.mp3',
                'description' => $language ? 'theme of Rosalina':'thème de Harmonie'
            ),
            array(
                'author_raw' => '<strong>Sephiroth3</strong> '. ($language ? 'and':'et') .' <a>Link-Triforce-8</a>',
                'base_url' => 'profil.php?id=4576',
                'for' => $language ? 'for the':'pour le',
                'res_url' => 'musics/endings/ending_diddy-kong.mp3',
                'description' => $language ? 'theme of Diddy-Kong':'thème de Diddy-Kong'
            )
        )
    );
    foreach ($credits as $group=>$groupCredits) {
        echo '<h2>'.$group.'</h2>';
        echo '<ul>';
        foreach ($groupCredits as $credit) {
            ?>
            <li><?php
            if (isset($credit['base_url'])) {
                if (isset($credit['author']))
                    echo '<a class="author" href="'. $credit['base_url'] .'">'. $credit['author'] .'</a>';
                elseif (isset($credit['author_raw']))
                    echo str_replace('<a>', '<a class="author" href="'.$credit['base_url'].'">', $credit['author_raw']);
            }
            elseif (isset($credit['author']))
                echo $credit['author'];
            ?>
            <?php echo $credit['for']; ?> <?php
            if (isset($credit['res_url'])) {
                if (isset($credit['description']))
                    echo '<a href="'. $credit['res_url'] .'">'. $credit['description'] .'</a>';
                elseif (isset($credit['description_raw']))
                    echo str_replace('<a>', '<a href="'.$credit['res_url'].'">', $credit['description_raw']);
            }
            elseif (isset($credit['description']))
                echo $credit['description'];
        }
        echo '</ul>';
    }
    ?>
    </ul>
    </div>
	<p>
        <a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
    </p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>