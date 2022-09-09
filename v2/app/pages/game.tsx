import { NextPage } from "next";
import { useCallback, useEffect, useMemo } from "react";
import Ad from "../components/Ad/Ad";
import withServerSideProps from "../components/WithAppContext/withServerSideProps";
import Head from 'next/head'
import useLanguage from "../hooks/useLanguage";
import { useTranslation } from "next-i18next";
import ss1 from "../images/main/screenshots/ss1.png"
import { useCookies } from "react-cookie";
import useFetch from "../hooks/useFetch";
import { insertScript } from "../hooks/useScript";

const localesNs = ["game"];
const Game: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);

  const [cookies] = useCookies(["mkplayers", "mkteam", "mkdifficulty", "mkrecorder", "iQuality", "bMusic", "iSfx", "iScreenScale"]);

  const getCookieValue = useCallback((key: keyof typeof cookies, defaultValue: number) => {
    const res = +cookies[key];
    if (isNaN(res)) return defaultValue;
    return res;
  }, [cookies]);

  const { data: gameData } = useFetch("/api/getGameParams.php");
  const { data: mapsData } = useFetch("/api/getGameMaps.php");

  let globals;
  if (typeof window !== "undefined") {
    globals = window;

    globals.page = "MK";
    globals.selectedPlayers = getCookieValue("mkplayers", 8);
    globals.selectedTeams = getCookieValue("mkteam", 0);
    globals.selectedDifficulty = getCookieValue("mkdifficulty", 1);
    globals.language = language;
    globals.recorder = cookies.mkrecorder ?? "";
    globals.baseOptions = {
      quality: localStorage.getItem("iQuality") ? +localStorage.getItem("iQuality") : 5,
      music: localStorage.getItem("bMusic") ? +localStorage.getItem("bMusic"):0,
      sfx: localStorage.getItem("iSfx") ? +localStorage.getItem("iSfx"):0,
      screenscale: localStorage.getItem("iScreenScale") ? +localStorage.getItem("iScreenScale"):(screen.width < 800) ? ((screen.width < 480) ? 4 : 6) : ((screen.width < 1500) ? 8 : 10)
    };
    globals.isCup = false;
    globals.isBattle = false;
    globals.isSingle = false;
    globals.complete = false;
    globals.simplified = false;
    globals.listMaps = () => mapsData;
  }

  function handleModeSubmit() {
    return false;
  }

  useEffect(() => {
    if (!gameData) return;
    if (!globals) return;
    globals.lCircuits = gameData.circuitNames;
    globals.cp = gameData.characterNames;
    globals.pUnlocked = gameData.unlockedCharacters;
    globals.ptsGP = gameData.ptsGP;
    globals.PERSOS_DIR = gameData.customCharacterDir;
    globals.NBCIRCUITS = gameData.nbVsCircuits;
  }, [gameData, globals]);

  const areGameParamsLoaded = useMemo(() => !!gameData && !!mapsData, [gameData, mapsData]);

  useEffect(() => {
    if (areGameParamsLoaded) {
      insertScript("/scripts/mk.js", {
        onload: () => {
          globals.MarioKart();
        }
      });
    }
  }, [areGameParamsLoaded]);

  return (
    <div>
      <Head>
        <title>Mario Kart PC</title>
        <meta name="author" content="Timothé Malahieude" />
        <meta name="description" content={t("Free_online_mario_kart")} />
        <meta name="keywords" content={t("Mario_kart_pc_game_race")} />
        <meta name="viewport" content="width=device-width, user-scalable=no" />
        <meta name="thumbnail" content={ss1.src} />
        <meta property="og:image" content={ss1.src} />
        <link rel="icon" href="/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="styles/mariokart.css" />
      </Head>

      <div id="mariokartcontainer"></div>

      <div id="virtualkeyboard"></div>

      <form name="modes" method="get" action="#null" onSubmit={handleModeSubmit}>
        <div id="options-ctn">
          <table cellPadding="3" cellSpacing="0" style={{ border: 0 }} id="options">
            <tbody>
              <tr>
                <td id="pSize">&nbsp;</td>
                <td id="vSize">
                </td>
                <td rowSpan={4} id="commandes">&nbsp;</td>
              </tr>
              <tr><td id="pMusic">
                &nbsp;
              </td>
                <td id="vMusic">
                  &nbsp;
                </td></tr>
              <tr><td id="pSfx">
                &nbsp;
              </td>
                <td id="vSfx">
                  &nbsp;
                </td></tr>
              <tr><td id="pFps">
                &nbsp;
              </td>
                <td id="vFps">
                  &nbsp;
                </td></tr>
            </tbody>
          </table>
        </div>
        <div id="vPub"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
          <Ad bannerId="6691323567" width={468} height={60} /></div>
      </form>
      <div id="dMaps"></div>
      <div id="scroller">
        <div>
          <img className="aObjet" alt="." src="images/items/fauxobjet.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/banane.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/carapace.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/bobomb.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/carapacerouge.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/carapacebleue.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/champi.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/megachampi.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/etoile.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/eclair.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/billball.png" /><br />&nbsp;<br />
          <img className="aObjet" alt="." src="images/items/champior.png" /><br />
        </div>
      </div>
      <div id="maps-list">
        {
          Object.keys([...Array(12)]).map((i) => <img key={i} src={"images/selectors/select_map" + (+i * 4 + 1) + ".png"} alt="" />)
        }
      </div>
      {/* TODO add game descrition */}
    </div>
  );
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default Game;