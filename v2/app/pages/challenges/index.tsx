import { NextPage } from "next";
import ClassicPage from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Challenges.module.scss";
import cx from "classnames";
import Link from "next/link"
import useLanguage from "../../hooks/useLanguage";
import Ad from "../../components/Ad/Ad";
import WithAppContext from "../../components/WithAppContext/WithAppContext";
import useSmoothFetch, { Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import { usePaging } from "../../hooks/usePaging";
import Pager from "../../components/Pager/Pager";
import { useCallback, useMemo, useState } from "react";
import { useRouter } from "next/router";
import useChallengeDifficulties from "../../hooks/useChallengeDifficulties";
import Rating from "../../components/Rating/Rating";
import RatingControl from "../../components/RatingControl/RatingControl";
import useCreations from "../../hooks/useCreations";
import { buildQuery } from "../../helpers/uris";
import useFormSubmit from "../../hooks/useFormSubmit";

const ChallengesList: NextPage = () => {
  const language = useLanguage();
  const router = useRouter();
  const { moderate, remoderate, rate, ordering } = router.query;
  const rateChallenges = rate;
  const title = useMemo(() => {
    if (moderate != null)
      return language ? 'Challenges pending moderation' : 'Défis en attente de validation';
    if (ordering === "rating")
      return language ? 'Top rated challenges' : 'Défis les mieux notés';
    if (remoderate != null)
      return language ? 'Undo a challenge validation' : 'Annuler la validation d\'un défi';
    return language ? 'Last published challenges' : 'Derniers défis publiés';
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

  return (
    <ClassicPage title={title} className={styles.Challenges} page="game">
      <div className={styles["challenges-list-ctn"]}>
        <h1>{title}</h1>
        {moderate && <ValidationTips />}
        {remoderate && <ReValidationTips />}
        {!rateChallenges && <div className={styles["challenges-list-sublinks"]}>
          <img src="images/cups/cup2.png" alt="Cup" /> <a href="challengeRanking.php">{language ? 'Challenges leaderboard' : 'Classement des défis'}</a> &nbsp;
          <img src="images/ministar0.png" alt="Star" className={styles.icStar} /> <Link href="/challenges?rate">{language ? 'Rate challenges' : 'Noter les défis'}</Link>
        </div>}
        {!rateChallenges && <ChallengesListSearch />}
        <Ad width={728} height={90} bannerId="4919860724" />
        <Skeleton loading={challengesLoading}>
          <div className={styles["challenges-list"]}>
            {challengesPayload.data.map((challenge) => <ChallengeItem key={challenge.id} challenge={challenge} />)}
          </div>
          <div className={styles.challengePages}>
            <p>
              <Pager page={currentPage} paging={paging} count={challengesPayload.count} onSetPage={setCurrentPage} />
            </p>
          </div>
        </Skeleton>
        {!challengesLoading && !challengesPayload.data.length && <h2><em>{language ? 'No challenge for this search' : 'Aucun défi trouvé'}</em></h2>}
        <p>
          {rateChallenges && <>
            <Link href="/challenges">{language ? 'Back to challenges list' : 'Retour à la liste des défis'}</Link>
            <br />
          </>}
          {(moderate != null) && <>
            <Link href="/challenges?remoderate">{language ? 'Undo a challenge validation mistake' : 'Annuler une erreur de validation'}</Link>
            <br />
          </>}
          {(remoderate != null) && <>
            <Link href="/challenges?moderate">{language ? 'Back to challenges list' : 'Retour à la liste des défis'}</Link>
            <br />
          </>}
          <Link href="/">{language ? 'Back to the Mario Kart PC' : 'Retour à Mario Kart PC'}</Link>
        </p>
      </div>
    </ClassicPage>
  );
}

function ValidationTips() {
  const language = useLanguage();
  if (language) {
    return <p>
      Welcome to the challenge moderation page. Before you begin, please read the <a href="javascript:document.getElementById('validation-hints').style.display=document.getElementById('validation-hints').style.display?'':'block';void(0)">validation tips</a>.
      <div id={styles["validation-hints"]}>
        For each challenge, you have 3 options:
        <ul>
          <li>Accept challenge, by clicking on <button className={styles["challenges-item-accept"]}>&check;</button></li>
          <li>Reject challenge, by clicking on <button className={styles["challenges-item-reject"]}>&times;</button></li>
          <li>Accept challenge, but change difficulty level</li>
        </ul>
        Here are the reasons why you would reject a challenge:
        <ul>
          <li>Challenge pointless or with no difficulty (&quot;Complete track&quot; without constraint, on an easy track)</li>
          <li>Spam (12 times the same challenge, or simillar challenges posted by the same person)</li>
          <li>Obvious constraint missing (&quot;CPUs in difficult mode&quot;). In the case, precise it on the rejection message.</li>
          <li>Challenge name with insults or inappropriate words.</li>
        </ul>
        You can also change the difficulty if you find it unsuitable for the challenge. Try to make it consistent with the reference scale:
        <ul>
          <li>A challenge <span className={styles["challenges-item-difficulty-0"]}>easy</span> has to be feasible for a beginner (&quot;Complete Mario Circuit 1 in Time Trial in less than 55s&quot;)</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-1"]}>medium</span> is typically difficult for a beginner but easy for an experimented player (&quot;Complete Mario Circuit 1 in TT in less than 45s&quot;)</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-2"]}>difficult</span> would be difficult for an experimented player but completable in several trials (&quot;Complete Mario Circuit 1 in TT in less than 39s&quot;)</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-3"]}>extreme</span> will require to try-hard even for an experimented player (&quot;Complete Mario Circuit 1 in TT in less than 38s&quot;)</li>
          <li>A challenge <span className={styles["challenges-item-difficulty-4"]}>impossible</span> will require to try-hard and may typically take several hours (or even days) before succeeding (&quot;Complete Mario Circuit 1 in TT in less than 37s&quot;)</li>
        </ul>
      </div>
    </p>;
  }
  else {
    return <p>
      Bienvenue dans la page de modération des défis. Avant de commencer, merci de lire les <a href="javascript:document.getElementById('validation-hints').style.display=document.getElementById('validation-hints').style.display?'':'block';void(0)">conseils de validation</a>.
      <div id={styles["validation-hints"]}>
        Pour chaque défi, vous avez 3 possibilités :
        <ul>
          <li>Accepter le défi, en cliquant sur <button className={styles["challenges-item-accept"]}>&check;</button></li>
          <li>Refuser le défi, en cliquant sur <button className={styles["challenges-item-reject"]}>&times;</button></li>
          <li>Accepter le défi, mais modifier le niveau de difficulté</li>
        </ul>
        Voici les raisons pour lesquelles vous pouvez refuser un défi&nbsp;:
        <ul>
          <li>Défi sans intérêt ou avec aucune difficulté (&quot;Finir le circuit&quot; sans contraintes, sur un circuit facile)</li>
          <li>Spam (12 fois le même défi, ou des défis simillaires publiées par la même personne)</li>
          <li>Contrainte évidente manquante (&quot;Ordis en mode difficile&quot;). Dans ce cas, précisez-le dans le message de refus.</li>
          <li>Nom de défi avec des insultes ou des mots obscènes</li>
        </ul>
        Vous pouvez également modifier la difficulté si vous la jugez inadaptée au défi. Essayez de vous confortez à cette échelle de référence&nbsp;:
        <ul>
          <li>Un défi <span className={styles["challenges-item-difficulty-0"]}>facile</span> doit être faisable par un débutant (&quot;Finir le Circuit Mario 1 en Contre-La-Montre en moins de 55s&quot;)</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-1"]}>moyen</span> est typiquement difficile pour un débutant mais facile pour un joueur expérimenté (&quot;Finir le Circuit Mario 1 en CLM en moins de 45s&quot;)</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-2"]}>difficile</span> sera difficile pour un joueur expérimenté mais réussissable en plusieurs essais (&quot;Finir le Circuit Mario 1 en CLM en moins de 39s&quot;)</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-3"]}>extrême</span> nécessitera de try-harder même pour un joueur expérimenté (&quot;Finir le Circuit Mario 1 en CLM en moins de 38s&quot;)</li>
          <li>Un défi <span className={styles["challenges-item-difficulty-4"]}>impossible</span> nécessite de try-harder et peut typiquement prendre plusieurs heures (voire jours) avant de réussir (&quot;Finir le Circuit Mario 1 en CLM en moins de 37s&quot;)</li>
        </ul>
      </div>
    </p>
  }
}
function ReValidationTips() {
  const language = useLanguage();

  return <>{language ? "A challenge you accepted or rejected by mistake? A difficulty to change? You're in the right place!" : "Un défi que vous avez accepté ou refusé par erreur ? Une difficulté à changer ? C'est ici que ça se passe !"}</>
}
function ChallengesListSearch() {
  const language = useLanguage();
  const router = useRouter();
  const handleSearch = useFormSubmit();
  const { ordering, author, winner, difficulty, hide_succeeded } = router.query;
  const challengeDifficulties = useChallengeDifficulties();

  return <form method="get" className={styles["challenges-list-search"]} action="/challenges" onSubmit={handleSearch}>
    <p>
      <label>{language ? 'Filter:' : 'Filtrer :'}{" "}
        {author && <input type="hidden" name="author" defaultValue={author} />}
        {winner && <input type="hidden" name="winner" defaultValue={winner} />}
        <select name="difficulty" defaultValue={difficulty}>
          <option value="">{language ? 'Difficulty...' : 'Difficulté...'}</option>
          {challengeDifficulties?.map((challengeDifficulty) => <option key={challengeDifficulty.level} value={challengeDifficulty.level}>{challengeDifficulty.name}</option>)}
        </select></label>
      &nbsp;
      <label><input type="checkbox" name="hide_succeeded" defaultChecked={!!hide_succeeded} />{language ? 'Hide succeeded challenges' : 'Masquer les défis réussis'}</label>
    </p>
    <p>
      <label>{language ? 'Show first:' : 'Afficher en premier :'}
        {" "}
        <select name="ordering" defaultValue={ordering}>
          <option value="latest">{language ? 'Most recent challenges' : 'Défis les plus récents'}</option>
          <option value="rating">{language ? 'Top rated challenges' : 'Défis les mieux notés'}</option>
        </select></label>
      &nbsp;<input type="submit" value="Ok" />
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
}
function ChallengeItem({ challenge }: ChallengeItemProps) {
  const router = useRouter();
  const language = useLanguage();
  const challengeDifficulties = useChallengeDifficulties();
  const { moderate, remoderate, rate, ordering } = router.query;
  const rateChallenges = rate;
  const isChallengeAction = rateChallenges || (moderate != null) || (remoderate != null);
  const challengeAction = useMemo(() => {
    if (rateChallenges)
      return "rate";
    if (moderate != null)
      return "moderate";
    if (remoderate != null)
      return "remoderate";
    return null;
  }, [rateChallenges, moderate, remoderate]);

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
  const [selectedDifficulty, setSelectedDifficulty] = useState(challenge.difficulty.level);

  const acceptChallenge = useCallback(() => {
    const lastDifficulty = challenge.difficulty.level;
    const newDifficulty = selectedDifficulty;
    const difficultyChanged = (lastDifficulty != newDifficulty);
    if (difficultyChanged) {
      window["o_prompt"](language
        ? "Please confirm challenge <strong>approval</strong>.<br />Optionnal: explain why you changed challenge difficulty:"
        : "Veuillez confirmer la <strong>validation</strong> du défi.<br />Facultatif&nbsp;: expliquez le changement de difficulté&nbsp;:",
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
  }, [challenge, language]);

  const rejectChallenge = useCallback(() => {
    window["o_prompt"](language
      ? "Please confirm challenge <strong>rejection</strong>.<br />Optionnal: explain why you rejected challenge:"
      : "Veuillez confirmer la <strong>non-validation</strong> du défi.<br />Facultatif&nbsp;: donnez les raisons du refus&nbsp;:",
      "",
      function (msg) {
        var data = { "challenge": challenge.id, "accept": 0 };
        if (msg) data["msg"] = msg;
        challengeModerate(data);
      }
    );
  }, [challenge, language]);

  const remoderateChallenge = useCallback(() => {
    window["o_confirm"](language ? "Put this challenge back to the &quot;pending moderation&quot; list?" : "Repasser ce défi dans la liste des défis à modérer ?", function (valided) {
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
      onClick={() => {
        if (isChallengeAction)
          return false;
      }}
    >
      {
        (challengeAction === 'rate') && <>
          <div className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${challenge.difficulty.level}`])}>
            <img src={`images/challenges/difficulty${challenge.difficulty.level}.png`} alt={challenge.difficulty.name} />
            {" "}{challenge.difficulty.name}
            {challengeThanks && <span className={styles["challenges-item-rating-thanks"]}>{language ? 'Thanks!' : 'Merci !'}</span>}
          </div>
          <RatingControl defaultValue={challenge.rating.avg} onChange={(value) => {
            rateChallenge(value);
          }} />
          {challenge.circuit.author && <div className={styles["challenges-item-author"]}>
            {language ? 'By' : 'Par'} <strong>{challenge.circuit.author}</strong>
          </div>}
        </>}
      {
        (challengeAction === "moderate") && <>
          <div className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${challenge.difficulty.level}`])}>
            <div className={styles["challenges-item-difficulty-value"]}>
              <img src={`images/challenges/difficulty${challenge.difficulty.level}.png`} alt={challenge.difficulty.name} />
              {" "}{challenge.difficulty.name}
              <span className={styles["challenge-item-link"]} onClick={editDifficulty}>{language ? 'Edit' : 'Modifier'}</span>
            </div>
            <div className={cx(styles["challenges-item-difficulty-edit"], {
              [styles["challenges-item-editting"]]: edittingDifficulty
            })}>
              <select className={styles["challenges-item-difficulty-select"]} value={selectedDifficulty} onChange={(e) => setSelectedDifficulty(+e.target.value)}>
                {challengeDifficulties.map((name, i) => <option value={i}>{name}</option>)}
              </select>
              <span className={styles["challenge-item-link"]} onClick={uneditDifficulty}>{language ? 'Undo' : 'Annuler'}</span>
            </div>
          </div>
          {challenge.circuit.author && <div className={styles["challenges-item-author"]}>
            {language ? 'By' : 'Par'} <strong>{challenge.circuit.author}</strong>
          </div>}
          <div className={styles["challenges-item-moderation"]}>
            <button className={styles["challenges-item-accept"]} onClick={acceptChallenge}>&check;</button>
            <button className={styles["challenges-item-reject"]} onClick={rejectChallenge}>&times;</button>
          </div>
        </>
      }
      {
        (challengeAction === "remoderate") && <>
          <div className={styles["challenge-item-remoderate"]}>
            {challenge.status === 'active' ? <>
              <span className={cx(styles["challenges-item-difficulty"], styles[`challenges-item-difficulty-${challenge.difficulty.level}`])}>
                <img src={`images/challenges/difficulty${challenge.difficulty.level}.png`} alt={challenge.difficulty.name} />
                {" "}{challenge.difficulty.name}
              </span><br />
              <span className={styles["challenges-item-accepted"]}>
                {language ? 'Accepted' : 'Accepté'}
              </span>
            </> : <>
              <span className={styles["challenges-item-rejected"]}>
                {language ? 'Rejected' : 'Refusé'}
              </span>
            </>}
            <br />
            <span className={styles["challenge-item-link"]} onClick={remoderateChallenge}>
              {language ? 'Undo' : 'Annuler'}
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
            {language ? 'By' : 'Par'} <strong>{challenge.circuit.author}</strong>
          </div>}
        </>
      }
    </div>
  </a>
}

export default WithAppContext(ChallengesList);