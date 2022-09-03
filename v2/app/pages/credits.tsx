import { NextPage } from "next";
import ClassicPage from "../components/ClassicPage/ClassicPage";
import styles from "../styles/Credits.module.scss";
import cx from "classnames";
import { useTranslation } from "next-i18next";
import withServerSideProps from "../components/WithAppContext/withServerSideProps";
import Link from "next/link";
import Head from "next/head";
import useLanguage from "../hooks/useLanguage";
import { Fragment, useMemo } from "react";
const localesNs = ["credits", "common"];
const CreationsList: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const credits = useMemo(() => ({
      [language ? 'Official Mario Kart resources':'Ressources des Mario Kart officiels']: [
          {
              'author': 'Nihilogic',
              'base_url': 'https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/',
              'res_url': 'https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/',
              'for': language ? 'for the':'pour le',
              'description': language ? 'basic Mario Kart':'Mario Kart de départ'
          },
          {
              'author': 'SNESMaps',
              'base_url': 'http://www.snesmaps.com/',
              'res_url': 'http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html',
              'for': language ? 'for the':'pour les',
              'description': language ? 'SNES track images':'images des circuits SNES'
          },
          {
              'author': 'MarioWiki',
              'base_url': 'https://www.mariowiki.com/',
              'res_url': 'https://www.mariowiki.com/Gallery:Mario_Kart:_Super_Circuit#Maps',
              'for': language ? 'for the':'pour les',
              'description': language ? 'GBA track images':'images des circuits GBA'
          },
          {
              'author': language ? '<a class="'+ styles.author +'" href="http://www.mariouniverse.com/">Mario Universe</a> and <a class="'+ styles.author +'" href="profil.php?id=4576">Link-Triforce-8</a>':'<a class="'+ styles.author +'" href="http://www.mariouniverse.com/">Mario Universe</a> et <a class="'+ styles.author +'" href="profil.php?id=4576">Link-Triforce-8</a>',
              'base_url': '',
              'res_url': 'http://www.mariouniverse.com/maps-ds-mk/',
              'for': language ? 'for the':'pour les',
              'description': language ? 'DS track images':'images des circuits DS'
              },
          {
              'author': 'Khinsider',
              'base_url': 'https://downloads.khinsider.com/',
              'res_url': 'https://downloads.khinsider.com/search?search=mario+kart',
              'for': language ? 'for the':'pour le',
              'description': language ? 'musics':'musiques'
          }
      ],
      [language ? 'Other resources - Sprites':'Autres ressources - Sprites']: [
          {
              'author_raw': language ? '<strong>Racoon Sam</strong>, <strong>EdpR</strong> and <a>Red5Pizza</a>' : '<strong>Racoon Sam</strong>, <strong>EdpR</strong> et <a>Red5Pizza</a>',
              'base_url': 'profil.php?id=8113',
              'res_url': 'images/sprites/sprite_daisy.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Daisy':'sprite de Daisy'
          },
          {
              'author': language ? '<strong>SWN</strong> and <strong>BVX</strong>':'<strong>SWN</strong> et <strong>BVX</strong>',
              'res_url': 'images/sprites/sprite_waluigi.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Waluigi':'sprite de Waluigi'
          },
          {
              'author': '<strong>Devicho</strong>',
              'res_url': 'images/sprites/sprite_bowser_jr.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Bowser Jr':'sprite de Bowser Jr'
          },
          {
              'author': '<strong>Clutch</strong>',
              'res_url': 'images/sprites/sprite_diddy-kong.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Diddy-Kong':'sprite de Diddy Kong'
          },
          {
              'author': '<strong>Flare</strong>',
              'res_url': 'images/sprites/sprite_birdo.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Birdo':'sprite de Birdo'
          },
          {
              'author': '<strong>Frario</strong>',
              'for': language ? 'for the':'pour le',
              'description': language ? '<a href="images/sprites/sprite_donkey-kong.png">sprite of Donkey Kong</a> and <a href="images/sprites/sprite_wario.png">of Wario</a>':'<a href="images/sprites/sprite_donkey-kong.png">sprite de Donkey Kong</a> et <a href="images/sprites/sprite_wario.png">de Wario</a>'
          },
          {
              'author': '<strong>Jex99</strong>',
              'for': language ? 'for the':'pour le',
              'description': language ? '<a href="images/sprites/sprite_funky-kong.png">sprite of Funky Kong</a> and <a href="images/sprites/sprite_frere_marto.png">of Hammer Bro</a>':'<a href="images/sprites/sprite_funky-kong.png">sprite de Funky Kong</a> et <a href="images/sprites/sprite_frere_marto.png">de Frère Marto</a>'
          },
          {
              'author': '<strong>Darking</strong>',
              'for': language ? 'for the':'pour le',
              'res_url': 'images/sprites/sprite_bowser_skelet.png',
              'description': language ? 'sprite of Dry Bowser':'sprite de Bowser Skelet'
          },
          {
              'author_raw': '<strong>X Gamer 66</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'images/sprites/sprite_flora_piranha.png',
              'description': language ? 'sprite of Petey Piranha':'sprite de Flora Piranha'
          },
          {
              'author': 'Red5Pizza',
              'base_url': 'profil.php?id=8113',
              'for': language ? 'for the':'pour les',
              'description': language ? 'sprites of <a href="images/sprites/sprite_link.png">Link</a>, <a href="images/sprites/sprite_billball.png">Bill Ball</a>, <a href="images/sprites/sprite_yoshi.png">Yoshi</a>, <a href="images/sprites/sprite_peach.png">Peach</a>, <a href="images/sprites/sprite_harmonie.png">Rosalina</a>, and <a href="images/sprites/sprite_roi_boo.png">King Boo</a>':'sprites de <a href="images/sprites/sprite_link.png">Link</a>, <a href="images/sprites/sprite_billball.png">Bill Ball</a>, <a href="images/sprites/sprite_yoshi.png">Yoshi</a>, <a href="images/sprites/sprite_peach.png">Peach</a>, <a href="images/sprites/sprite_harmonie.png">Harmonie</a>, et <a href="images/sprites/sprite_roi_boo.png">Roi Boo</a>'
          },
          {
              'author': '<strong>LISARTINO2009</strong>',
              'res_url': 'images/sprites/sprite_toadette.png',
              'for': language ? 'for the':'pour le',
              'description': language ? 'sprite of Toadette':'sprite de Toadette'
          },
          {
              'author': 'Angel121',
              'base_url': 'profil.php?id=45670',
              'for': language ? 'for the':'pour le',
              'description': language ? '<a href="images/sprites/sprite_skelerex.png">sprite of Dry Bones</a>':'<a href="images/sprites/sprite_skelerex.png">sprite de Skelerex</a>'
          },
          {
              'author': 'Hoppingicon',
              'base_url': 'profil.php?id=26749',
              'for': language ? 'for the':'pour le',
              'description': language ? '<a href="images/sprites/sprite_frere_marto.png">sprite of Hammer Bro</a>':'<a href="images/sprites/sprite_frere_marto.png">sprite de Frère Marto</a>'
          }
      ],
      [language ? 'Other resources - Musics':'Autres ressources - Musiques']: [
          {
              'author_raw': '<strong>Teck</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_wario.mp3',
              'description': language ? 'theme of Wario':'thème de Wario'
          },
          {
              'author_raw': '<strong>Jeff Daily</strong>, <strong>Mark7</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_daisy.mp3',
              'description': language ? 'theme of Daisy':'thème de Daisy'
          },
          {
              'author_raw': '<strong>辰</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'description': language ? 'theme of <a href="musics/endings/ending_roi_boo.mp3">King Boo</a>, <a href="musics/endings/ending_bowser_skelet.mp3">Dry Bowser</a>, and <a href="musics/endings/ending_bowser_jr.mp3">Bowser Jr</a>':'thème de <a href="musics/endings/ending_roi_boo.mp3">Roi Boo</a>, <a href="musics/endings/ending_bowser_skelet.mp3">Bowser Skelet</a> et <a href="musics/endings/ending_bowser_jr.mp3">Bowser Jr</a>'
          },
          {
              'author_raw': '<strong>ledinred</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_frere_marto.mp3',
              'description': language ? 'theme of Hammer Bro':'thème de Frère Marto'
          },
          {
              'author_raw': '<strong>PianoMan547</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_flora_piranha.mp3',
              'description': language ? 'theme of Petey Piranha':'thème de Flora Piranha'
          },
          {
              'author_raw': '<strong>Luigi P.</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_link.mp3',
              'description': language ? 'theme of Link':'thème de Link'
          },
          {
              'author_raw': '<strong>Blue.Nocturne</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_harmonie.mp3',
              'description': language ? 'theme of Rosalina':'thème de Harmonie'
          },
          {
              'author_raw': '<strong>Sephiroth3</strong> '+ (language ? 'and':'et') +' <a>Link-Triforce-8</a>',
              'base_url': 'profil.php?id=4576',
              'for': language ? 'for the':'pour le',
              'res_url': 'musics/endings/ending_diddy-kong.mp3',
              'description': language ? 'theme of Diddy-Kong':'thème de Diddy-Kong'
          }
      ]
    }), [language]);

  return (
    <ClassicPage title="Credits - Mario Kart PC" className={cx(styles.Credits, styles.CreditsRoot)} page="home">
      <Head>
        <link rel="stylesheet" type="text/css" href="styles/forum.css" />
      </Head>
      <h1>{(t("MKPC_Credits"))}</h1>
      { language ? <p>
        Mario Kart PC uses a variety of online resources for the game.<br />
        This page gathers all the sites and people who provided these resources.<br />
        Many thanks to them!
      </p> : <p>
        Mario Kart PC utilise un certain nombre de resources en ligne pour le jeu.<br />
        Cette page regroupe l'ensemble des sites et personnes à l'origine de ces resources.<br />
        Un grand merci à eux !
      </p>}
      <div id={styles.credits}>
        {Object.entries(credits).map(([group, groupCredits]) => <Fragment key={group}>
          <h2>{group}</h2>
          <ul>
            {groupCredits.map((credit: CreditProps["data"],i) => <Credit data={credit} key={i} />)}
          </ul>
        </Fragment>)}
      </div>
      <p>
        <Link href="/"><a>{t("common:Back_to_mario_kart_pc")}</a></Link>
      </p>
    </ClassicPage>
  );
}

interface CreditProps {
  data: {
    base_url?: string;
    author?: string;
    author_raw?: string;
    for: string;
    res_url?: string;
    description?: string;
    description_raw?: string;
  };
}
function Credit(props: CreditProps) {
  const credit = props.data;

  const prefix = useMemo(() => {
    if (credit.base_url) {
        if (credit.author)
            return <a className={styles.author} href={ credit.base_url } dangerouslySetInnerHTML={{__html:credit.author}} />;
        if (credit.author_raw)
            return <span dangerouslySetInnerHTML={{__html: credit.author_raw.replace(/<a>/g, '<a class="'+ styles.author +'" href="'+credit.base_url+'">')}} />;
    }
    else if (credit.author)
        return <span dangerouslySetInnerHTML={{ __html: credit.author }}></span>;
  }, [credit]);

  const suffix = useMemo(() => {
    if (credit.res_url) {
      if (credit.description)
          return <a href={credit.res_url} dangerouslySetInnerHTML={{ __html: credit.description }} />;
      else if (credit.description_raw)
          return <span dangerouslySetInnerHTML={{__html: credit.description_raw.replace(/<a>/g, '<a href="'+credit.res_url+'">') }} />;
    }
    else if (credit.description)
        return <span dangerouslySetInnerHTML={{ __html: credit.description }} />
  }, [credit]);

  return <li>
    {prefix}
    {" "}
    {credit.for}
    {" "}
    {suffix}
  </li>
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default CreationsList;