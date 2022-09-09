import useLanguage from "../../hooks/useLanguage";
import useAuthUser from "../../hooks/useAuthUser";
import useFetch from "../../hooks/useFetch";
import { insertScript } from "../../hooks/useScript";
import styles from "./ClassicPage.module.scss"
import innerStyles from "./Common.module.scss"
import english from "../../images/icons/english.png";
import french from "../../images/icons/french.png";
import notifSettings from "../../images/icons/notif-settings.png";
import banner from "../../images/main/header/banner.png";
import headerLeft from "../../images/main/header/ic_left.png";
import headerRight from "../../images/main/header/ic_right.png";
import footerLeft from "../../images/main/footer/ic_left.png";
import footerRight from "../../images/main/footer/ic_right.png";
import { ReactNode, useEffect, useState } from "react";
import cx from "classnames"
import Link from "next/link";
import Head from 'next/head'

function Flag({ nLanguage, src: srcData, alt, page, homepage = false }) {
  let url;
  let language = useLanguage();
  if (homepage) {
    url = nLanguage ? '/en.php' : '/fr.php';
    alt = nLanguage ? 'Home - Mario Kart PC' : 'Accueil - Mario Kart PC';
  }
  else
    url = '/changeLanguage.php?nLanguage=' + nLanguage + '&amp;page=' + page;
  const chosen = (nLanguage === language);
  function handleClick() {
    if (chosen)
      return false;
  }
  return <a href={url} title={homepage ? alt : ''} onClick={handleClick}><img id={chosen ? styles.chosen : styles.toChoose} src={srcData.src} alt={alt} /></a>;
}

type Notif = {
  id: number,
  ids: number[],
  link: string,
  content: string,
}
type Props = {
  page: "home" | "game" | "forum";
  className?: string;
  title?: string;
  children: ReactNode;
  noOnlineChat?: boolean;
}
function ClassicPage(props: Props) {
  const language = useLanguage();
  const { id } = useAuthUser() ?? {};
  const page = props.page;

  const { data: notifsPayload } = useFetch(`/api/getNotifs.php`);
  const [notifsList, setNotifsList] = useState<Notif[]>(null);
  const [nbNotifs, setNbNotifs] = useState<number>(null);
  useEffect(() => {
    if (notifsPayload) {
      setNotifsList(notifsPayload.data);
      setNbNotifs(notifsPayload.count);
    }
  }, [notifsPayload]);

  function closeNotifs() {
    setNotifsList([]);
    setNbNotifs(0);
  }
  function closeNotif(e, notif) {
    e.preventDefault();
    setNotifsList(notifsList.filter(n => n.id !== notif.id));
    setNbNotifs(nbNotifs - 1);
    // TODO make API call to close notif
  }
  useEffect(() => {
    if (props.noOnlineChat) {
      if (window["o_unloadOnline"]) {
        window["o_online"] = 0;
        window["o_active"] = 0;
        window["o_unloadOnline"]();
      }
      delete window["o_loaded"];
      var chatelt = document.getElementById("connect");
      if (chatelt)
        document.body.removeChild(chatelt);
      return;
    }
    if (window["o_loaded"] === undefined) {
      window["o_loaded"] = false;
      insertScript("scripts/online.js");
    }
  }, [props.noOnlineChat]);

  return (
    <div className={styles.ClassicPage}>
      <Head>
        <title>{props.title || "Mario Kart PC"}</title>
        <meta name="author" content="Timothé Malahieude" />
        <meta name="keywords" content={ language ? 'Mario, Kart, PC, game, race, free game, multiplayer, circuits editor':'Mario, Kart, PC, jeu, course, jeu gratuit, multijoueur, éditeur de circuits' } />
        <link rel="icon" href="/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="/styles/main.css" />
        {!props.noOnlineChat && <link rel="stylesheet" type="text/css" href="/styles/online.css" />}
      </Head>
      <header role="banner">
        <table>
          <tbody><tr>
            <td id={styles.header_left}>
              <img src={headerLeft.src} alt="/|" />
            </td>
            <td id={styles.header_center}>
              <img src={banner.src} alt="Mario Kart PC" />
            </td>
            <td id={styles.header_right}>
              <img src={headerRight.src} alt="|\" />
            </td>
          </tr>
          </tbody>
        </table>
      </header>
      <nav>
        <div id={styles.menu_left}>
          <Flag nLanguage={1} src={english} alt="English" page={props.page} />
          <Flag nLanguage={0} src={french} alt="Français" page={props.page} />
          {/* TODO handle homepage */}
        </div>
        <div id={styles.menu_right}>
          {notifsList && <div id={styles["notifs-bubble"]} className={nbNotifs ? styles.notifs : styles["no-notifs"]}>
            <div id={styles["notifs-nb-alert"]}>
              {nbNotifs}
            </div>
            <div className={styles["notifs-container"]}>
              {nbNotifs ? <div id={styles["nb-notifs"]}>
                <strong>{nbNotifs}</strong> notification{(nbNotifs > 1 ? 's' : '')}
                {id && <a href="/notif-settings.php"><img src={notifSettings.src} alt="Settings" title={language ? 'Notification settings' : 'Paramètres de notifications'} /></a>}
              </div> : <div id={styles["no-notif"]}>
                {language ? 'No notifications' : 'Aucune notification'}
                {id && <a href="/notif-settings.php"><img src={notifSettings.src} alt="Settings" title={language ? 'Notification settings' : 'Paramètres de notifications'} /></a>}
              </div>}
              <div id={styles["notifs-list"]}>
                {notifsList.map((notif) => <a key={notif.id} className={styles["notif-container"]} href={notif.link}>
                  <div className={styles["notif-options"]}><span className={styles["close-notif"]} onClick={(e) => closeNotif(e, notif)}>&times;</span></div>
                  <div className={styles["notif-value"]} dangerouslySetInnerHTML={{ __html: notif.content }} />
                </a>)}
              </div>
              <div id={styles["notifs-options"]}>
                <input type="button" value={language ? 'Mark everything as read' : 'Tout marquer comme lu'} onClick={closeNotifs} />
              </div>
            </div>
          </div>}
        </div>
        <div id={styles.menu_center} role="menubar">
          <Link href="/"><a id={(page === 'home') ? styles.thispage : ""} role="menuitem">{language ? 'Home' : 'Accueil'}</a></Link>{" "}
          <Link href="/game"><a id={(page === 'game') ? styles.thispage : ""} role="menuitem">{language ? 'Play game' : 'Le jeu'}</a></Link>{" "}
          <Link href="/forum"><a id={(page === 'forum') ? styles.thispage : ""} role="menuitem">Forum</a></Link>
        </div>
      </nav>
      <main className={cx(innerStyles.Common, props.className)}>{props.children}</main>
      <footer>
        <div id={styles.relief_bottom}>
          <img src={footerLeft.src} id={styles.relief_left} alt="<" />
          <img src={footerRight.src} id={styles.relief_right} alt=">" />
        </div>
        <div id={styles.footer_bottom}>
          <div id={styles.footer_left}></div>
          <div id={styles.footer_right}></div>
          <table id={styles.footer_center}><tbody><tr><td>Mario Kart PC © 2010-{new Date().getFullYear()}<span id={styles.developer}> | Designed and developed by Wargor</span></td></tr></tbody></table>
        </div>
      </footer>
    </div>
  );
}

export const commonStyles = innerStyles;

export default ClassicPage;