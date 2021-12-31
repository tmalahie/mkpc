import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage from "../../hooks/useLanguage";
import WithAppContext from "../../components/WithAppContext/WithAppContext";
import Ad from "../../components/Ad/Ad";
import { formatDate } from "../../helpers/dates";
import { useRouter } from "next/dist/client/router";
import useSmoothFetch, { postData, Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import { usePaging } from "../../hooks/usePaging";
import Pager from "../../components/Pager/Pager";
import { FormEvent, useEffect, useRef } from "react";
import { useFormSubmit } from "../../hooks/useFormSubmit";

const ForumSearch: NextPage = () => {
  const language = useLanguage();
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
            {language ? 'Search' : 'Recherche '}:{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={language ? 'Topic title' : 'Titre du topic'} name="content" defaultValue={content} ref={searchInput} />
          {" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
          <a href="/forum-search.php">{language ? 'Advanced search' : 'Recherche avancée'}</a>
        </p>
      </form>
      <p><Link href="/forum">{language ? 'Back to the forum' : 'Retour au forum'}</Link></p>
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
              <td>{language ? 'Subjects' : 'Sujets'}</td>
              <td>{language ? 'Author' : 'Auteur'}</td>
              <td className={styles["topic-nbmsgs"]}>{language ? 'Msgs nb' : 'Nb msgs'}</td>
              <td>{language ? 'Last message' : 'Dernier message'}</td>
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
                    : <em>{language ? "Deleted account" : "Compte supprimé"}</em>
                }
              </td>
              <td className={styles["topic-nbmsgs"]}>{topic.nbMessages}</td>
              <td className={styles.lastmsgs}>
                {formatDate(topic.lastMessage.date, {
                  language,
                  mode: "datetime",
                  prefix: true,
                  case: "capitalize",
                  includeYear: "always",
                  includeSeconds: true
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
        <Link href="/forum">{language ? 'Back to the forum' : 'Retour au forum'}</Link><br />
        <Link href="/">{language ? 'Back to home' : 'Retour à l\'accueil'}</Link>
      </div>
    </ClassicPage>
  );
}

export default WithAppContext(ForumSearch);