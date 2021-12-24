import styles from "./ForumAccount.module.scss";
import forumStyles from "../../../styles/Forum.module.scss";
import useAuthUser from "../../../hooks/useAuthUser";
import useLanguage from "../../../hooks/useLanguage";
import { useMemo } from "react";
import useSmoothFetch from "../../../hooks/useSmoothFetch";
import Skeleton from "../../Skeleton/Skeleton";

function ForumAccount() {
  const language = useLanguage();
  const user = useAuthUser();

  const adminRole = useMemo(() => {
    if (!user) return;
    if (user.roles.manager) {
      if (user.roles.admin)
        return language ? "administrator" : "adminisrateur";
      if (user.roles.moderator)
        return language ? "moderator" : "modérateur";
      return language ? "event host" : "animateur";
    }
  }, [language, user?.roles]);

  return <>
    {
      /* TODO handle account recovery */
      user
        ? <p id={styles.compte}><span>{user.name}</span>
          <a href={"/profil.php?id=" + user.id}>{language ? 'My profile' : 'Mon profil'}</a><br />
          <a href="/logout.php">{language ? 'Log out' : 'Déconnexion'}</a>
        </p>
        : <form method="post" action="/forum.php">
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
                <a href="/signup.php">{language ? 'Register' : 'Inscription'}</a>{" | "}
                <a href="/password-lost.php" style={{ fontWeight: "normal" }}>{language ? 'Forgot password' : 'Mot de passe perdu'}</a>
              </td></tr>
            </tbody>
          </table>
        </form>
    }
    {
      adminRole && (language ? <div className={forumStyles.success}>
        You are now {adminRole}! <a href="/admin.php">Click here</a> to go to the admin page.
      </div> : <div className={forumStyles.success}>
        Vous êtes maintenant {adminRole} ! <a href="/admin.php">Cliquez ici</a> pour vous rendre sur la page admin.
      </div>)
    }
    {
      user.banned && <BanMessage />
    }
  </>;
}
function BanMessage() {
  const language = useLanguage();
  const { data, loading } = useSmoothFetch<{ message: string }>("/api/user/me/banData");

  if (data?.message) {
    return language ? <p className={forumStyles.warning}>
      You have been banned for the following reason:<br />
      <strong className={styles.banMessage}>{data.message}</strong><br />
      Therefore, you can not post messages until further notice.
    </p> : <p className={forumStyles.warning}>
      Vous avez été banni pour la raison suivante :<br />
      <strong className={styles.banMessage}>{data.message}</strong><br />
      Par conséquent, vous ne pourrez plus poster de message jusqu'à nouvel ordre.
    </p>
  }
  else {
    return <Skeleton loading={loading}>{language ? <p className={forumStyles.warning}>
      You have been banned temporarily because of inappropriate behavior.<br />
      Therefore, you can not post messages until further notice.<br />
      Be careful : in case of recurrence, your account will be deleted.
    </p> : <p className={forumStyles.warning}>
      Vous avez été banni temporairement suite à un comportement innaproprié sur le site.<br />
      Par conséquent, vous ne pouvez plus poster de message jusqu'à nouvel ordre.<br />
      Attention : en cas de récidive, votre compte sera supprimé définitivement.
    </p>}</Skeleton>;
  }
}

export default ForumAccount;