import ClassicPage from "../../../components/ClassicPage/ClassicPage";
import useLanguage, { plural } from "../../../hooks/useLanguage";
import "../Forum.css"
import useUser from "../../../hooks/useUser";
import Ad from "../../../components/Ad/Ad";
import { formatDate } from "../../../helpers/dates";
import goldCupIcon from "../../../images/icons/gold-cup.png"
import silverCupIcon from "../../../images/icons/silver-cup.png"
import useFetch from "../../../hooks/useFetch";

function ForumCategories() {
  const language = useLanguage();
  const user = useUser();

  const { data: categoriesPayload } = useFetch("api/forum/categories");

  const forumStats = {
    nbMessages: 100000,
    nbTopics: 5000,
    nbMembers: 1000,
    mostActivePlayer: {
      id: 1,
      name: "Wargor",
      nbMessages: 3000
    },
    monthActivePlayer: {
      id: 11,
      name: "Timmy",
      nbMessages: 200,
      since: "November 1st"
    }
  }

  return (
    <ClassicPage page="forum">
      <h1>Forum Mario Kart PC</h1>
      {
        /* TODO handle rights msg */
        /* TODO handle account recovery */
        user
          ? <p id="compte"><span>{user.name}</span>
          <a href={"profil.php?id="+user.id}>{ language ? 'My profile':'Mon profil' }</a><br />
          <a href="logout.php">{ language ? 'Log out':'Déconnexion' }</a>
        </p>
          : <form method="post" action="forum.old.php">
            <table id="connexion">
              <caption>{language ? <>You aren't logged in.<br />Enter your login and password here :</> : <>Vous n'êtes pas connecté<br />Entrez votre pseudo et code ici :</>}</caption>
              <tbody>
                <tr>
                  <td className="ligne"><label htmlFor="pseudo">{ language ? 'Login':'Pseudo' } :</label></td>
                  <td><input type="text" name="pseudo" id="pseudo" /></td>
                </tr>
                <tr>
                  <td className="ligne"><label htmlFor="code">{ language ? 'Password':'Code' } :</label></td>
                  <td><input type="password" name="code" id="code" /></td>
                </tr>
                <tr>
                  <td colSpan={2}><input type="submit" value={ language ? 'Submit':'Valider' } /></td>
                </tr>
                <tr><td colSpan={2}>
                  <a href="signup.php">{ language ? 'Register':'Inscription' }</a>{" | "}
                  <a href="password-lost.php" style={{fontWeight: "normal"}}>{ language ? 'Forgot password':'Mot de passe perdu' }</a>
                </td></tr>
              </tbody>
            </table>
          </form>
      }
      <Ad width={728} height={90} bannerId="4919860724" />
      <form method="get" action="recherche.php" className="forum-search">
        <p>
          <label htmlFor="search-content">
            {language ? 'Search':'Recherche ' }:{" "}
          </label>
          <input type="text" id="search-content" placeholder={ language ? 'Topic title':'Titre du topic' } name="content" />
          {" "}
          <input type="submit" value="Ok" className="action_button" />
          <a href="forum-search.php">{ language ? 'Advanced search':'Recherche avancée' }</a>
        </p>
      </form>
      <table id="listeTopics">
      <col id="categories" />
      <col id="nbmsgs" />
      <col id="lastmsgs" />
      <tr id="titres">
      <td>{ language ? 'Category':'Catégorie' }</td>
      <td>{ language ? 'Topics nb':'Nb topics'}</td>
      <td>{ language ? 'Last message':'Dernier message' }</td>
      </tr>
      {
        categoriesPayload?.data.map((category,i) => <tr className={(i%2) ? 'fonce':'clair'}>
          <td className="subjects">
            <a href={"category.php?category="+ category.id}>{ category.name }</a>
            <div className="category-description">{category.description}</div>
          </td>
          <td>{ category.nbTopics }</td>
          <td>{ formatDate(category.lastTopic?.lastMessage.date, {
            mode: "datetime",
            prefix: true,
            case: "capitalize",
            includeYear: "always",
            includeSeconds: true
          }) }</td>
        </tr>)
      }
      </table>
      {forumStats && <ul className="forumStats">
        {
          language ? <>
            <li>The forum has a total of <strong>{ plural("%n message%s", forumStats.nbMessages) }</strong> split into <strong>{forumStats.nbTopics} topics</strong> and posted by <strong>{ plural("%n member%s", forumStats.nbMembers) }</strong>.</li>
            <li>The most active member is <a href={"profil.php?id="+ forumStats.mostActivePlayer.id}>{ forumStats.mostActivePlayer.name }</a> with <strong>{ plural("%n message%s", forumStats.mostActivePlayer.nbMessages) }</strong> posted in total.<a href="ranking-forum.php"><img src={goldCupIcon} alt="" />Ranking of most active members<img src={goldCupIcon} alt="" /></a></li>
            {forumStats.monthActivePlayer && <li>The most active member of the month is <a href={"profil.php?id="+ forumStats.monthActivePlayer.id}>{ forumStats.monthActivePlayer.name }</a> with <strong>{ plural("%n message%s", forumStats.monthActivePlayer.nbMessages) }</strong> since {forumStats.monthActivePlayer.since }.<a href="ranking-forum.php?month=last"><img src={silverCupIcon} alt="" />Ranking of month's most active members<img src={silverCupIcon} alt="" /></a></li>}
          </> : <>
            <li>Le forum comptabilise <strong>{ plural("%n message%s", forumStats.nbMessages) }</strong> répartis dans <strong>{forumStats.nbTopics} topics</strong> et postés par <strong>{ plural("%n member%s", forumStats.nbMembers) }</strong>.</li>
            <li>Le membre le plus actif est <a href={"profil.php?id="+ forumStats.mostActivePlayer.id}>{ forumStats.mostActivePlayer.name }</a> avec <strong>{ plural("%n message%s", forumStats.mostActivePlayer.nbMessages) }</strong> postés au total.<a href="ranking-forum.php"><img src={goldCupIcon} alt="" />Classement des membres les plus actifs<img src={goldCupIcon} alt="" /></a></li>
            {forumStats.monthActivePlayer && <li>Le membre le plus actif du mois est <a href={"profil.php?id="+ forumStats.monthActivePlayer.id}>{ forumStats.monthActivePlayer.name }</a> avec <strong>{ plural("%n message%s", forumStats.monthActivePlayer.nbMessages) }</strong> depuis le {forumStats.monthActivePlayer.since }.<a href="ranking-forum.php?month=last"><img src={silverCupIcon} alt="" />Classement des plus actifs du mois<img src={silverCupIcon} alt="" /></a></li>}
          </>
        }
      </ul>}
      <p className="forumButtons">
      <a href="index.php">{ language ? "Back to home":"Retour à l'accueil" }</a>
      </p>
    </ClassicPage>
  );
}

export default ForumCategories;