import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage, { plural } from "../../hooks/useLanguage";
import { useMemo } from "react";
import { formatDate, localeString } from "../../helpers/dates";
import Ad from "../../components/Ad/Ad";
import goldCupIcon from "../../images/icons/gold-cup.png"
import silverCupIcon from "../../images/icons/silver-cup.png"
import WithAppContext from "../../components/WithAppContext/WithAppContext";
import ForumAccount from "../../components/Forum/Account/Account";
import useSmoothFetch, { Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";

const ForumCategories: NextPage = () => {
  const language = useLanguage();

  const { data: categoriesPayload, loading: categoriesLoading } = useSmoothFetch("/api/forum/categories", {
    placeholder: () => ({
      data: Placeholder.array(5, (id) => ({
        id,
        name: Placeholder.text(25, 45),
        description: Placeholder.text(100, 300),
        nbTopics: Placeholder.number(100, 9999),
        lastTopic: {
          name: Placeholder.text(25, 45),
          lastMessage: {
            date: Placeholder.date()
          }
        }
      }))
    })
  });

  const { data: forumStats, loading: statsLoading } = useSmoothFetch("/api/forum/stats", {
    placeholder: () => ({
      nbTopics: Placeholder.number(1000, 9999),
      nbMessages: Placeholder.number(50000, 200000),
      nbMembers: Placeholder.number(1000, 9999),
      mostActivePlayer: {
        id: 1,
        name: Placeholder.text(8, 12),
        nbMessages: Placeholder.number(1000, 9999),
        beginMonth: Placeholder.date(),
      },
      monthActivePlayer: {
        id: 1,
        name: Placeholder.text(8, 12),
        beginMonth: Placeholder.date(),
        nbMessages: Placeholder.number(10, 500),
      }
    })
  });
  const beginMonthJsx = useMemo(() => {
    if (!forumStats?.monthActivePlayer?.beginMonth) return <></>;
    const beginMonth = new Date(forumStats.monthActivePlayer.beginMonth);
    const monthStr = beginMonth.toLocaleDateString(localeString(language), { month: "long" });
    return language ? <>{monthStr} 1<small className={styles.superscript}>st</small></> : <>1<small className={styles.superscript}>er</small> {monthStr}</>
  }, [forumStats, language])

  return (
    <ClassicPage title="Forum Mario Kart PC" className={styles.Forum} page="forum">
      <h1>Forum Mario Kart PC</h1>
      <ForumAccount />
      <p className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </p>
      <form method="get" action="recherche.php" className={styles["forum-search"]}>
        <p>
          <label htmlFor="search-content">
            {language ? 'Search' : 'Recherche '}:{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={language ? 'Topic title' : 'Titre du topic'} name="content" />
          {" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
          <a href="/forum-search.php">{language ? 'Advanced search' : 'Recherche avancée'}</a>
        </p>
      </form>
      <Skeleton loading={categoriesLoading}>
        <table id={styles.listeTopics}>
          <colgroup>
            <col id={styles.categories} />
            <col id={styles.nbmsgs} />
            <col id={styles.lastmsgs} />
          </colgroup>
          <thead>
            <tr id={styles.titres}>
              <td>{language ? 'Category' : 'Catégorie'}</td>
              <td>{language ? 'Topics nb' : 'Nb topics'}</td>
              <td>{language ? 'Last message' : 'Dernier message'}</td>
            </tr>
          </thead>
          <tbody>
            {
              categoriesPayload?.data.map((category, i) => <tr key={category.id} className={(i % 2) ? styles.fonce : styles.clair}>
                <td className={styles.subjects}>
                  <Link href={"forum/category/" + category.id}>{category.name}</Link>
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
      </Skeleton>
      <Skeleton loading={statsLoading}>
        {forumStats && <ul className={styles.forumStats}>
          {
            language ? <>
              <li>The forum has a total of <strong>{plural("%n message%s", forumStats.nbMessages)}</strong> split into <strong>{forumStats.nbTopics} topics</strong> and posted by <strong>{plural("%n member%s", forumStats.nbMembers)}</strong>.</li>
              <li>The most active member is <a href={"/profil.php?id=" + forumStats.mostActivePlayer.id}>{forumStats.mostActivePlayer.name}</a> with <strong>{plural("%n message%s", forumStats.mostActivePlayer.nbMessages)}</strong> posted in total.<a href="/ranking-forum.php"><img src={goldCupIcon.src} alt="" />Ranking of most active members<img src={goldCupIcon.src} alt="" /></a></li>
              {forumStats.monthActivePlayer && <li>The most active member of the month is <a href={"/profil.php?id=" + forumStats.monthActivePlayer.id}>{forumStats.monthActivePlayer.name}</a> with <strong>{plural("%n message%s", forumStats.monthActivePlayer.nbMessages)}</strong> since {beginMonthJsx}.<a href="/ranking-forum.php?month=last"><img src={silverCupIcon.src} alt="" />Ranking of month's most active members<img src={silverCupIcon.src} alt="" /></a></li>}
            </> : <>
              <li>Le forum comptabilise <strong>{plural("%n message%s", forumStats.nbMessages)}</strong> répartis dans <strong>{forumStats.nbTopics} topics</strong> et postés par <strong>{plural("%n member%s", forumStats.nbMembers)}</strong>.</li>
              <li>Le membre le plus actif est <a href={"/profil.php?id=" + forumStats.mostActivePlayer.id}>{forumStats.mostActivePlayer.name}</a> avec <strong>{plural("%n message%s", forumStats.mostActivePlayer.nbMessages)}</strong> postés au total.<a href="/ranking-forum.php"><img src={goldCupIcon.src} alt="" />Classement des membres les plus actifs<img src={goldCupIcon.src} alt="" /></a></li>
              {forumStats.monthActivePlayer && <li>Le membre le plus actif du mois est <a href={"/profil.php?id=" + forumStats.monthActivePlayer.id}>{forumStats.monthActivePlayer.name}</a> avec <strong>{plural("%n message%s", forumStats.monthActivePlayer.nbMessages)}</strong> depuis le {beginMonthJsx}.<a href="/ranking-forum.php?month=last"><img src={silverCupIcon.src} alt="" />Classement des plus actifs du mois<img src={silverCupIcon.src} alt="" /></a></li>}
            </>
          }
        </ul>}
      </Skeleton>
      <p className={styles.forumButtons}>
        <Link href="/">{language ? "Back to home" : "Retour à l'accueil"}</Link>
      </p>
    </ClassicPage>
  );
}

export default WithAppContext(ForumCategories);