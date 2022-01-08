import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../components/ClassicPage/ClassicPage";
import styles from "../styles/Creations.module.scss";
import cx from "classnames";
import useLanguage from "../hooks/useLanguage";
import Ad from "../components/Ad/Ad";
import WithAppContext from "../components/WithAppContext/WithAppContext";
import useSmoothFetch, { Placeholder } from "../hooks/useSmoothFetch";
import Skeleton from "../components/Skeleton/Skeleton";
import { useEffect, useMemo, useRef, useState } from "react";
import { useRouter } from "next/router";
import TrackCreationCard, { TrackCreation } from "../components/TrackCreationCard/TrackCreationCard";
import { buildQuery } from "../helpers/uris";
import Link from "next/link";
import useCreations from "../hooks/useCreations";
import useFormSubmit, { doSubmit } from "../hooks/useFormSubmit";
import useEffectOnUpdate from "../hooks/useEffectUpdate";

const resPerPage = 60, resPerRow = 5;
const CreationsList: NextPage = () => {
  const language = useLanguage();
  const router = useRouter();
  const handleSearch = useFormSubmit();

  const { user, admin, tri, type, nom, auteur } = router.query;
  const { data: creator } = useSmoothFetch<{ name: string }>(`/api/user/${user}`, {
    disabled: !user
  });
  const nTri = +tri || 0;
  const sortTabs = useMemo(() => language ? ['By latest', 'Top rated', 'Trending'] : ['Les plus récents', 'Les mieux notés', 'Tendances'], [language]);
  const sortTabsData = useMemo(() => {
    return sortTabs.map((sortTab, i) => ({
      text: sortTab,
      url: "/creations?" + buildQuery({
        ...router.query,
        tri: i
      })
    }));
  }, [sortTabs, router.query]);
  const types = useMemo(() => language
    ? ['Complete mode  - multicups', 'Quick mode - multicups', 'Complete mode - cups', 'Quick mode - cups', 'Complete mode - circuits', 'Quick mode - circuits', 'Complete mode - arenas', 'Quick mode - arenas']
    : ['Mode complet  - multicoupes', 'Mode simplifié - multicoupes', 'Mode complet - coupes', 'Mode simplifié - coupes', 'Mode complet - circuits', 'Mode simplifié - circuits', 'Mode complet - arènes', 'Mode simplifié - arènes']
    , [language]);

  const [page, setPage] = useState(1);
  const creationParams = useMemo(() => {
    const params = {
      user, admin, tri, type, nom, auteur, page
    };
    return buildQuery(params);
  }, [router.query, page]);
  useEffectOnUpdate(() => {
    resetAbortController();
    setCreationsListHeights({
      current: 0,
      max: 0
    });
    setPage(1);
  }, [buildQuery(router.query)]);
  useEffectOnUpdate(() => {
    setQueryId(queryId + 1);
  }, [creationParams]);
  let [cardHeight, setCardHeight] = useState(0);
  let [chunkHeight, setChunkHeight] = useState(0);
  function getCardHeight() {
    try {
      if (window.matchMedia('(max-width: 800px)').matches) {
        return 126;
      }
    }
    catch (e) {
    }
    return 146;
  }
  useEffect(() => {
    setCardHeight(cardHeight = getCardHeight());
    setChunkHeight(chunkHeight = cardHeight * resPerPage / resPerRow);
  }, []);
  const [creationsListHeights, setCreationsListHeights] = useState({
    current: 0,
    max: 0
  });

  function createAbortController() {
    if (typeof AbortController !== "undefined")
      return new AbortController();
  }
  const controller = useRef(createAbortController());
  const [queryId, setQueryId] = useState(0);
  function resetAbortController() {
    controller.current?.abort();
    controller.current = createAbortController();
  }

  const { data: creationsPayload, loading: creationsLoading } = useSmoothFetch(`/api/getCreations.php?${creationParams}`, {
    placeholder: () => ({
      data: Placeholder.array(resPerPage, (id) => ({
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
    }),
    onSuccess: (payload) => {
      if (page === 1)
        setCreationsList(payload.data);
      else
        setCreationsList([...creationsList, ...payload.data]);
      setTimeout(() => {
        const $creationsList = document.getElementById(styles.creationsList);
        if ($creationsList) {
          const $creationsListWrapper = $creationsList.parentNode;
          if ($creationsListWrapper instanceof HTMLElement) {
            if (!$creationsListWrapper.style.width)
              $creationsListWrapper.style.width = $creationsListWrapper.offsetWidth + "px";
          }
          setCreationsListHeights({
            current: Math.min(creationsListHeights.current + chunkHeight, $creationsList.scrollHeight),
            max: $creationsList.scrollHeight
          });
          setCreationsRendering(false);
        }
      });
    },
    reloadDeps: [queryId],
    requestOptions: {
      signal: controller.current?.signal
    }
  });
  const [creationsList, setCreationsList] = useState<TrackCreation[]>(creationsPayload.data);
  const [creationCount, setCreationCount] = useState<{ total: number, byType: number[], isTotal: boolean }>();
  useEffect(() => {
    if (creationsLoading) return;
    setCreationCount({
      total: creationsPayload.count,
      byType: creationsPayload.countByType,
      isTotal: (creationsPayload.countByType.length > 1)
    })
  }, [creationsLoading, creationsPayload]);
  const [creationsRendering, setCreationsRendering] = useState(creationsLoading);
  useEffect(() => {
    if (creationsLoading)
      setCreationsRendering(true);
  }, [creationsLoading]);

  const { previewCreation } = useCreations();

  const creationsListStyle = useMemo(() => ({
    height: creationsListHeights.current,
    minHeight: creationsRendering ? chunkHeight : Math.min(chunkHeight, creationsListHeights.max),
  }), [creationsListHeights, chunkHeight, creationsRendering]);

  const lastPage = useMemo(() => (page * resPerPage >= creationCount?.total), [page, creationCount]);
  function loadMore() {
    const nextHeight = creationsListHeights.current + chunkHeight;
    if ((nextHeight > creationsListHeights.max) && !lastPage) {
      resetAbortController();
      setPage(page + 1);
    }
    else {
      setCreationsListHeights({
        ...creationsListHeights,
        current: Math.min(nextHeight, creationsListHeights.max)
      });
    }
  }
  function showLess() {
    setCreationsListHeights({
      ...creationsListHeights,
      current: creationsListHeights.current - chunkHeight
    });
  }
  function reduceAll() {
    setCreationsListHeights({
      ...creationsListHeights,
      current: chunkHeight
    });
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
      <form method="get" action="/creations" id={styles["form-search"]} onSubmit={handleSearch}>
        <div id={styles["sort-tabs"]}>
          {sortTabsData.map((sortTab, i) => {
            return (i === nTri) ? <span key={sortTab.text}>{sortTab.text}</span> : <Link key={sortTab.text} href={sortTab.url}>{sortTab.text}</Link>
          })}
        </div>
        <div><strong>{language ? 'Creation type' : 'Type de création '}</strong>{": "}
          <select name="type" defaultValue={type} onChange={(e) => doSubmit(router, e.target.form)}>
            <option value="">{language ? 'All creations' : 'Toutes les créations'}{" "}{creationCount?.isTotal && ' (' + creationCount.total + ')'}</option>
            {types.map((iType, i) => <option key={iType} value={i}>{iType}{creationCount?.isTotal && ' (' + creationCount.byType[i] + ')'}</option>)}
          </select></div>
        <div><strong>{language ? 'Search' : 'Recherche '}</strong>{": "}
          {admin && <input type="hidden" name="admin" value="1" />}
          <input type="hidden" name="user" defaultValue={user} />
          <input type="hidden" name="tri" id={styles.tri} defaultValue={tri} />
          <input type="text" name="nom" placeholder={language ? 'Name' : 'Nom'} defaultValue={nom} />{" "}
          <input type="text" name="auteur" placeholder={language ? 'Author' : 'Auteur'} defaultValue={auteur} />{" "}
          <input type="submit" value="Ok" className={commonStyles.action_button} />
        </div>
      </form>
      <div className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </div>
      <div id={styles.cTracks}>
        <div className={styles.liste} id={styles.liste} style={creationsListStyle}>
          <Skeleton loading={creationsLoading && page <= 1} id={styles.creationsList}>
            {creationsList.map((creation, i) => <TrackCreationCard key={`${i}-${creation.id}`} creation={creation} onPreview={previewCreation} />)}
          </Skeleton>
        </div>
      </div>
      <p className={cx(styles.subbuttons, { [styles.invisible]: creationsRendering })}>
        <input type="button" id={styles.defiler} className={cx(styles.defiler, commonStyles.action_button, { [styles.invisible]: ((creationsListHeights.current >= creationsListHeights.max) && lastPage) })} value={language ? 'More' : 'Plus'} onClick={() => loadMore()} />{"   "}
        <input type="button" id={styles.masquer} className={cx(styles.defiler, commonStyles.action_button, { [styles.invisible]: chunkHeight >= creationsListHeights.current })} value={language ? 'Less' : 'Moins'} onClick={() => showLess()} />{"   "}
        <input type="button" id={styles.reduire} className={cx(styles.defiler, commonStyles.action_button, { [styles.invisible]: chunkHeight >= creationsListHeights.current })} value={language ? 'Minimize' : 'Réduire'} onClick={() => reduceAll()} />
      </p>

      {!creationsLoading && !creationsList.length && <h4>
        {language ? 'No result for this search' : 'Aucun résultat pour cette recherche'}
      </h4>}

      <p>
        <a className={styles.retour} href="#null" onClick={scrollToTop}>{language ? 'Back to top' : 'Retour haut de page'}</a>{" - "}
        <Link href="/"><a className={styles.retour}>{language ? 'Back to Mario Kart PC' : 'Retour à Mario Kart PC'}</a></Link>
      </p>
    </ClassicPage>
  );
}

export default WithAppContext(CreationsList);