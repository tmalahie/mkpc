import useLanguage from "../../hooks/useLanguage";
import useUserId from "../../hooks/useUserId";
import "./ClassicPage.css"
import english from "../../images/icons/english.png";
import french from "../../images/icons/french.png";
import notifSettings from "../../images/icons/notif-settings.png";
import banner from "../../images/main/header/banner.png";
import headerLeft from "../../images/main/header/ic_left.png";
import headerRight from "../../images/main/header/ic_right.png";
import footerLeft from "../../images/main/footer/ic_left.png";
import footerRight from "../../images/main/footer/ic_right.png";

function Flag({ nLanguage, src, alt, page, homepage = false }) {
  let url;
  let language = useLanguage();
  if (homepage) {
    url = nLanguage ? 'en.php' : 'fr.php';
    alt = nLanguage ? 'Home - Mario Kart PC' : 'Accueil - Mario Kart PC';
  }
  else
    url = 'changeLanguage.php?nLanguage=' + nLanguage + '&amp;page=' + page;
  const chosen = (nLanguage === language);
  function handleClick() {
    if (chosen)
      return false;
  }
  return <a href={url} title={homepage ? alt : ''} onClick={handleClick}><img id={chosen ? 'chosen' : 'toChoose'} src={src} alt={alt} /></a>;
}

function ClassicPage(props) {
  const nbNotifs = 0; // TODO handle notifs
  const language = useLanguage();
  const id = useUserId();
  const page = props.page;

  function closeNotifs() {
    // TODO
  }

  return (
    <div className="ClassicPage">
      <header role="banner">
        <table>
          <tbody><tr>
            <td id="header_left">
              <img src={headerLeft} alt="/|" />
            </td>
            <td id="header_center">
              <img src={banner} alt="Mario Kart PC" />
            </td>
            <td id="header_right">
              <img src={headerRight} alt="|\" />
            </td>
          </tr>
          </tbody>
        </table>
      </header>
      <nav>
        <div id="menu_left">
          <Flag nLanguage={1} src={english} alt="English" page={props.page} />
          <Flag nLanguage={0} src={french} alt="Français" page={props.page} />
          {/* TODO handle homepage */}
        </div>
        <div id="menu_right">
          <div id="notifs-bubble" className={nbNotifs ? 'notifs' : 'no-notifs'}>
            <div id="notifs-nb-alert">
              {nbNotifs}
            </div>
            <div className="notifs-container">
              {nbNotifs ? <div id="nb-notifs">
                <strong>{nbNotifs}</strong> notification{(nbNotifs > 1 ? 's' : '')}
                {id && <a href="notif-settings.php"><img src={notifSettings} alt="Settings" title={language ? 'Notification settings' : 'Paramètres de notifications'} /></a>}
              </div> : <div id="no-notif">
                {language ? 'No notifications' : 'Aucune notification'}
                {id && <a href="notif-settings.php"><img src={notifSettings} alt="Settings" title={language ? 'Notification settings' : 'Paramètres de notifications'} /></a>}
              </div>}
              <div id="notifs-list">
                {/* TODO handle notifs */}
              </div>
              <div id="notifs-options">
                <input type="button" value={language ? 'Mark everything as read' : 'Tout marquer comme lu'} onClick={closeNotifs} />
              </div>
            </div>
          </div>
        </div>
        <div id="menu_center" role="menubar">
          <a href="index.php" id={(page === 'home') ? "thispage" : ""} role="menuitem">{language ? 'Home' : 'Accueil'}</a>{" "}
          <a href="mariokart.php" id={(page === 'game') ? "thispage" : ""} role="menuitem">{language ? 'Play game' : 'Le jeu'}</a>{" "}
          <a href="forum.php" id={(page === 'forum') ? "thispage" : ""} role="menuitem">Forum</a>
        </div>
      </nav>
      <main>{props.children}</main>
      <footer>
        <div id="relief_bottom">
          <img src={footerLeft} id="relief_left" alt="<" />
          <img src={footerRight} id="relief_right" alt=">" />
        </div>
        <div id="footer_bottom">
          <div id="footer_left"></div>
          <div id="footer_right"></div>
          <table id="footer_center"><tbody><tr><td>Mario Kart PC © 2010-{new Date().getFullYear()}<span id="developer"> | Designed and developed by Wargor</span></td></tr></tbody></table>
        </div>
      </footer>
    </div>
  );
}

export default ClassicPage;