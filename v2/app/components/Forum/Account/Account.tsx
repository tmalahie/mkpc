import cx from "classnames";
import styles from "./ForumAccount.module.scss";
import forumStyles from "../../../helpers/globalStyles";
import useAuthUser from "../../../hooks/useAuthUser";
import useLanguage from "../../../hooks/useLanguage";
import { FormEvent, MouseEvent, useMemo, useState } from "react";
import useSmoothFetch, { postData } from "../../../hooks/useSmoothFetch";
import Skeleton from "../../Skeleton/Skeleton";
import { useRouter } from "next/router";

function ForumAccount() {
  const language = useLanguage();
  const user = useAuthUser();
  const router = useRouter();

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

  const [loginMessage, setLoginMessage] = useState(language ? <>You aren't logged in.<br />Enter your login and password here :</> : <>Vous n'êtes pas connecté<br />Entrez votre pseudo et code ici :</>);
  const [loginMessageStyle, setLoginMessageStyle] = useState("");
  const [accountDeleted, setAccountDeleted] = useState(false);

  function handleLogin(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    loginUser(undefined)
  }
  function loginUser(params) {
    const $form = document.forms["login"];
    const $submit = $form.querySelector('input[type="submit"]');
    if ($submit instanceof HTMLInputElement)
      $submit.disabled = true;
    setLoginMessage(<>...</>);
    setLoginMessageStyle("");
    return fetch("/api/login.php", postData({
      name: $form.elements["pseudo"].value,
      password: $form.elements["code"].value,
      ...params
    }))
      .then(res => res.json())
      .then(data => {
        if (!data.error) {
          sessionStorage.removeItem("authUser");
          router.reload(); // TODO reload component data in a less brutal way :)
          return;
        }
        switch (data.error) {
          case "wrong_credentials":
            setLoginMessage(<>{language ? 'Incorrect login or password' : 'Pseudo ou mot de passe incorrect'}</>);
            setLoginMessageStyle(styles.loginError);
            break;
          case "account_deleted":
            setAccountDeleted(true);
            break;
        }
      }).finally(() => {
        if ($submit instanceof HTMLInputElement)
          $submit.disabled = false;
      });
  }

  function handleLogout(e: MouseEvent) {
    e.preventDefault();
    fetch("/api/logout.php")
      .then(() => {
        sessionStorage.removeItem("authUser");
        router.reload(); // TODO same comment
      });
  }

  function handleRestoreAccount(e: MouseEvent) {
    e.preventDefault();
    loginUser({
      restoreDeleted: true
    });
  }

  return <>
    {
      accountDeleted && (language ? <p className={forumStyles.warning}>
        This account has been deleted. The connection to it has been disabled.<br />
        If you want to undo and restore it, you can still do it by clicking <a href="#null" onClick={handleRestoreAccount}>here</a>.
      </p> : <p className={forumStyles.warning}>
        Ce compte a été supprimé. La connexion a celui-ci a donc été desactivée.<br />
        Si vous souhaitez revenir en arrière et le restaurer, vous pouvez toujours le faire en cliquant <a href="#null" onClick={handleRestoreAccount}>ici</a>.
      </p>)
    }
    {
      user
        ? <Skeleton loading={user?.id === 0}>
          <p id={styles.compte}><span>{user.name}</span>
            <a href={"/profil.php?id=" + user.id}>{language ? 'My profile' : 'Mon profil'}</a><br />
            <a href="/logout.php" onClick={handleLogout}>{language ? 'Log out' : 'Déconnexion'}</a>
          </p>
        </Skeleton>
        : <form method="post" name="login" action="/forum" className={cx({ [styles.hidden]: accountDeleted })} onSubmit={handleLogin}>
          <table id={styles.connexion}>
            <caption className={loginMessageStyle}>{loginMessage}</caption>
            <tbody>
              <tr>
                <td className={forumStyles.ligne}><label htmlFor="pseudo">{language ? 'Login' : 'Pseudo'} :</label></td>
                <td><input type="text" name="pseudo" id={styles.pseudo} /></td>
              </tr>
              <tr>
                <td className={forumStyles.ligne}><label htmlFor="code">{language ? 'Password' : 'Code'} :</label></td>
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
      user?.banned && <BanMessage />
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
      Vous avez été banni pour la raison suivante :<br />
      <strong className={styles.banMessage}>{data.message}</strong><br />
      Par conséquent, vous ne pourrez plus poster de message jusqu'à nouvel ordre.
    </p>
  }
  else {
    return <Skeleton loading={loading}>{language ? <p className={forumStyles.warning}>
      You have been banned temporarily because of inappropriate behavior.<br />
      Therefore, you can not post messages until further notice.<br />
      Be careful : in case of recurrence, your account will be deleted.
    </p> : <p className={forumStyles.warning}>
      Vous avez été banni temporairement suite à un comportement innaproprié sur le site.<br />
      Par conséquent, vous ne pouvez plus poster de message jusqu'à nouvel ordre.<br />
      Attention : en cas de récidive, votre compte sera supprimé définitivement.
    </p>}</Skeleton>;
  }
}

export default ForumAccount;