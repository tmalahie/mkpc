import { NextPage } from "next";
import ClassicPage from "../../components/ClassicPage/ClassicPage";
import styles from "../../helpers/globalStyles";
import cx from "classnames";
import Link from "next/link"
import useLanguage from "../../hooks/useLanguage";
import { useTranslation } from "next-i18next";
import Ad from "../../components/Ad/Ad";
import Head from 'next/head';
import withServerSideProps from "../../components/WithAppContext/withServerSideProps";
import useSmoothFetch, { Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import { usePaging } from "../../hooks/usePaging";
import Pager from "../../components/Pager/Pager";
import { ChangeEvent, useCallback, useMemo, useState } from "react";
import { useRouter } from "next/router";
import useChallengeDifficulties from "../../hooks/useChallengeDifficulties";
import Rating from "../../components/Rating/Rating";
import RatingControl from "../../components/RatingControl/RatingControl";
import useCreations from "../../hooks/useCreations";
import { buildQuery } from "../../helpers/uris";
import useFormSubmit from "../../hooks/useFormSubmit";

const localesNs = ["challenges", "common"];
const ChallengesList: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const { moderate, remoderate, rate, ordering } = router.query;
  const rateChallenges = rate;
  const title = useMemo(() => {
    if (moderate != null)
      return t("Challenges_pending_moderation");
    if (ordering === "rating")
      return t("Top_rated_challenges");
    if (remoderate != null)
      return t("Undo_a_challenge_validation");
    return t("Last_published_challenges");
    // TODO title for author
  }, [language, moderate, remoderate, ordering]);

  const { paging, currentPage, setCurrentPage } = usePaging();

  const creationParams = useMemo(() => buildQuery(router.query), [router.query]);

  const { data: challengesPayload, loading: challengesLoading } = useSmoothFetch(`/api/getChallenges.php?${creationParams}`, {
    placeholder: () => ({
      data: Placeholder.array(20, (id) => challengePlaceholder(id)),
      count: 0
    }),
    reloadDeps: [creationParams]
  });

  useCreations();

  const challengeAction = useMemo(() => {
    if (rateChallenges)
      return "rate";
    if (moderate != null)
      return "moderate";
    if (remoderate != null)
      return "remoderate";
    return null;
  }, [rateChallenges, moderate, remoderate]);

  return (
    <ClassicPage title={title} className={styles.Challenges} page="game">
      <Head>
        <link rel="stylesheet" type="text/css" href="/styles/challenge-creations.css" />
      </Head>
      <div className={styles["challenges-list-ctn"]}>
        <h1>{title}</h1>
        {(moderate != null) && <ValidationTips />}
        {(remoderate != null) && <ReValidationTips />}
        {!challengeAction && <div className={styles["challenges-list-sublinks"]}>
          <img src="images/cups/cup2.png" alt="Cup" /> <a href="challengeRanking.php">{t("Challenges_leaderboard")}</a>  
          <img src="images/ministar0.png" alt="Star" className={styles.icStar} /> <Link href="/challenges?rate">{t("Rate_challenges")}</Link>
        </div>}
        {!challengeAction && <ChallengesListSearch />}
        <Ad width={728} height={90} bannerId="4919860724" />
        <Skeleton loading={challengesLoading}>
          <div className={styles["challenges-list"]}>
            {challengesPayload.data.map((challenge) => <ChallengeItem key={challenge.id} challenge={challenge} challengeAction={challengeAction} />)}
          </div>
          <div className={styles.challengePages}>
            <p>
              <Pager page={currentPage} paging={paging} count={challengesPayload.count} onSetPage={setCurrentPage} />
            </p>
          </div>
        </Skeleton>
        {!challengesLoading && !challengesPayload.data.length && <h2><em>{t("No_challenge_for_this")}</em></h2>}
        <p>
          {rateChallenges && <>
            <Link href="/challenges">{t("Back_to_challenges_list")}</Link>
            <br />
          </>}
          {(moderate != null) && <>
            <Link href="/challenges?remoderate">{t("Undo_a_challenge_validation_mistake")}</Link>
            <br />
          </>}
          {(remoderate != null) && <>
            <Link href="/challenges?moderate">{t("Back_to_challenges_list")}</Link>
            <br />
          </>}
          <Link href="/">{t("common:Back_to_mario_kart_pc")}</Link>
        </p>
      </div>
    </ClassicPage>
  );
}

function ValidationTips() {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);

  const [showValidationTips, setShowValidationTips] = useState(false);
  function toggleValidationTips(e) {
    e.preventDefault();
    setShowValidationTips(!showValidationTips);
  }

  if (language) {
    return <div className={styles["validation-hints-ctn"]}>
      Welcome to the challenge moderation page. Before you begin, please read the <a href="#null" onClick={toggleValidationTips}>validation tips</a>.
      {showValidationTips && <div id={styles["validation-hints"]}>
        For each challenge, you have 3 options:
        <ul>
          <li>Accept challenge, by clicking on <button className={styles["challenges-item-accept"]}>✓</button></li>
          <li>Reject challenge, by clicking on <button className={styles["challenges-item-reject"]}>×</button></li>
          <li>Accept challenge, but change difficulty level</li>
        </ul>
        Here are the reasons why you would reject a challenge:
        <ul>
          <li>Challenge pointless or with no difficulty ("Complete track" without constraint, on an easy track)</li>
          <li>Spam (12 times the same challenge, or simillar challenges posted by the same person)</li>
          <li>Obvious constraint missing ("CPUs in difficult mode"). In the case, precise it on the rejection message.</li>
          <li>Challenge name with insults or inappropriate words.</li>
        </ul>
        You can also change the difficulty if you find it unsuitable for the challenge. Try to make it consistent with the reference scale:
        <ul>
          <li>A challenge <span className={styles["challenges-item-difficulty-0"]}>easy</span> has to be feasible for a beginner ("Complete Mario Circuit 1 in Time Trial in less than 55s")</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-1"]}>medium</span> is typically difficult for a beginner but easy for an experimented player ("Complete Mario Circuit 1 in TT in less than 45s")</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-2"]}>difficult</span> would be difficult for an experimented player but completable in several trials ("Complete Mario Circuit 1 in TT in less than 39s")</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-3"]}>extreme</span> will require to try-hard even for an experimented player ("Complete Mario Circuit 1 in TT in less than 38s")</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-4"]}>impossible</span> will require to try-hard and may typically take several hours (or even days) before succeeding ("Complete Mario Circuit 1 in TT in less than 37s")</li>
        </ul>
      </div>}
    </div>;
  }
  else {
    return <p>
      Bienvenue dans la page de modération des défis. Avant de commencer, merci de lire les <a href="javascript:document.getElementById('validation-hints').style.display=document.getElementById('validation-hints').style.display?'':'block';void(0)">conseils de validation</a>.
      <div id={styles["validation-hints"]}>
        Pour chaque défi, vous avez 3 possibilités :
        <ul>
          <li>Accepter le défi, en cliquant sur <button className={styles["challenges-item-accept"]}>✓</button></li>
          <li>Refuser le défi, en cliquant sur <button className={styles["challenges-item-reject"]}>×</button></li>
          <li>Accepter le défi, mais modifier le niveau de difficulté</li>
        </ul>
        Voici les raisons pour lesquelles vous pouvez refuser un défi :
        <ul>
          <li>Défi sans intérêt ou avec aucune difficulté ("Finir le circuit" sans contraintes, sur un circuit facile)</li>
          <li>Spam (12 fois le même défi, ou des défis simillaires publiées par la même personne)</li>
          <li>Contrainte évidente manquante ("Ordis en mode difficile"). Dans ce cas, précisez-le dans le message de refus.</li>
          <li>Nom de défi avec des insultes ou des mots obscènes</li>
        </ul>
        Vous pouvez également modifier la difficulté si vous la jugez inadaptée au défi. Essayez de vous confortez à cette échelle de référence :
        <ul>
          <li>Un défi <span className={styles["challenges-item-difficulty-0"]}>facile</span> doit être faisable par un débutant ("Finir le Circuit Mario 1 en Contre-La-Montre en moins de 55s")</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-1"]}>moyen</span> est typiquement difficile pour un débutant mais facile pour un joueur expérimenté ("Finir le Circuit Mario 1 en CLM en moins de 45s")</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-2"]}>difficile</span> sera difficile pour un joueur expérimenté mais réussissable en plusieurs essais ("Finir le Circuit Mario 1 en CLM en moins de 39s")</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-3"]}>extrême</span> nécessitera de try-harder même pour un joueur expérimenté ("Finir le Circuit Mario 1 en CLM en moins de 38s")</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-4"]}>impossible</span> nécessite de try-harder et peut typiquement prendre plusieurs heures (voire jours) avant de réussir ("Finir le Circuit Mario 1 en CLM en moins de 37s")</li>
        </ul>
      </div>
    </p>
  }
}
function ReValidationTips() {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);

  return <>{t("A_challenge_you_accepted")}</>
}
function ChallengesListSearch() {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const handleSearch = useFormSubmit();
  const { ordering, author, winner, difficulty, hide_succeeded } = router.query;
  const challengeDifficulties = useChallengeDifficulties();

  return <form method="get" className={styles["challenges-list-search"]} action="/challenges" onSubmit={handleSearch}>
    <p>
      <label>{t("Filter_")}{" "}
        {author && <input type="hidden" name="author" defaultValue={author} />}
        {winner && <input type="hidden" name="winner" defaultValue={winner} />}
        <select name="difficulty" defaultValue={difficulty}>
          <option value="">{t("Difficulty")}</option>
          {challengeDifficulties?.map((challengeDifficulty) => <option key={challengeDifficulty.level} value={challengeDifficulty.level}>{challengeDifficulty.name}</option>)}
        </select></label>
       
      <label><input type="checkbox" name="hide_succeeded" defaultChecked={!!hide_succeeded} />{t("Hide_succeeded_challenges")}</label>
    </p>
    <p>
      <label>{t("Show_first_")}
        {" "}
        <select name="ordering" defaultValue={ordering}>
          <option value="latest">{t("Most_recent_challenges")}</option>
          <option value="rating">{t("Top_rated_challenges")}</option>
        </select></label>
       <input type="submit" value="Ok" />
    </p>
  </form>
}

function challengePlaceholder(id: number) {
  return {
    id,
    name: Placeholder.text(25, 45),
    difficulty: {
      name: Placeholder.text(5, 8),
      level: Placeholder.rand(0, 5)
    },
    description: {
      main: Placeholder.text(100, 120),
      extra: Placeholder.text(25, 45)
    },
    circuit: {
      name: Placeholder.text(15, 25),
      author: Placeholder.text(8, 12),
      href: "",
      isCup: false,
      cicon: "",
      icons: [],
    },
    rating: {
      avg: 0,
      nb: 0
    },
    status: "accepted",
    succeeded: null
  }
}
type Challenge = ReturnType<typeof challengePlaceholder>;
type ChallengeItemProps = {
  challenge: Challenge;
  challengeAction: string;
}
function ChallengeItem({ challenge, challengeAction }: ChallengeItemProps) {
  const router = useRouter();
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const challengeDifficulties = useChallengeDifficulties();
  const { moderate, remoderate, rate, ordering } = router.query;
  const rateChallenges = rate;
  const isChallengeAction = rateChallenges || (moderate != null) || (remoderate != null);

  const [challengeThanks, setChallengeThanks] = useState(false);

  const rateChallenge = useCallback((rating) => {
    window["o_xhr"]("challengeRate.php", "challenge=" + challenge.id + "&rating=" + rating, function (reponse) {
      if (reponse == 1) {
        setChallengeThanks(true);
        return true;
      }
      return false;
    });
  }, [challenge]);
  const [edittingDifficulty, setEdittingDifficulty] = useState(false);
  const [selectedLevel, setSelectedLevel] = useState(challenge.difficulty.level);
  const selectedDifficulty = useMemo(() => {
    const res = challengeDifficulties?.find((difficulty) => difficulty.level === selectedLevel);
    return res ?? challenge.difficulty;
  }, [selectedLevel, challengeDifficulties]);

  const updateChallengeDifficulty = useCallback((e: ChangeEvent<HTMLSelectElement>) => {
    setSelectedLevel(+e.target.value);
    setEdittingDifficulty(false);
  }, []);

  const acceptChallenge = useCallback(() => {
    const lastDifficulty = challenge.difficulty.level;
    const newDifficulty = selectedLevel;
    const difficultyChanged = (lastDifficulty != newDifficulty);
    if (difficultyChanged) {
      window["o_prompt"](language
        ? "Please confirm challenge <strong>approval</strong>.<br />Optionnal: explain why you changed challenge difficulty:"
        : "Veuillez confirmer la <strong>validation</strong> du défi.<br />Facultatif : expliquez le changement de difficulté :",
        "",
        function (msg) {
          var data = { "challenge": challenge.id, "accept": 1, "difficulty": newDifficulty };
          if (msg) data["msg"] = msg;
          challengeModerate(data);
        }
      );
    }
    else {
      window["o_confirm"](language
        ? "Please confirm challenge <strong>approval</strong>"
        : "Veuillez confirmer la <strong>validation</strong> du défi",
        function (ok) {
          if (ok) {
            var data = { "challenge": challenge.id, "accept": 1 };
            challengeModerate(data);
          }
        }
      );
    }
  }, [challenge, selectedLevel, language]);

  const rejectChallenge = useCallback(() => {
    window["o_prompt"](language
      ? "Please confirm challenge <strong>rejection</strong>.<br />Optionnal: explain why you rejected challenge:"
      : "Veuillez confirmer la <strong>non-validation</strong> du défi.<br />Facultatif : donnez les raisons du refus :",
      "",
      function (msg) {
        var data = { "challenge": challenge.id, "accept": 0 };
        if (msg) data["msg"] = msg;
        challengeModerate(data);
      }
    );
  }, [challenge, language]);

  const remoderateChallenge = useCallback(() => {
    window["o_confirm"](t("Put_this_challenge_back"), function (valided) {
      if (valided) {
        var data = { "challenge": challenge.id };
        challengeModerate(data, "challengeRemoderate.php");
      }
    });
  }, [challenge, language]);
  function challengeModerate(data, url = "challengeModerate.php") {
    var rawdata = "";
    for (var key in data) {
      if (rawdata) rawdata += "&";
      rawdata += key + "=" + encodeURIComponent(data[key]);
    }
    window["o_xhr"](url, rawdata, function (res) {
      return (res == 1);
    });
    var id = data.challenge;
    var $challenge = document.getElementById("challenges-item-" + id);
    function fadeOut($elt) {
      var opacity = 1;
      function fadeOutAux() {
        opacity -= 0.1;
        if (opacity <= 0)
          $elt.parentNode.removeChild($elt);
        else {
          $elt.style.opacity = opacity;
          setTimeout(fadeOutAux, 40);
        }
      }
      fadeOutAux();
    }
    fadeOut($challenge);
  }

  const editDifficulty = useCallback(() => {
    setEdittingDifficulty(true);
  }, []);
  const uneditDifficulty = useCallback(() => {
    setEdittingDifficulty(false);
  }, []);

  return <a className={cx(styles["challenges-list-item"], {
    [styles["challenges-list-item-rate"]]: rateChallenges,
    [styles["challenges-list-item-moderate"]]: (moderate != null) || (remoderate != null),
    [styles["list-item-success"]]: challenge.succeeded
  })} id={styles[`challenges-item-${challenge.id}`]} href={`challengeTry.php?challenge=${challenge.id}`}>
    <div className={cx(styles["challenges-item-circuit"], styles.creation_icon, challenge.circuit.isCup ? styles.creation_cup : styles.single_creation)}
      style={{ backgroundImage: challenge.circuit.icons ? challenge.circuit.icons.map(src => `url('images/creation_icons/${src}')`).join(",") : undefined }}
      data-cicon={challenge.circuit.icons ? undefined : challenge.circuit.cicon}>
      {challenge.succeeded && <div className={styles["challenges-item-success"]}>✔</div>}
    </div>
    <div className={styles["challenges-item-description"]}>
      <div>
        {challenge.name && <h2>{challenge.name}</h2>}
        {challenge.circuit.name && <h3><strong>{challenge.circuit.name}</strong> : {challenge.description.main}</h3>}
        {challenge.description.extra && <h4>{challenge.description.extra}</h4>}
      </div>
    </div>
    <div className={cx(styles["challenges-item-action"], {
      [styles["challenges-item-action-rate"]]: isChallengeAction,
    })}
      onClick={(e) => {
        if (isChallengeAction)
          e.preventDefault();
      }}
    >
      {
        (challengeAction === 'rate') && <>
          <div className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${challenge.difficulty.level}`])}>
            <img src={`images/challenges/difficulty${challenge.difficulty.level}.png`} alt={challenge.difficulty.name} />
            {" "}{challenge.difficulty.name}
            {challengeThanks && <span className={styles["challenges-item-rating-thanks"]}>{t("Thanks")}</span>}
          </div>
          <RatingControl defaultValue={challenge.rating.avg} onChange={(value) => {
            rateChallenge(value);
          }} />
          {challenge.circuit.author && <div className={styles["challenges-item-author"]}>
            {t("By")} <strong>{challenge.circuit.author}</strong>
          </div>}
        </>}
      {
        (challengeAction === "moderate") && <>
          <div className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${selectedLevel}`], {
              [styles["challenges-item-editting"]]: edittingDifficulty
            })}>
            <div className={styles["challenges-item-difficulty-value"]}>
              <img src={`images/challenges/difficulty${selectedLevel}.png`} alt={selectedDifficulty.name} />
              {" "}{selectedDifficulty.name}{" "}
              <span className={styles["challenge-item-link"]} onClick={editDifficulty}>{t("Edit")}</span>
            </div>
            <div className={cx(styles["challenges-item-difficulty-edit"])}>
              <select className={styles["challenges-item-difficulty-select"]} value={selectedLevel} onChange={updateChallengeDifficulty}>
                {challengeDifficulties?.map((challengeDifficulty, i) => <option key={i} value={i}>{challengeDifficulty.name}</option>)}
              </select>
              <span className={styles["challenge-item-link"]} onClick={uneditDifficulty}>{t("Undo")}</span>
            </div>
          </div>
          {challenge.circuit.author && <div className={styles["challenges-item-author"]}>
            {t("By")} <strong>{challenge.circuit.author}</strong>
          </div>}
          <div className={styles["challenges-item-moderation"]}>
            <button className={styles["challenges-item-accept"]} onClick={acceptChallenge}>✓</button>
            {" "}
            <button className={styles["challenges-item-reject"]} onClick={rejectChallenge}>×</button>
          </div>
        </>
      }
      {
        (challengeAction === "remoderate") && <>
          <div className={styles["challenge-item-remoderate"]}>
            {challenge.status === 'active' ? <>
              <span className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${selectedLevel}`])}>
                <img src={`images/challenges/difficulty${selectedLevel}.png`} alt={selectedDifficulty.name} />
                {" "}{selectedDifficulty.name}
              </span><br />
              <span className={styles["challenges-item-accepted"]}>
                {t("Accepted")}
              </span>
            </> : <>
              <span className={styles["challenges-item-rejected"]}>
                {t("Rejected")}
              </span>
            </>}
            <br />
            <span className={styles["challenge-item-link"]} onClick={remoderateChallenge}>
              {t("Undo")}
            </span>
          </div>
        </>
      }
      {
        (challengeAction === null) && <>
          <div className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${challenge.difficulty.level}`])}>
            <img src={`images/challenges/difficulty${challenge.difficulty.level}.png`} alt={challenge.difficulty.name} />
            {" "}{challenge.difficulty.name}
          </div>
          <div className={styles["challenges-item-rating"]}>
            <Rating rating={challenge.rating.avg} nbRatings={challenge.rating.nb} />
          </div>
          {challenge.circuit.author && <div className={styles["challenges-item-author"]}>
            {t("By")} <strong>{challenge.circuit.author}</strong>
          </div>}
        </>
      }
    </div>
  </a>
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default ChallengesList;