import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../../components/ClassicPage/ClassicPage";
import cx from "classnames";
import styles from "../../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage from "../../../hooks/useLanguage";
import WithAppContext from "../../../components/WithAppContext/WithAppContext";
import ForumAccount from "../../../components/Forum/Account/Account";
import Ad from "../../../components/Ad/Ad";
import { formatDate } from "../../../helpers/dates";
import { useRouter } from "next/dist/client/router";
import useSmoothFetch, { postData, Placeholder } from "../../../hooks/useSmoothFetch";
import Skeleton from "../../../components/Skeleton/Skeleton";
import { usePaging } from "../../../hooks/usePaging";
import Pager from "../../../components/Pager/Pager";
import useAuthUser from "../../../hooks/useAuthUser";
import { useMemo } from "react";

const ForumCategory: NextPage = () => {
  const language = useLanguage();
  const router = useRouter();
  const user = useAuthUser();
  const categoryID = +router.query.id;

  const { data: categoryPayload, loading: catsLoading } = useSmoothFetch(`/api/forum/categories/${categoryID}`, {
    placeholder: () => ({
      id: categoryID,
      name: Placeholder.text(25, 45),
      description: Placeholder.text(200, 400),
      adminOnly: true
    })
  });

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
      filters: [{
        key: "category",
        type: "=",
        value: categoryID
      }],
      sort: {
        key: "lastMessageDate",
        order: "desc"
      },
      paging
    }),
    reloadDeps: [paging]
  });

  const canPostTopic = useMemo(() => {
    return user && !user.banned && (!categoryPayload.adminOnly || user.roles.manager);
  }, [user, categoryPayload]);

  return (
    <ClassicPage title="Forum Mario Kart PC" className={styles.Forum} page="forum">
      <Skeleton loading={catsLoading}>
        <h1>{categoryPayload.name}</h1>
      </Skeleton>
      <ForumAccount />
      <Ad width={728} height={90} bannerId="4919860724" />
      <p><Link href="/forum">{language ? 'Back to the forum' : 'Retour au forum'}</Link></p>
      <Skeleton loading={catsLoading}>
        <p id={styles["category-description"]}>{categoryPayload.description}</p>
      </Skeleton>
      {canPostTopic && <p className={styles.forumButtons}>
        <a href={"/newtopic.php?category=" + categoryID} className={cx(styles.action_button, commonStyles.action_button)}>{language ? 'New topic' : 'Nouveau topic'}</a>
      </p>}
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
        {canPostTopic && <p><a href={"/newtopic.php?category=" + categoryID} className={cx(styles.action_button, commonStyles.action_button)}>{language ? 'New topic' : 'Nouveau topic'}</a></p>}
        <Link href="/forum">{language ? 'Back to the forum' : 'Retour au forum'}</Link><br />
        <Link href="/">{language ? 'Back to home' : 'Retour à l\'accueil'}</Link>
      </div>
    </ClassicPage>
  );
}

export default WithAppContext(ForumCategory);