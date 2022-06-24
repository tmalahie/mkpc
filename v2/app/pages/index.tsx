import type { NextPage } from 'next'
import Head from 'next/head'

import Ad from "../components/Ad/Ad";
import ClassicPage, { commonStyles } from "../components/ClassicPage/ClassicPage";
import withServerSideProps from '../components/WithAppContext/withServerSideProps';
import Rating from "../components/Rating/Rating";
import useLanguage, { plural } from "../hooks/useLanguage";
import useScript, { insertScript } from "../hooks/useScript";
import styles from "../styles/Home.module.scss"
import mkpcBox from "../images/main/mkpc_box.jpg"
import sidebarIcon from "../images/icons/sidebar_icon.png"

import aboutIcon from "../images/icons/about.png"
import screenshotsIcon from "../images/icons/camera.png"
import thanksIcon from "../images/icons/thanks.png"
import followIcon from "../images/icons/follow.png"
import gameIcon from "../images/icons/gamepad.png"
import birthdayIcon from "../images/icons/birthday.png"
import commentIcon from "../images/icons/comment.png"
import clockIcon from "../images/icons/clock.png"

import diapo1 from "../images/main/slides/diapo1.jpg"
import diapo2 from "../images/main/slides/diapo2.jpg"
import diapo3 from "../images/main/slides/diapo3.png"
import diapo4 from "../images/main/slides/diapo4.png"
import diapo5 from "../images/main/slides/diapo5.jpg"
import diapo6 from "../images/main/slides/diapo6.jpg"
import diapo7 from "../images/main/slides/diapo7.jpg"
import diapo8 from "../images/main/slides/diapo8.png"

import ss1 from "../images/main/screenshots/ss1.png"
import ss2 from "../images/main/screenshots/ss2.png"
import ss3 from "../images/main/screenshots/ss3.png"
import ss4 from "../images/main/screenshots/ss4.png"
import ss5 from "../images/main/screenshots/ss5.png"
import ss6 from "../images/main/screenshots/ss6.png"
import ss7 from "../images/main/screenshots/ss7.png"
import ss8 from "../images/main/screenshots/ss8.png"
import ss9 from "../images/main/screenshots/ss9.png"
import ss10 from "../images/main/screenshots/ss10.png"
import ss11 from "../images/main/screenshots/ss11.png"
import ss12 from "../images/main/screenshots/ss12.png"

import ss1xs from "../images/main/screenshots/ss1xs.png"
import ss2xs from "../images/main/screenshots/ss2xs.png"
import ss3xs from "../images/main/screenshots/ss3xs.png"
import ss4xs from "../images/main/screenshots/ss4xs.png"
import ss5xs from "../images/main/screenshots/ss5xs.png"
import ss6xs from "../images/main/screenshots/ss6xs.png"
import ss7xs from "../images/main/screenshots/ss7xs.png"
import ss8xs from "../images/main/screenshots/ss8xs.png"
import ss9xs from "../images/main/screenshots/ss9xs.png"
import ss10xs from "../images/main/screenshots/ss10xs.png"
import ss11xs from "../images/main/screenshots/ss11xs.png"
import ss12xs from "../images/main/screenshots/ss12xs.png"
import { formatDate, localeString } from "../helpers/dates";
import { formatRank, formatTime } from "../helpers/records";
import { escapeHtml } from "../helpers/strings";
import { buildQuery } from "../helpers/uris";
import { Fragment, useEffect, useMemo, useState } from "react";
import cx from "classnames";
import { uniqBy } from "../helpers/objects";
import Link from "next/link";
import useSmoothFetch, { postData, Placeholder } from '../hooks/useSmoothFetch';
import Skeleton from '../components/Skeleton/Skeleton';
import useAuthUser from '../hooks/useAuthUser';
import useCreations from '../hooks/useCreations';
import { useTranslation } from 'next-i18next';
import Tooltip from 'react-tooltip';

const screenshots = [[{
  xs: ss1xs,
  lg: ss1
}, {
  xs: ss2xs,
  lg: ss2
}, {
  xs: ss3xs,
  lg: ss3
}], [{
  xs: ss4xs,
  lg: ss4
}, {
  xs: ss5xs,
  lg: ss5
}, {
  xs: ss6xs,
  lg: ss6
}], [{
  xs: ss7xs,
  lg: ss7
}, {
  xs: ss8xs,
  lg: ss8
}, {
  xs: ss9xs,
  lg: ss9
}], [{
  xs: ss10xs,
  lg: ss10
}, {
  xs: ss11xs,
  lg: ss11
}, {
  xs: ss12xs,
  lg: ss12
}]];

function SectionBar({ title, link }) {
  const bar =
    <table className={styles.sidebar_container}>
      <tbody>
        <tr><td className={styles.sidebar_icon}><img src={sidebarIcon.src} alt={title} /></td>
          <td className={styles.sidebar_title}>{title}</td></tr>
      </tbody>
    </table>
  return (
    link ? <Link href={link}><a className={styles.sidebar_link}>{bar}</a></Link> : bar
  );
}

enum LeaderboardTab {
  VS = 0,
  BATTLE = 1,
  TT_150 = 2,
  TT_200 = 3
}

const localesNs = ["home", "common"];
const Home: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  useScript("/scripts/jquery.min.js", {
    async: false, onload: () => {
      insertScript("/scripts/slider.js");
    }
  });
  useScript("/scripts/photoswipe.min.js", {
    async: false,
    onload: () => {
      insertScript("/scripts/init-diapos.js");
    }
  });

  const { previewCreation } = useCreations();

  const [leaderboardTab, setLeaderboardTab] = useState<LeaderboardTab>(LeaderboardTab.VS);
  function dispRankTab(e, tab) {
    e.preventDefault();
    setLeaderboardTab(tab);
    setDismissedRankTabs({ ...dismissedRankTabs, [tab]: true });
    if (tab >= LeaderboardTab.TT_150)
      setCurrentTtCc(tab);
  }
  const [currentTtCc, setCurrentTtCc] = useState(LeaderboardTab.TT_150);

  const leaderboardLink = useMemo(() => {
    switch (leaderboardTab) {
      case LeaderboardTab.VS:
        return "/leaderboard/vs";
      case LeaderboardTab.BATTLE:
        return "/leaderboard/battle";
      case LeaderboardTab.TT_150:
        return "/leaderboard/tt/150";
      case LeaderboardTab.TT_200:
        return "/leaderboard/tt/200";
    }
  }, [leaderboardTab]);

  const user = useAuthUser();

  const { data: topicsPayload, loading: topicsLoading } = useSmoothFetch("/api/forum/topics/find", {
    placeholder: () => ({
      data: Placeholder.array(10, (id) => ({
        id,
        title: Placeholder.text(25, 45),
        nbMessages: Placeholder.number(1, 1000),
        language: "en",
        lastMessage: {
          author: {
            name: Placeholder.text(8, 12)
          },
          date: Placeholder.date()
        }
      }))
    }),
    requestOptions: postData({
      sort: {
        key: "lastMessageDate",
        order: "desc"
      },
      paging: {
        limit: 10
      }
    })
  });
  const topicsSorted = useMemo(() => {
    if (language === 0)
      return topicsPayload.data;
    return [
      ...topicsPayload.data.filter(t => t.language !== "fr"),
      ...topicsPayload.data.filter(t => t.language === "fr")
    ];
  }, [topicsPayload, language]);
  const { data: newsPayload, loading: newsLoading } = useSmoothFetch("/api/news/find", {
    placeholder: () => ({
      data: Placeholder.array(10, (id) => ({
        id,
        author: {
          name: Placeholder.text(10, 20)
        },
        category: {
          name: Placeholder.text(3, 8)
        },
        isNew: false,
        nbComments: Placeholder.number(1, 100),
        title: Placeholder.text(25, 45),
        publicationDate: Placeholder.date()
      }))
    }),
    requestOptions: postData({
      sort: {
        key: "publicationDate",
        order: "desc"
      },
      filters: [{
        key: "status",
        operator: "=",
        value: "accepted"
      }],
      paging: {
        limit: 10
      }
    })
  });
  const { data: lastNewsRead, loading: lastNewsReadLoading } = useSmoothFetch<{ date: string }>("/api/news/lastRead", {
    disabled: !user
  });
  const listNews = useMemo(() => {
    if (lastNewsReadLoading) return newsPayload.data;
    let newDateThreashold = new Date().getTime() - 7 * 86400000;
    if (lastNewsRead) {
      const lastReadDate = new Date(lastNewsRead.date).getTime();
      if (lastReadDate > newDateThreashold)
        newDateThreashold = lastReadDate;
    }
    return newsPayload.data.map(n => ({
      ...n,
      isNew: new Date(n.publicationDate).getTime() > newDateThreashold
    }));
  }, [lastNewsRead, newsPayload, lastNewsReadLoading]);
  const creationParams = useMemo(() => {
    const nbByType = [1, 1, 2, 2, 3, 3, 2, 2];
    return buildQuery({ nbByType });
  }, []);
  const { data: creationsPayload, loading: creationsLoading } = useSmoothFetch(`/api/getCreations.php?${creationParams}`, {
    placeholder: () => ({
      data: Placeholder.array(10, (id) => ({
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
      }))
    })
  });
  useEffect(() => {
    // @ts-ignore
    if (creationsPayload && window.loadCircuitImgs) {
      // @ts-ignore
      window.loadCircuitImgs();
    }
  }, [creationsPayload]);
  const creationsSorted = useMemo(() => {
    const logb = Math.log(1.7);
    const linesWithScore = creationsPayload.data.map((line) => {
      let publishedSince = new Date().getTime() - line.publicationDate;
      publishedSince = Math.max(publishedSince / 1000, 0);
      let recency = 8 - Math.log(publishedSince / 2000) / logb;
      recency = Math.min(Math.max(recency, 3), 8);
      let rating = line.rating - 1;
      let nbRatings = Math.max(line.nbRatings, 1);
      if (rating === -1) {
        if (recency === 8)
          rating = recency;
        else
          rating = 2;
      }
      else if (recency > rating) {
        if (rating >= 2.6)
          rating = recency;
        else if (rating <= 1.4)
          rating = Math.max(nbRatings, 2);
      }
      return {
        ...line,
        score: (recency + rating * nbRatings) / (1 + nbRatings)
      }
    });
    const sortedLines = linesWithScore.sort((line1, line2) => {
      if (line1.score !== line2.score)
        return line2.score - line1.score;
      return line2.publicationDate - line1.publicationDate;
    });
    return sortedLines.slice(0, 14);
  }, [creationsPayload]);

  const { data: challengesPayload, loading: challengesLoading } = useSmoothFetch(`/api/getChallenges.php`, {
    placeholder: () => ({
      data: Placeholder.array(10, (id) => ({
        id,
        name: Placeholder.text(25, 45),
        difficulty: {
          name: Placeholder.text(3, 8)
        },
        description: {
          main: Placeholder.text(25, 45)
        },
        circuit: {
          name: Placeholder.text(15, 25),
          author: Placeholder.text(8, 12),
          href: ""
        },
        succeeded: null
      }))
    })
  });
  const challengesSorted = useMemo(() => {
    return challengesPayload.data.slice(0, 15);
  }, [challengesPayload]);

  const { data: commentsPayload, loading: commentsLoading } = useSmoothFetch(`/api/track-builder/comments`, {
    placeholder: () => ({
      data: Placeholder.array(5, (id) => ({
        id,
        message: Placeholder.text(25, 45),
        date: Placeholder.date(),
        circuit: {
          name: Placeholder.text(10, 20),
          url: `#comment-${id}`
        },
        author: {
          name: Placeholder.text(8, 12)
        }
      }))
    })
  });
  const { data: recordsPayload, loading: recordsLoading } = useSmoothFetch("/api/time-trial/records/find", {
    placeholder: () => ({
      data: Placeholder.array(5, (id) => ({
        id,
        name: Placeholder.text(8, 12),
        date: Placeholder.date(),
        time: Placeholder.number(60000, 200000),
        circuit: {
          name: Placeholder.text(10, 20),
          url: `#record-${id}`
        },
        player: {
          name: Placeholder.text(8, 12)
        },
        leaderboard: {
          rank: 1,
          count: 1
        }
      }))
    }),
    requestOptions: postData({
      filters: [{
        key: "type",
        operator: "in",
        value: ["circuits", "mkcircuits"]
      }],
      sort: {
        key: "date",
        order: "desc"
      }
    })
  });
  const activityPayload = useMemo(() => {
    const comments = commentsPayload.data.map((comment) => {
      return {
        ...comment,
        key: `comment${comment.id}`,
        title: comment.message,
        message: escapeHtml(comment.message),
        name: comment.author?.name,
        icon: commentIcon,
        type: "comment",
        recency: new Date().getTime() - new Date(comment.date).getTime()
      }
    });
    const records = recordsPayload.data.map((record) => {
      return {
        ...record,
        key: `record${record.id}`,
        icon: clockIcon,
        message: `${formatTime(record.time)} (${formatRank(language, record.leaderboard.rank)} ${t("out_of")} ${record.leaderboard.count})`,
        type: "record",
        recency: (new Date().getTime() - new Date(record.date).getTime()) * 2
      }
    });
    const allActivity = [...comments, ...records];
    const activityWithCircuit = allActivity.filter(a => a.circuit);
    const activitySorted = activityWithCircuit.sort((a1, a2) => a1.recency - a2.recency);
    const allActivityByCircuit = uniqBy(activitySorted, (a) => a.circuit.url);
    return allActivityByCircuit.slice(0, 14);
  }, [commentsPayload, recordsPayload, language]);

  const leaderboardPlaceholder = () => ({
    data: Placeholder.array(10, (id) => ({
      id,
      name: Placeholder.text(10, 20),
      score: Placeholder.number(1000, 20000),
    }))
  });
  const { data: vsLeaderboard, loading: vsLoading } = useSmoothFetch("/api/online-game/leaderboard", {
    placeholder: leaderboardPlaceholder,
    requestOptions: postData({
      mode: "vs",
      paging: {
        limit: 10
      }
    }),
  });
  const vsLeaderboardFiltered = useMemo(() => vsLeaderboard?.data.slice(0, 10) ?? [], [vsLeaderboard]);
  const { data: battleLeaderboard, loading: battleLoading } = useSmoothFetch("/api/online-game/leaderboard", {
    placeholder: leaderboardPlaceholder,
    requestOptions: postData({
      mode: "battle",
      paging: {
        limit: 10
      }
    }),
  });
  const battleLeaderboardFiltered = useMemo(() => battleLeaderboard?.data.slice(0, 10) ?? [], [battleLeaderboard]);

  const { data: tt150Leaderboard, loading: tt150Loading } = useSmoothFetch("/api/time-trial/leaderboard", {
    placeholder: leaderboardPlaceholder,
    requestOptions: postData({
      cc: 150,
      paging: {
        limit: 10
      }
    }),
  });
  const tt150LeaderboardFiltered = useMemo(() => tt150Leaderboard?.data.slice(0, 10) ?? [], [tt150Leaderboard]);
  const { data: tt200Leaderboard, loading: tt200Loading } = useSmoothFetch("/api/time-trial/leaderboard", {
    placeholder: leaderboardPlaceholder,
    requestOptions: postData({
      cc: 200,
      paging: {
        limit: 10
      }
    }),
  });
  const tt200LeaderboardFiltered = useMemo(() => tt200Leaderboard?.data.slice(0, 10) ?? [], [tt200Leaderboard]);

  const leaderboard = useMemo(() => {
    switch (leaderboardTab) {
      case LeaderboardTab.VS:
        return vsLeaderboardFiltered;
      case LeaderboardTab.BATTLE:
        return battleLeaderboardFiltered;
      case LeaderboardTab.TT_150:
        return tt150LeaderboardFiltered;
      case LeaderboardTab.TT_200:
        return tt200LeaderboardFiltered;
    }
  }, [leaderboardTab, vsLeaderboardFiltered, battleLeaderboardFiltered, tt150LeaderboardFiltered, tt200LeaderboardFiltered]);
  const leaderboardLoading = useMemo(() => {
    switch (leaderboardTab) {
      case LeaderboardTab.VS:
        return vsLoading;
      case LeaderboardTab.BATTLE:
        return battleLoading;
      case LeaderboardTab.TT_150:
        return tt150Loading;
      case LeaderboardTab.TT_200:
        return tt200Loading;
    }
  }, [leaderboardTab, vsLeaderboardFiltered, battleLeaderboardFiltered, tt150LeaderboardFiltered, tt200LeaderboardFiltered]);

  const { data: activeLobbiesData } = useSmoothFetch<ActiveLobbiesData>("/api/getOnlinePlayers.php", {
    placeholder: () => ({
      vs: [],
      battle: []
    })
  });
  const [dismissedRankTabs, setDismissedRankTabs] = useState({});

  return (
    <ClassicPage className={styles.Home} page="home">
      <Head>
        <link rel="stylesheet" href="/styles/photoswipe.css" />
        <link rel="stylesheet" href="/styles/slider.css" />
      </Head>
      <section id={styles.left_section}>
        <div className="fp-slider">
          <div className="fp-slides-container">
            <div className="fp-slides">
              <div className="fp-slides-items">
                <div className="fp-thumbnail" style={{ background: "url(" + diapo1.src + ") top" }}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("A_mario_kart_game_for")}</h3>
                    <p>
                      {language ? <>										A computer version of the famous racing game by Nintendo.<br />
                        This game is <strong>completely free</strong> and does not require <strong>any downloads</strong>. All you need is a web browser!
                      </> : <>										Une version ordi du célèbre jeu de course de Nintendo.<br />
                        Ce jeu est <strong>entièrement gratuit</strong> et ne requiert <strong>aucun téléchargement</strong>, un simple navigateur internet suffit.
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo2.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Crazy_races_full_of_fun")}</h3>
                    <p>
                      {language ? <>										Try to be the fastest while avoiding the items!<br />
                        Find all the <strong>56 tracks</strong> from the original games <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> and <strong>Mario Kart DS</strong>.
                      </> : <>										Tentez d'être le plus rapide tout en évitant les objets !<br />
                        Retrouvez l'intégralité des <strong>56 circuits</strong> repris du jeu original <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> et <strong>Mario Kart DS</strong>.
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo3.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Win_all_the_grand_prix")}</h3>
                    <p>
                      {language ? <>										Face off the cpu on the <strong>14 grands prix</strong> tournaments and try to win the gold cup!<br />
                        Win enough cups to unlock the <strong>15 secret characters</strong>!
                      </> : <>										Affrontez les ordis sur les <strong>14 grands prix</strong> et tentez de gagner la coupe en or !<br />
                        Remportez suffisament de coupes pour débloquer les <strong>15 persos secrets</strong> !
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo4.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Create_your_own_tracks")}</h3>
                    <p>
                      {language ? <>										With the <strong>track builder</strong>, the possibilities are infinite: the only limit is your own imagination.<br />
                        Try other peoples' creations thanks to the integrated <strong>sharing tool</strong>.
                      </> : <>										Avec l'<strong>éditeur de circuits</strong> et d'arènes, les possibilités sont infinies ; votre imagination est la seule limite !<br />
                        Essayer les créations des autres grâce à l'<strong>outil de partage intégré</strong>.
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo5.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Face_players_from_around")}</h3>
                    <p>
                      {language ? <>										Fight other players in <strong>online mode</strong>!<br />
                        Win as many races as possible and <strong>climb in the official ranking</strong>!
                      </> : <>										Battez-vous contre d'autres joueurs avec le <strong>mode en ligne</strong> !<br />
                        Remportez un maximum de course afin de <strong>grimper dans le classement</strong> officiel !
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo6.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Make_the_best_scores_in")}</h3>
                    <p>
                      {language ? <>										<strong>Finish the race track</strong> as fast as you can!<br />
                        <strong>Compare your scores</strong> with the community, and face other players' ghosts!
                      </> : <>										<strong>Bouclez les 3 tours</strong> le plus rapidement possible !<br />
                        <strong>Comparez votre score</strong> avec la communauté, et affrontez les fantômes des autres joueurs !
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo7.src}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Release_your_fighter_talents")}</h3>
                    <p>
                      {language ? <>										<strong>Destroy the balloons</strong> of your opponents with your own items without getting hit by theirs<br />
                        The last player standing wins!
                      </> : <>										<strong>Détruisez les ballons</strong> de votre adversaire en évitant de vous faire toucher !<br />
                        Soyez le dernier survivant pour remporter la partie !
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" style={{ backgroundImage: "url('" + diapo8.src + "')" }}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{t("Face_off_your_friends")}</h3>
                    <p>
                      {language ? <>										Prove your friends that you're the best!<br />
                        Face them in <strong>multiplayer</strong> on VS races or on battle mode.
                      </> : <>
                        Montrez à vos amis que vous êtes le meilleur !<br />
                        Affrontez-les en <strong>multijoueur</strong> en course VS ou sur les batailles de ballons.
                      </>}
                    </p>
                  </div>
                  {/* eslint-disable jsx-a11y/anchor-has-content */}
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>
            </div>
            <div className="fp-nav">
              <span className="fp-pager"></span>
            </div>
          </div>
        </div>
        <h1>Mario Kart PC</h1>
        <div id={styles.toBegin}><Link href="/game"><a>
          &#9660;&nbsp;{t("Click_on_the_game_box")}&nbsp;&#9660;<br />
          <img src={mkpcBox.src} alt={t("Go_to_the_game")} /><br />
          &#9650;&nbsp;{t("Click_on_the_game_box")}&nbsp;&#9650;</a></Link></div>
        <h2><img src={aboutIcon.src} alt="" /> {t("Whats_mario_kart_pc")}</h2>
        <div>
          {language ? <>				<p>You might know Mario Kart, the most fun racing game series of all time!
            Mario Kart PC uses the same base as the original games but is playable on your browser, and <strong>for free</strong>.</p>
            <p>Most of the modes from Mario Kart have been included: Grand Prix, VS, Battle mode, Time Trials, and more!<br />
              There's also a brand new mode: the <strong>track builder</strong>! Place straight lines and turns, add items, boost panels and more!
              Everything is customizable! The only limit is your own imagination!<br />
              You can share your tracks, and try other people's tracks thanks to the <Link href="/creations">sharing tool</Link>. Thousands of custom tracks are already available!</p>
            <p>Finally, you can face players from the whole world thanks to the <strong>multiplayer online mode</strong>! Climb the <Link href="/leaderboard/vs">rankings</Link> and become world champion!</p>
          </> : <>				<p>Vous connaissez certainement Mario Kart, le jeu de course le plus fun de tous les temps !
            Mario Kart PC reprend les mêmes principes que le jeu original mais il est jouable sur navigateur, et <strong>gratuitement</strong>.</p>
            <p>La plupart des modes issus de Mario Kart ont été repris : Grand Prix, courses VS, batailles de ballons, contre-la-montre...<br />
              Et un dernier mode inédit : l'<strong>éditeur de circuits</strong> ! Placez les lignes droites et les virages, ajoutez les objets, insérez des accélérateurs...
              Tout est personnalisable ! Votre imagination est la seule limite !<br />
              Vous pouvez également partager vos créations et essayer celles des autres grâce à l'<Link href="/creations">outil de partage</Link>.
              Plusieurs milliers de circuits ont déjà été partagés !</p>
            <p>Enfin, il est possible d'affronter les joueurs du monde entier grâce au <strong>mode multijoueurs en ligne</strong> ! Grimpez dans le <Link href="/leaderboard/vs">classement</Link> et devenez champion du monde !</p>
          </>}
        </div>
        <h2><img src={screenshotsIcon.src} alt="" /> {t("Some_screenshots")}</h2>
        <div>
          {t("Here_are_some_screenshots")}
          <table id={styles.screenshots} className="demo-gallery">
            <tbody>
              {screenshots.map((screenshotGroup, i) => <tr key={i}>
                {screenshotGroup.map((screenshot, j) => <td key={j}>
                  <a href={screenshot.lg.src} data-size="960x468" data-med={screenshot.lg.src} data-med-size="240x117" className="demo-gallery__photo demo-gallery__img--main"><img src={screenshot.xs.src} alt={"Screenshot " + (i * 3 + j + 1)} /></a>
                </td>)}
              </tr>)}
            </tbody>
          </table>
        </div>
        <br />
        <div className={styles.pub_section}>
          <Ad width={728} height={90} bannerId="4919860724" />
        </div>
        <h2><img src={thanksIcon.src} alt="" /> {t("Special_thanks")}</h2>
        <div>
          {language ? <>				A big thanks to Nintendo, these three sites and these artists without which Mario Kart PC would have probably never existed !
            <ul>
              <li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> for the <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">basic Mario Kart</a></li>
              <li><a href="http://www.snesmaps.com/">SNESMaps</a> for the <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">track images</a></li>
              <li><a href="http://www.vgmusic.com/">VGmusic</a> for the <a href="http://www.vgmusic.com/music/console/nintendo/snes/index-sz.html#Super_Mario_Kart">musics</a></li>
              <li>And <a href="/credits.php">many more</a>!</li>
            </ul>
          </> : <>				Un grand merci à Nintendo, ces 3 sites et ces artistes sans lesquels Mario Kart PC n'aurait probablement jamais existé !
            <ul>
              <li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> pour le <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">Mario Kart de départ</a></li>
              <li><a href="http://www.snesmaps.com/">SNESMaps</a> pour les <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">images des circuits</a></li>
              <li><a href="https://downloads.khinsider.com/">Khinsider</a> pour les <a href="https://downloads.khinsider.com/search?search=mario+kart">musiques</a></li>
              <li>Et <a href="/credits.php">bien d'autres</a> !</li>
            </ul>
          </>}
        </div>
        <h2><img src={followIcon.src} alt="" /> {t("Follow_us")}</h2>
        <div>
          {language ? <>				<ul>
            <li><a href="https://discord.gg/VkeAxaj">Discord Server</a> of the site: join it to chat with the community and be informed about updates and events.</li>
            <li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Official Youtube Channel</a> : find out videos about the game and informations about the website and its events. The channel is maintained by members, if you want to participate, tell it on the <a href="/topic.php?topic=3392">official topic</a>.</li>
            <li><a href="https://twitter.com/MarioKartPC">Twitter Page</a> and <a href="https://www.facebook.com/groups/126497814060671/">Facebook Group</a> of the game: follow then to be informed of the latest news concerning the site!</li>
            <li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">MKPC Wiki</a>: find out all the information about the game and its history. This site is maintained by the community, if you want to contribute, tell it on the <a href="/topic.php?topic=343">this topic</a>!</li>
          </ul>
          </> : <>				<ul>
            <li><a href="https://discord.gg/VkeAxaj">Serveur Discord</a> du site : rejoignez-le pour discuter avec la communauté et être informé des mises à jours et événements.</li>
            <li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Chaîne Youtube Officielle</a> : retrouvez des vidéos sur le jeu et des informations sur le site et ses évenements. La chaîne est alimentée par les membres, si vous voulez participez, parlez-en sur <a href="/topic.php?topic=3392">le topic officiel</a>.</li>
            <li><a href="https://twitter.com/MarioKartPC">Page Twitter</a> et <a href="https://www.facebook.com/groups/126497814060671/">Groupe Facebook</a> du jeu : suivez-les pour être au courant des dernières actualités du site !</li>
            <li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">Wiki MKPC</a> : retrouvez toutes les informations sur le jeu et son histoire. Ce site est maintenu par les membres, si vous voulez contribuer, parlez-en sur <a href="/topic.php?topic=343">ce topic</a>&nbsp;!</li>
          </ul>
          </>}
          {language ? <>				<p><em>This site is mostly maintained by French members, if you see some translation errors in the game or the site, don't hesitate to report them on this <a href="/topic.php?topic=1">forum topic</a>.</em></p>
          </> : <></>}
        </div>
        <div className={styles.pub_section}>
          <Ad width={728} height={90} bannerId="4919860724" />
        </div>
        <h2><img src={gameIcon.src} alt="" /> {t("Go_to_the_game")}</h2>
        <div>
          {language ? <>				To start playing, it's very simple, just click on &quot;Play game&quot; in the menu above. Or more simply, click here:<br />
            <Link href="/game"><a className={cx(commonStyles.action_button, styles.button_game)}>Start playing now &gt;</a></Link>
          </> : <>				Pour commencer à jouer, c'est très simple, cliquez sur &quot;Le jeu&quot; dans le menu en haut. Ou plus simplement, cliquez là :<br />
            <Link href="/game"><a className={cx(commonStyles.action_button, styles.button_game)}>Commencer à jouer &gt;</a></Link>
          </>}
        </div>
      </section>
      <section id={styles.right_section}>
        <div className={styles.subsection}>
          {user && <BirthdaysList />}
          <SectionBar title="Forum" link="/forum" />
          <h2>{t("Last_topics")}</h2>
          <Skeleton id={styles.forum_section} className={styles.right_subsection} loading={topicsLoading}>
            {topicsSorted.map(topic => <a key={topic.id} href={"/topic.php?topic=" + topic.id} title={topic.title}>
              <h2>{topic.title}</h2>
              <h3>{t("Last_message")}
                {topic.lastMessage.author && <> {t("by")} <strong>{topic.lastMessage.author.name}</strong></>}
                {" "}
                {formatDate(topic.lastMessage.date, { language, prefix: true, mode: "short" })}</h3>
              <div className={styles.creation_comments} title={plural("%n message%s", topic.nbMessages)}><img src="images/comments.png" alt="Messages" /> {topic.nbMessages}</div>
            </a>)}
          </Skeleton>
          <Link href="/forum"><a className={cx(styles.right_section_actions, commonStyles.action_button)}>{t("Go_to_the_forum")}</a></Link>
        </div>
        <div className={styles.subsection}>
          <SectionBar title="News" link="/news" />
          <h2>{t("Latest_news")}</h2>
          <Skeleton loading={newsLoading} id={styles.news_section} className={styles.right_subsection}>
            {
              listNews.map(news => <a key={news.id} href={"/news.php?id=" + news.id} title={news.title} className={news.isNew ? styles.news_new : ""}>
                <h2>{news.title}</h2>
                <h3>{t("In")} <strong>{news.category.name}</strong> {news.author ? <>{t("by")} <strong>{news.author.name}</strong> </> : <></>}{formatDate(news.publicationDate, { language, prefix: true, mode: "short" })}</h3>
                <div className={styles.creation_comments} title={plural(t("n_comments"), news.nbComments)}><img src={commentIcon.src} alt="Messages" /> {news.nbComments}</div>
              </a>)
            }
          </Skeleton>
          {user?.roles.publisher && <PendingNews />}
          <Link href="/news"><a className={cx(styles.right_section_actions, commonStyles.action_button)}>{t("All_news")}</a></Link>
        </div>
        <div className={styles.subsection}>
          <SectionBar title={t("Track_builder")} link="/creations" />
          <h2>{t("Latest_creations")}</h2>
          <Skeleton loading={creationsLoading} id={styles.creations_section} className={styles.right_subsection}>
            <table>
              <tbody>
                {creationsSorted.map(creation => <tr key={creation.id} className={styles.creation_line}>
                  <td className={cx(styles.creation_icon, creation.isCup ? styles.creation_cup : styles.single_creation)}
                    style={{ backgroundImage: creation.icons ? creation.icons.map(src => `url('images/creation_icons/${src}')`).join(",") : undefined }}
                    data-cicon={creation.icons ? undefined : creation.cicon}
                    title={t("Preview")}
                    onClick={() => previewCreation(creation)}>
                  </td>
                  <td className={styles.creation_description}>
                    <a href={creation.href} title={creation.name}>
                      <h2>{creation.name || (t("Untitled"))}</h2>
                      <Rating rating={creation.rating} nbRatings={creation.nbRatings} label={<h3>{creation.author && <>{t("By")}{" "}{creation.author /* TODO control length */}</>}</h3>} />
                      {(creation.nbComments > 0) && <div className={styles.creation_coms} title={plural(t("n_comments"), creation.nbComments)}><img src={commentIcon.src} alt="Commentaires" />{creation.nbComments}</div>}
                      <div className={styles.creation_date} title={(t("Published")) + ' ' + formatDate(creation.publicationDate, { language, prefix: true, mode: "datetime" })}><img src={clockIcon.src} alt="Date" />{formatDate(creation.publicationDate, { language, mode: "short" })}</div>
                    </a>
                  </td>
                </tr>)}
              </tbody>
            </table>
          </Skeleton>
          <Link href="/creations"><a className={cx(styles.right_section_actions, commonStyles.action_button)}>{t("Display_all")}</a></Link>
          <h2>{t("Last_challenges")}</h2>
          <Skeleton loading={challengesLoading} id={styles.challenges_section} className={styles.right_subsection}>
            {
              challengesSorted.map(challenge => <a key={challenge.id} href={"/challengeTry.php?challenge=" + challenge.id} title={challenge.description.main} className={challenge.succeeded && styles.challenges_section_succeeded}>
                <h2>{challenge.description.main}</h2>
                <h3>
                  {challenge.circuit?.author && <div className={styles.challenge_section_author}>
                    {t("By")}{" "}
                    <strong>{challenge.circuit.author}</strong>
                  </div>}
                  {challenge.circuit?.name && <div className={styles.challenge_section_circuit}>
                    {challenge.circuit.author ? (t("in")) : (t("In"))}{" "}
                    <strong>{challenge.circuit.name}</strong>
                  </div>}
                  <div className={styles.challenge_section_difficulty}>
                    {"- "}<strong>{challenge.difficulty.name}</strong>
                  </div>
                </h3>
              </a>)
            }
          </Skeleton>
          {/*
			if (hasRight('clvalidator')) {
				$getPendingChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchallenges WHERE status="pending_moderation"'));
				if ($getPendingChallenges['nb']) {
					$s = ($getPendingChallenges['nb']>=2) ? 's':'';
					echo '<p className={styles["nb-pending-news"]}><Link href="/challenges?moderate">'. $getPendingChallenges['nb'] .' '. ($language ? 'pending':"défi$s") .'</Link> '. ($language ? "challenge$s":'en attente de validation') .'</p>';
				}
			*/}
          <Link href="/challenges"><a className={cx(styles.right_section_actions, commonStyles.action_button)}>{t("Display_all")}</a></Link>
          <div id={styles.challenge_ranking}><a href="/challengeRanking.php">{t("Challenge_points_leaderboard")}</a></div>
          <h2>{t("Recent_activity")}</h2>
          <Skeleton loading={commentsLoading || recordsLoading} id={styles.comments_section} className={styles.right_subsection}>
            {activityPayload?.map((activity) => (
              <a key={activity.key} href={activity.circuit.url} title={activity.title}>
                <h2><img src={activity.icon.src} alt={activity.type} /> <span dangerouslySetInnerHTML={{ __html: activity.message }} /></h2>
                <h3>
                  {activity.name && <div className={styles.comments_section_author}>
                    {t("By")}{" "}
                    <strong>{activity.name}</strong>
                  </div>}
                  {activity.circuit.name && <div className={styles.comments_section_circuit}>
                    {activity.name ? (t("in")) : (t("In"))}{" "}
                    <strong>{activity.circuit.name}</strong>
                  </div>}
                  <div className={styles.comments_section_date}>
                    {formatDate(activity.date, { language, prefix: true, mode: "short" })}
                  </div>
                </h3>
              </a>
            ))}
          </Skeleton>
        </div>
        <div className={cx(styles.subsection, styles.rank_vs)} id={styles.rankings_section}>
          <h2>Top 10</h2>
          <div className={styles.ranking_tabs}>
            <a className={cx({
              [styles.tab_selected]: leaderboardTab === LeaderboardTab.VS
            })} href="#null" onClick={(e) => dispRankTab(e, LeaderboardTab.VS)}>
              {t("Vs_mode")}
            </a><a className={cx({
              [styles.tab_selected]: leaderboardTab === LeaderboardTab.BATTLE
            })} href="#null" onClick={(e) => dispRankTab(e, LeaderboardTab.BATTLE)}>
              {t("Battle")}
              {(!!activeLobbiesData["battle"].length && !dismissedRankTabs[LeaderboardTab.BATTLE]) && <span className={styles.ranking_badge}><span>{activeLobbiesData["battle"].length}</span></span>}
            </a><a className={cx({
              [styles.tab_selected]: leaderboardTab >= LeaderboardTab.TT_150
            })} href="#null" onClick={(e) => dispRankTab(e, currentTtCc)}>
              {t("Time_trial")}
            </a>
          </div>
          <div id={styles.currently_online}>
            {leaderboardTab === LeaderboardTab.VS && <ActiveLobbies data={activeLobbiesData} mode="vs" />}
            {leaderboardTab === LeaderboardTab.BATTLE && <ActiveLobbies data={activeLobbiesData} mode="battle" />}
          </div>
          {(leaderboardTab >= LeaderboardTab.TT_150) && <div id={styles.clm_cc}>
            <a className={cx({ [styles.tab_selected]: leaderboardTab === LeaderboardTab.TT_150 })} href="#null" onClick={(e) => dispRankTab(e, 2)}>150cc</a>
            {" "}<span>|</span>{" "}
            <a className={cx({ [styles.tab_selected]: leaderboardTab === LeaderboardTab.TT_200 })} href="#null" onClick={(e) => dispRankTab(e, 3)}>200cc</a>
          </div>}
          <Skeleton loading={leaderboardLoading} id={styles.top10} className={styles.right_subsection}>
            <table>
              <tbody>
                <tr>
                  <th>{t("Rank")}</th>
                  <th>{t("Nick")}</th>
                  <th>Score</th>
                </tr>
                {leaderboard.map((player, i) => <tr key={player.id}>
                  <td className={styles.top10position}>{i + 1}</td>
                  <td><a href={`profil.php?id=${player.id}`}>{player.name}</a></td>
                  <td>{player.score}</td>
                </tr>)}
              </tbody>
            </table>
          </Skeleton>
          <Link href={leaderboardLink}><a className={cx(styles.right_section_actions, commonStyles.action_button)}>{t("Display_all")}</a></Link>
        </div>
        <div className={styles.pub_section}>
          <Ad width={300} height={250} bannerId="4492555127" />
        </div>
        <div className={styles.subsection}>
          <div className={styles.flag_counter}>
            <h4>{t("Visitors_since_november")}</h4>
            <img src="https://s01.flagcounter.com/countxl/XMvG/bg_FFFFFF/txt_000000/border_CCCCCC/columns_3/maxflags_9/viewers_3/labels_0/pageviews_0/flags_0/percent_0/" alt={t("Visitors")} />
            <a className={cx(styles.right_section_actions, commonStyles.action_button)} href="/topic.php?topic=2288">{t("Learn_more")}</a>
          </div>
        </div>
      </section>
      <div id="gallery" className="pswp" role="dialog" aria-hidden="true">
        <div className="pswp__bg"></div>
        <div className="pswp__scroll-wrap">
          <div className="pswp__container">
            <div className="pswp__item"></div>
            <div className="pswp__item"></div>
            <div className="pswp__item"></div>
          </div>
          <div className="pswp__ui pswp__ui--hidden">
            <div className="pswp__top-bar">
              <div className="pswp__counter"></div>
              <button className="pswp__button pswp__button--close" title="Close (Esc)"></button>
              <button className="pswp__button pswp__button--share" title="Share"></button>
              <button className="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
              <button className="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
              <div className="pswp__preloader">
                <div className="pswp__preloader__icn">
                  <div className="pswp__preloader__cut">
                    <div className="pswp__preloader__donut"></div>
                  </div>
                </div>
              </div>
            </div>
            <div className="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
              <div className="pswp__share-tooltip">
              </div>
            </div>
            <button className="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button className="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div className="pswp__caption">
              <div className="pswp__caption__center"></div>
            </div>
          </div>
        </div>
      </div>
    </ClassicPage>
  )
}
function BirthdaysList() {
  const { data: birthdaysPayload } = useSmoothFetch<{
    data: Array<{
      id: string;
      name: string;
    }>
  }>(`/api/getBirthdays.php`);
  const { t } = useTranslation(localesNs);

  const membersList = useMemo(() => {
    if (!birthdaysPayload) return;
    let res = [];
    const nbBirthdays = birthdaysPayload.data.length;
    for (let i = 0; i < nbBirthdays; i++) {
      const member = birthdaysPayload.data[i];
      res.push(<Fragment key={member.id}>
        {!!i && ((i == nbBirthdays - 1) ? (t("and_")) : ", ")}
        <a href={`/profil.php?id=${member.id}`}>{member.name}</a>
      </Fragment>);
    }
    return res;
  }, [birthdaysPayload]);

  if (!birthdaysPayload?.data.length) return <></>;

  return <div className={styles["birthdays-list"]}>
    <img src={birthdayIcon.src} alt="" />
    {t("Its_the_birthday_of")}
    {" "}
    {membersList}
    {t("common:_exclamation_mark")}
  </div>
}
function PendingNews() {
  const { t } = useTranslation(localesNs);
  const { data: pendingNewsPayload } = useSmoothFetch<{ count: number }>("/api/news/find", {
    requestOptions: postData({
      filters: [{
        key: "status",
        operator: "=",
        value: "pending"
      }],
      paging: {
        limit: 0,
        count: true
      }
    })
  });

  if (!pendingNewsPayload?.count) return <></>;

  return <p className={styles["nb-pending-news"]}>
    <Link href={"/news#pending-news"}>{plural(t("n_pending"), pendingNewsPayload.count)}</Link> {t("news")}
  </p>
}
type ActiveLobbyForMode = {
  href: string;
  key?: number;
  creation?: {
      type: string;
      id: number;
      name: string;
  };
  rules?: Record<string, any>;
  players: {
      id: number;
      name: string;
      pts: number;
  }[];
};
type ActiveLobbiesData = {
  vs: ActiveLobbyForMode[];
  battle: ActiveLobbyForMode[];
};

type OnlineGameMode = "vs" | "battle";

interface ActiveLobbiesProps {
  data: ActiveLobbiesData;
  mode: OnlineGameMode;
}
function ActiveLobbies({ data, mode }: ActiveLobbiesProps) {
  const { t } = useTranslation(localesNs);

  const activeLobbiesForMode = data[mode];
  if (!activeLobbiesForMode.length) return <></>;

  const firstLobby = activeLobbiesForMode[0];
  return <div className={styles.ranking_current} id={styles[`ranking_current_${mode}`]}>
    <Tooltip place="top" effect="solid" offset={{top:-6}} />
    {
      (activeLobbiesForMode.length < 2) && !firstLobby.creation && !firstLobby.key ? 
      <span className={styles.ranking_list}>
        {t("Currently_online_")}{" "}
        <ActivePlayers data={firstLobby} mode={mode} single />
      </span> : <>
        {t("Currently_online_")}
        <ul className={styles.ranking_list_game}>
          {activeLobbiesForMode.map((lobby, i) => {
            return <li key={lobby.href}>
              <ActivePlayers data={lobby} mode={mode} />
            </li>
          })}
        </ul>
      </>
    }
  </div>
}

interface ActivePlayersProps {
  data: ActiveLobbyForMode;
  mode: OnlineGameMode;
  single?: boolean;
}
function ActivePlayers({ data, mode, single }: ActivePlayersProps) {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);

  const creationTrKey = useMemo(() => {
    if (!data.creation) return;

    if (mode === "battle") return "the_arena";

    switch (data.creation.type) {
      case "circuits":
      case "mkcircuits":
        return "the_circuit";
      case "mkcups":
        return "the_cup";
      case "mkmcups":
        return "the_multicup";
    }
  }, [data]);
  const rulesTrKey = useMemo(() => {
    if (data.creation) return;
    const defaultVal = single ? undefined : t("Normal");
    if (!data.rules) return defaultVal;

    const modeNames = {
      cc: '${value}cc',
      mirror: t("Mirror"),
      team: t("Team"),
      friendly: t('Friendly')
    };

    let ruleNames = [];
    for (const key in modeNames) {
      if (data.rules[key]) {
        ruleNames.push(modeNames[key].replace('${value}', data.rules[key]));
      }
    }
    if (!ruleNames.length) return defaultVal;
    return ruleNames.join("+");
  }, [data]);

  const title = useMemo(() => data.players.map(player => `${player.name} (${player.pts.toLocaleString(localeString(language))} pts)`).join("\n"), [data]);
  return <><span className={styles.ranking_activeplayernb} data-tip={title}>
    { t("n_Members", { count: data.players.length }) }
  </span>
  {" "}
  {creationTrKey && <>{t("in_the_creation", { the_creation: t(creationTrKey) })}
    {" "}<strong>{ data.creation?.name ?? t("Untitled") }</strong>{" "}
  </>}
  {rulesTrKey && t("in_mode_x", { x: t(rulesTrKey)})}
  <a className={cx(commonStyles.action_button, styles.action_button)} href={data.href}>{t("Join")}</a>
  </>
}

export const getServerSideProps = withServerSideProps({ localesNs })

export default Home;
