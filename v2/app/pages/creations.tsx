import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../components/ClassicPage/ClassicPage";
import styles from "../styles/Creations.module.scss";
import cx from "classnames";
import useLanguage from "../hooks/useLanguage";
import Ad from "../components/Ad/Ad";
import WithAppContext from "../components/WithAppContext/WithAppContext";
import useSmoothFetch, { Placeholder } from "../hooks/useSmoothFetch";
import Skeleton from "../components/Skeleton/Skeleton";
import { useMemo } from "react";
import { useRouter } from "next/router";
import TrackCreationCard from "../components/TrackCreationCard/TrackCreationCard";
import { buildQuery } from "../helpers/uris";
import Link from "next/link";
import useCreations from "../hooks/useCreations";

const CreationsList: NextPage = () => {
  const language = useLanguage();
  const router = useRouter();
  const { user, admin, tri, type, nom, auteur } = router.query;
  const { data: creator } = useSmoothFetch<{ name: string }>(`/api/user/${user}`, {
    disabled: !user
  });
  const nTri = +tri || 0;
  const singleType = (type !== '');
  const sortTabs = language ? ['By latest', 'Top rated', 'Trending'] : ['Les plus récents', 'Les mieux notés', 'Tendances'];
  const types = language
    ? ['Complete mode  - multicups', 'Quick mode - multicups', 'Complete mode - cups', 'Quick mode - cups', 'Complete mode - circuits', 'Quick mode - circuits', 'Complete mode - arenas', 'Quick mode - arenas']
    : ['Mode complet  - multicoupes', 'Mode simplifié - multicoupes', 'Mode complet - coupes', 'Mode simplifié - coupes', 'Mode complet - circuits', 'Mode simplifié - circuits', 'Mode complet - arènes', 'Mode simplifié - arènes']
  const creationParams = useMemo(() => {
    const params = {
      user, admin, tri, type, nom, auteur
    };
    return buildQuery(params);
  }, [router.query]);
  const { data: creationsPayload, loading: creationsLoading } = useSmoothFetch(`/api/getCreations.php?${creationParams}`, {
    placeholder: () => ({
      data: Placeholder.array(60, (id) => ({
        id,
        author: "",
        cicon: "",
        icons: [],
        href: "",
        isCup: false,
        name: Placeholder.text(15, 35),
        nbComments: Placeholder.number(1, 100),
        publicationDate: Placeholder.timestamp(),
        rating: 0,
        nbRatings: 0
      })),
      count: 0,
      countByType: []
    })
  });
  const creationCount = useMemo(() => {
    if (creationsLoading) return;
    return {
      total: creationsPayload.count,
      byType: creationsPayload.countByType
    }
  }, [creationsPayload, creationsLoading]);

  const { previewCreation } = useCreations();

  function defile() {
  }
  function masque() {
  }
  function reduceAll() {
  }
  function handleTabSelect(e) {
    e.preventDefault();
  }
  function scrollToTop(e) {
    e.preventDefault();
    window.scrollTo(0, 0);
  }

  return (
    <ClassicPage title={(language ? 'All shared circuits' : 'Tous les circuits partagés') + " - Mario Kart PC"} className={styles.Creations} page="game">
      <Skeleton loading={user && !creator}>
        <h1>{creator
          ? (language ? 'Creations list of ' + creator.name : 'Liste des créations de ' + creator.name)
          : (language ? 'Creations list of Mario Kart PC' : 'Liste des créations Mario Kart PC')}</h1>
      </Skeleton>
      <p>{!user && (
        language ? <>Welcome to the list of circuits and courses shared by the Mario Kart PC community !<br />
          You too, share your circuit creations by clicking on "Share circuit" at the bottom-left of the circuit page.</>
          : <>Bienvenue dans la liste des circuits et arènes partagés par la communauté de Mario Kart PC !<br />
            Vous aussi, partagez les circuits que vous créez en cliquant sur "Partager le circuit" en bas à gauche de la page du circuit.</>
      )}</p>
      <form method="get" action="/creations" id={styles["form-search"]}>
        <div id={styles["sort-tabs"]}>
          {sortTabs.map((sortTab, i) => {
            return (i === nTri) ? <span key={sortTab}>{sortTab}</span> : <a key={sortTab} href="?" onClick={handleTabSelect}>{sortTab}</a>
          })}
        </div>
        <div><strong>{language ? 'Creation type' : 'Type de création '}</strong>{": "}
          <select name="type" defaultValue={type} onChange={(e) => e.target.form.submit()}>
            <option value="">{language ? 'All creations' : 'Toutes les créations'}{" "}{creationCount && ' (' + creationCount.total + ')'}</option>
            {types.map((iType, i) => <option key={iType} value={i}>{iType}{creationCount && ' (' + creationCount.byType[i] + ')'}</option>)}
          </select></div>
        <div><strong>{language ? 'Search' : 'Recherche '}</strong>{": "}
          {admin && <input type="hidden" name="admin" value="1" />}
          <input type="hidden" name="user" defaultValue={user} />
          <input type="hidden" name="tri" id={styles.tri} defaultValue={tri} />
          <input type="text" name="nom" placeholder={language ? 'Name' : 'Nom'} defaultValue={nom} />{" "}
          <input type="text" name="auteur" placeholder={language ? 'Author' : 'Auteur'} value={auteur} />{" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
        </div>
      </form>
      <div className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </div>
      <div id={styles.cTracks}>
        <Skeleton loading={creationsLoading} className={styles.liste} id={styles.liste}>
          {creationsPayload.data.map((creation) => <TrackCreationCard key={creation.id} creation={creation} onPreview={previewCreation} />)}
        </Skeleton>
      </div>
      <p className={styles.subbuttons}>
        <input type="button" id={styles.defiler} className={cx(styles.defiler, commonStyles.action_button)} value={language ? 'More' : 'Plus'} onClick={() => defile()} />{"   "}
        <input type="button" id={styles.masquer} className={cx(styles.defiler, commonStyles.action_button)} value={language ? 'Less' : 'Moins'} style={{ visibility: "hidden" }} onClick={() => masque()} />{"   "}
        <input type="button" id={styles.reduire} className={cx(styles.defiler, commonStyles.action_button)} value={language ? 'Minimize' : 'Réduire'} style={{ visibility: "hidden" }} onClick={() => reduceAll()} />
      </p>

      <p>
        <a className={styles.retour} href="#null" onClick={scrollToTop}>{language ? 'Back to top' : 'Retour haut de page'}</a>{" - "}
        <Link href="/"><a className={styles.retour}>{language ? 'Back to Mario Kart PC' : 'Retour à Mario Kart PC'}</a></Link>
      </p>
    </ClassicPage >
  );
}

export default WithAppContext(CreationsList);