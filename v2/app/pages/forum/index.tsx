import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage, { plural } from "../../hooks/useLanguage";
import useUser from "../../hooks/useUser";
import useFetch from "../../hooks/useFetch";
import { useMemo } from "react";
import { formatDate, localeString } from "../../helpers/dates";
import Ad from "../../components/Ad/Ad";
import goldCupIcon from "../../images/icons/gold-cup.png"
import silverCupIcon from "../../images/icons/silver-cup.png"

const ForumCategories: NextPage = () => {
  const language = useLanguage();
  const user = useUser();

  const { data: categoriesPayload } = useFetch("/api/forum/categories");

  const { data: forumStats } = useFetch("/api/forum/stats");
  const beginMonthJsx = useMemo(() => {
    if (!forumStats?.monthActivePlayer?.beginMonth) return <></>;
    const beginMonth = new Date(forumStats.monthActivePlayer.beginMonth);
    const monthStr = beginMonth.toLocaleDateString(localeString(language), { month: "long" });
    return language ? <>{monthStr} 1<small className={styles.superscript}>st</small></> : <>1<small className={styles.superscript}>er</small> {monthStr}</>
  }, [forumStats, language])

  return (
    <ClassicPage className={styles.ForumCategories} page="forum">
      <h1>Forum Mario Kart PC</h1>
      {
        /* TODO handle rights msg */
        /* TODO handle account recovery */
        user
          ? <p id={styles.compte}><span>{user.name}</span>
            <a href={"profil.php?id=" + user.id}>{language ? 'My profile' : 'Mon profil'}</a><br />
            <a href="logout.php">{language ? 'Log out' : 'Déconnexion'}</a>
          </p>
          : <form method="post" action="forum.old.php">
            <table id={styles.connexion}>
              <caption>{language ? <>You aren't logged in.<br />Enter your login and password here :</> : <>Vous n'êtes pas connecté<br />Entrez votre pseudo et code ici :</>}</caption>
              <tbody>
                <tr>
                  <td className={styles.ligne}><label htmlFor="pseudo">{language ? 'Login' : 'Pseudo'} :</label></td>
                  <td><input type="text" name="pseudo" id={styles.pseudo} /></td>
                </tr>
                <tr>
                  <td className={styles.ligne}><label htmlFor="code">{language ? 'Password' : 'Code'} :</label></td>
                  <td><input type="password" name="code" id={styles.code} /></td>
                </tr>
                <tr>
                  <td colSpan={2}><input type="submit" value={language ? 'Submit' : 'Valider'} /></td>
                </tr>
                <tr><td colSpan={2}>
                  <a href="signup.php">{language ? 'Register' : 'Inscription'}</a>{" | "}
                  <a href="password-lost.php" style={{ fontWeight: "normal" }}>{language ? 'Forgot password' : 'Mot de passe perdu'}</a>
                </td></tr>
              </tbody>
            </table>
          </form>
      }
      <Ad width={728} height={90} bannerId="4919860724" />
      <form method="get" action="recherche.php" className={styles["forum-search"]}>
        <p>
          <label htmlFor="search-content">
            {language ? 'Search' : 'Recherche '}:{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={language ? 'Topic title' : 'Titre du topic'} name="content" />
          {" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
          <a href="forum-search.php">{language ? 'Advanced search' : 'Recherche avancée'}</a>
        </p>
      </form>
      <table id={styles.listeTopics}>
        <colgroup>
          <col id={styles.categories} />
          <col id={styles.nbmsgs} />
          <col id={styles.lastmsgs} />
        </colgroup>
        <tbody>
          <tr id={styles.titres}>
            <td>{language ? 'Category' : 'Catégorie'}</td>
            <td>{language ? 'Topics nb' : 'Nb topics'}</td>
            <td>{language ? 'Last message' : 'Dernier message'}</td>
          </tr>
          {
            categoriesPayload?.data.map((category, i) => <tr key={category.id} className={(i % 2) ? styles.fonce : styles.clair}>
              <td className={styles.subjects}>
                <a href={"category.php?category=" + category.id}>{category.name}</a>
                <div className={styles["category-description"]}>{category.description}</div>
              </td>
              <td>{category.nbTopics}</td>
              <td>{formatDate(category.lastTopic?.lastMessage.date, {
                language,
                mode: "datetime",
                prefix: true,
                case: "capitalize",
                includeYear: "always",
                includeSeconds: true
              })}</td>
            </tr>)
          }
        </tbody>
      </table>
      {forumStats && <ul className={styles.forumStats}>
        {
          language ? <>
            <li>The forum has a total of <strong>{plural("%n message%s", forumStats.nbMessages)}</strong> split into <strong>{forumStats.nbTopics} topics</strong> and posted by <strong>{plural("%n member%s", forumStats.nbMembers)}</strong>.</li>
            <li>The most active member is <a href={"profil.php?id=" + forumStats.mostActivePlayer.id}>{forumStats.mostActivePlayer.name}</a> with <strong>{plural("%n message%s", forumStats.mostActivePlayer.nbMessages)}</strong> posted in total.<a href="ranking-forum.php"><img src={goldCupIcon.src} alt="" />Ranking of most active members<img src={goldCupIcon.src} alt="" /></a></li>
            {forumStats.monthActivePlayer && <li>The most active member of the month is <a href={"profil.php?id=" + forumStats.monthActivePlayer.id}>{forumStats.monthActivePlayer.name}</a> with <strong>{plural("%n message%s", forumStats.monthActivePlayer.nbMessages)}</strong> since {beginMonthJsx}.<a href="ranking-forum.php?month=last"><img src={silverCupIcon.src} alt="" />Ranking of month's most active members<img src={silverCupIcon.src} alt="" /></a></li>}
          </> : <>
            <li>Le forum comptabilise <strong>{plural("%n message%s", forumStats.nbMessages)}</strong> répartis dans <strong>{forumStats.nbTopics} topics</strong> et postés par <strong>{plural("%n member%s", forumStats.nbMembers)}</strong>.</li>
            <li>Le membre le plus actif est <a href={"profil.php?id=" + forumStats.mostActivePlayer.id}>{forumStats.mostActivePlayer.name}</a> avec <strong>{plural("%n message%s", forumStats.mostActivePlayer.nbMessages)}</strong> postés au total.<a href="ranking-forum.php"><img src={goldCupIcon.src} alt="" />Classement des membres les plus actifs<img src={goldCupIcon.src} alt="" /></a></li>
            {forumStats.monthActivePlayer && <li>Le membre le plus actif du mois est <a href={"profil.php?id=" + forumStats.monthActivePlayer.id}>{forumStats.monthActivePlayer.name}</a> avec <strong>{plural("%n message%s", forumStats.monthActivePlayer.nbMessages)}</strong> depuis le {beginMonthJsx}.<a href="ranking-forum.php?month=last"><img src={silverCupIcon.src} alt="" />Classement des plus actifs du mois<img src={silverCupIcon.src} alt="" /></a></li>}
          </>
        }
      </ul>}
      <p className={styles.forumButtons}>
        <Link href="/">{language ? "Back to home" : "Retour à l'accueil"}</Link>
      </p>
    </ClassicPage>
  );
}

export default ForumCategories;