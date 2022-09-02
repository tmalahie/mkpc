import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../helpers/globalStyles";
import Link from "next/link"
import useLanguage, { plural } from "../../hooks/useLanguage";
import { useMemo } from "react";
import { formatDate, localeString } from "../../helpers/dates";
import Ad from "../../components/Ad/Ad";
import Head from 'next/head';
import goldCupIcon from "../../images/icons/gold-cup.png"
import silverCupIcon from "../../images/icons/silver-cup.png"
import withServerSideProps from "../../components/WithAppContext/withServerSideProps";
import ForumAccount from "../../components/Forum/Account/Account";
import useSmoothFetch, { Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import useFormSubmit from "../../hooks/useFormSubmit";
import { useTranslation } from "next-i18next";

const localesNs = ["forum"];
const ForumCategories: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const handleSearch = useFormSubmit();

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
      <Head>
        <link rel="stylesheet" type="text/css" href="/styles/forum.css" />
      </Head>
      <h1>Forum Mario Kart PC</h1>
      <ForumAccount />
      <p className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </p>
      <form method="get" action="/forum/search" className={styles["forum-search"]} onSubmit={handleSearch}>
        <p>
          <label htmlFor="search-content">
            {t("Search_")}{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={t("Topic_title")} name="content" />
          {" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
          <a href="/forum-search.php">{t("Advanced_search")}</a>
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
              <td>{t("Category")}</td>
              <td>{t("Topics_nb")}</td>
              <td>{t("Last_message")}</td>
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
                  includeYear: "always"
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
        <Link href="/">{t("Back_to_home")}</Link>
      </p>
    </ClassicPage>
  );
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default ForumCategories;