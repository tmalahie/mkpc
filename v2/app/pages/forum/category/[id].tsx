import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../../components/ClassicPage/ClassicPage";
import styles from "../../../styles/Forum.module.scss";
import Link from "next/link"
import useLanguage, { plural } from "../../../hooks/useLanguage";
import useUser from "../../../hooks/useUser";
import WithAppContext from "../../../components/WithAppContext/WithAppContext";
import ForumAccount from "../../../components/Forum/Account/Account";
import Ad from "../../../components/Ad/Ad";

const ForumCategory: NextPage = () => {
  const language = useLanguage();

  const category = {
    id: 1,
    name: "Topics officiels",
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."
  }

  const categoryID = category.id;

  return (
    <ClassicPage title="Forum Mario Kart PC" className={styles.Forum} page="forum">
      <h1>{category.name}</h1>
      <ForumAccount />
      <Ad width={728} height={90} bannerId="4919860724" />
      <p><a href="forum.php">{language ? 'Back to the forum' : 'Retour au forum'}</a></p>
      <p id="category-description">{category.description}</p>
      {/* TODO handle rights */}
      {categoryID && <p className="forumButtons"><a href={"newtopic.php?category=" + categoryID} className={commonStyles.action_button}>{language ? 'New topic' : 'Nouveau topic'}</a></p>}
    </ClassicPage>
  );
}

export default WithAppContext(ForumCategory);