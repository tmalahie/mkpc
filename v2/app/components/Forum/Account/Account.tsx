import styles from "./ForumAccount.module.scss";
import useUser from "../../../hooks/useUser";
import useLanguage from "../../../hooks/useLanguage";

function ForumAccount() {
  const language = useLanguage();
  const user = useUser();

  return (
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
  );
}

export default ForumAccount;