import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage from "../../hooks/useLanguage";
import { useTranslation } from "next-i18next";
import withServerSideProps from "../../components/WithAppContext/withServerSideProps";
import Ad from "../../components/Ad/Ad";
import { formatDate } from "../../helpers/dates";
import { useRouter } from "next/dist/client/router";
import useSmoothFetch, { postData, Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import { usePaging } from "../../hooks/usePaging";
import Pager from "../../components/Pager/Pager";
import { useEffect, useRef } from "react";
import useFormSubmit from "../../hooks/useFormSubmit";

const localesNs = ["forum"];
const ForumSearch: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const handleSearch = useFormSubmit();
  const searchInput = useRef<HTMLInputElement>(null);
  const content = router.query.content;
  const { paging, currentPage, setCurrentPage } = usePaging(50);

  const { data: topicsPayload, loading: topicsLoading } = useSmoothFetch("/api/forum/topics/find", {
    placeholder: () => ({
      data: Placeholder.array(20, (id) => ({
        id,
        title: Placeholder.text(25, 45),
        nbMessages: Placeholder.number(100, 9999),
        firstMessage: {
          author: {
            id: 1,
            name: Placeholder.text(8, 12),
          },
          date: Placeholder.date(),
        },
        lastMessage: {
          date: Placeholder.date(),
        }
      })),
      count: 0
    }),
    requestOptions: postData({
      filters: content ? [{
        key: "title",
        operator: "%",
        value: content
      }] : [],
      sort: {
        key: "lastMessageDate",
        order: "desc"
      },
      paging
    }),
    reloadDeps: [paging, content]
  });

  useEffect(() => {
    searchInput.current.value = content.toString();
  }, [content]);

  return (
    <ClassicPage title="Forum Mario Kart PC" className={styles.Forum} page="forum">
      <h1>Forum Mario Kart PC</h1>
      <p className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </p>
      <form method="get" action="/forum/search" className={styles["forum-search"]} onSubmit={handleSearch}>
        <p>
          <label htmlFor="search-content">
            {t("Search_")}{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={t("Topic_title")} name="content" defaultValue={content} ref={searchInput} />
          {" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
          <a href="/forum-search.php">{t("Advanced_search")}</a>
        </p>
      </form>
      <p><Link href="/forum">{t("Back_to_the_forum")}</Link></p>
      <Skeleton loading={topicsLoading}>
        <table id={styles.listeTopics}>
          <colgroup>
            <col />
            <col id={styles.authors} />
            <col id={styles.nbmsgs} />
            <col id={styles.lastmsgs} />
          </colgroup>
          <thead>
            <tr id={styles.titres}>
              <td>{t("Subjects")}</td>
              <td>{t("Author")}</td>
              <td className={styles["topic-nbmsgs"]}>{t("Msgs_nb")}</td>
              <td>{t("Last_message")}</td>
            </tr>
          </thead>
          <tbody>
            {topicsPayload?.data.map((topic, i) => (<tr key={topic.id} className={(i % 2) ? styles.fonce : styles.clair}>
              <td className={styles.subjects}>
                <a href={"/topic.php?topic=" + topic.id} className={styles.fulllink}>{topic.title}</a>
              </td>
              <td className={styles.authors}>
                {
                  topic.firstMessage.author
                    ? <a className={styles["forum-auteur"]} href={"/profil.php?id=" + topic.firstMessage.author.id}>{topic.firstMessage.author.name}</a>
                    : <em>{t("Deleted_account")}</em>
                }
              </td>
              <td className={styles["topic-nbmsgs"]}>{topic.nbMessages}</td>
              <td className={styles.lastmsgs}>
                {formatDate(topic.lastMessage.date, {
                  language,
                  mode: "datetime",
                  prefix: true,
                  case: "capitalize",
                  includeYear: "always"
                })}
              </td>
            </tr>))}
          </tbody>
        </table>
        <div className={styles.topicPages}>
          <p>
            <Pager page={currentPage} paging={paging} count={topicsPayload.count} onSetPage={setCurrentPage} />
          </p>
        </div>
      </Skeleton>
      <div className={styles.forumButtons}>
        <Link href="/forum">{t("Back_to_the_forum")}</Link><br />
        <Link href="/">{t("Back_to_home")}</Link>
      </div>
    </ClassicPage>
  );
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default ForumSearch;