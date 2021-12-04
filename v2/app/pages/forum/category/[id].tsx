import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../../components/ClassicPage/ClassicPage";
import styles from "../../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage, { plural } from "../../../hooks/useLanguage";
import cx from "classnames";
import WithAppContext from "../../../components/WithAppContext/WithAppContext";
import ForumAccount from "../../../components/Forum/Account/Account";
import Ad from "../../../components/Ad/Ad";
import { formatDate } from "../../../helpers/dates";
import useFetch from "../../../hooks/useFetch";
import { useRouter } from "next/dist/client/router";

const ForumCategory: NextPage = () => {
  const language = useLanguage();
  const router = useRouter();
  const categoryID = +router.query.id;

  const { data: categoryPayload } = useFetch(`/api/forum/categories/${categoryID}`);

  // TODO make right API call
  const { data: topicsPayload } = useFetch(`/api/forum/topics`);


  return (
    <ClassicPage title="Forum Mario Kart PC" className={styles.Forum} page="forum">
      <h1>{categoryPayload?.name}</h1>
      <ForumAccount />
      <Ad width={728} height={90} bannerId="4919860724" />
      <p><a href="forum.php">{language ? 'Back to the forum' : 'Retour au forum'}</a></p>
      <p id={styles["category-description"]}>{categoryPayload?.description}</p>
      {/* TODO handle rights */}
      {!!categoryID && <p className={styles.forumButtons}>
        <a href={"newtopic.php?category=" + categoryID} className={commonStyles.action_button}>{language ? 'New topic' : 'Nouveau topic'}</a>
      </p>}
      <table id={styles.listeTopics}>
        <col />
        <col id={styles.authors} />
        <col id={styles.nbmsgs} />
        <col id={styles.lastmsgs} />
        <tr id={styles.titres}>
          <td>{language ? 'Subjects' : 'Sujets'}</td>
          <td>{language ? 'Author' : 'Auteur'}</td>
          <td className={styles["topic-nbmsgs"]}>{language ? 'Msgs nb' : 'Nb msgs'}</td>
          <td>{language ? 'Last message' : 'Dernier message'}</td>
        </tr>
        {topicsPayload?.data.map((topic, i) => (<tr className={(i % 2) ? styles.fonce : styles.clair}>
          <td className={styles.subjects}>
            <a href={"topic.php?topic=" + topic.id} className={styles.fulllink}>{topic.title}</a>
          </td>
          <td className={styles.authors}>
            {
              topic.firstMessage.author
                ? <a className={styles["forum-auteur"]} href={"profil.php?id=" + topic.firstMessage.author.id}>{topic.firstMessage.author.name}</a>
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
      </table>
      {/* TODO add pagination */}
      <p className={styles.forumButtons}>
        {/* TODO handle rights */}
        {!!categoryID && <a href={"newtopic.php?category=" + categoryID} className={cx(commonStyles.action_button, styles.action_button)}>{language ? 'New topic' : 'Nouveau topic'}</a>}
        <a href="forum.php">{language ? 'Back to the forum' : 'Retour au forum'}</a><br />
        <a href="index.php">{language ? 'Back to home' : 'Retour à l\'accueil'}</a>
      </p>
    </ClassicPage>
  );
}

export default WithAppContext(ForumCategory);