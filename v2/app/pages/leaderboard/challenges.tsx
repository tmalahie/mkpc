import { NextPage } from "next";
import ClassicPage, {
  commonStyles,
} from "../../components/ClassicPage/ClassicPage";
import styles from "../../helpers/globalStyles";
import useLanguage, { plural } from "../../hooks/useLanguage";
import { useTranslation } from "next-i18next";
import withServerSideProps from "../../components/WithAppContext/withServerSideProps";
import useSmoothFetch, { Placeholder, postData } from "../../hooks/useSmoothFetch";
import { formatRank } from "../../helpers/records";
import { usePaging } from "../../hooks/usePaging";
import Pager from "../../components/Pager/Pager";
import { useRouter } from "next/router";
import { useMemo, useState } from "react";
import Skeleton from "../../components/Skeleton/Skeleton";
import Link from "next/link";
import Ad from "../../components/Ad/Ad";
import Head from 'next/head';
import Autocomplete from "../../components/Autocomplete/Autocomplete";
import useDebounce from "../../hooks/useDebounce";
import useFormSubmit, { doSubmit } from "../../hooks/useFormSubmit";
import useAuthUser from "../../hooks/useAuthUser";
import useChallengeDifficulties from "../../hooks/useChallengeDifficulties";

const localesNs = ["leaderboard", "common"];

const ChallengeLeaderboard: NextPage = () => {
  const language = useLanguage();
  const user = useAuthUser();
  const { t } = useTranslation(localesNs);
  const router = useRouter();

  const { paging, currentPage, setCurrentPage, resPerPage } = usePaging();

  const playerQuery = router.query.player?.toString();
  const { recordsPayload, recordsLoading } = useLeaderboardData(paging, playerQuery);

  const [player, setPlayer] = useState<string>(playerQuery || (user?.id ? user.name : ""));
  const { playerSearchOptions } = usePlayerSearchData(player);

  const handleSearch = useFormSubmit();

  const [showExplain, setShowExplain] = useState(false);
  function toggleChallengeExplain(e) {
    e.preventDefault();
    setShowExplain(!showExplain);
  }

  const challengeDifficulties = useChallengeDifficulties();

  return (
    <ClassicPage
      title={t("Challenge_leaderboard")}
      className={styles.Leaderboard}
      page="game"
    >
      <Head>
        <link rel="stylesheet" type="text/css" href="/styles/classement.css" />
      </Head>
      <h1>{t("Challenge_points_leaderboard")}</h1>
      <div id={styles["ranking_explain"]}>
        {t("Leaderboard_cl_explain")}
        {" "}
        <a href="#null" onClick={toggleChallengeExplain} style={{position:"relative",top:-1}}>[{t("common:Learn_more")}]</a>.
        {showExplain && <div id={styles["ranking_info"]}>{
          language ? <>
            <Link href="/challenges">Challenges</Link> are actions to perform in the game (Ex: &quot;Complete a track in less than 1:30&quot;).
            They are created by members thanks to the <strong>challenge editor</strong>. Anyone can create challenges, including you!<br />
            When you complete a challenge, you win a certain amount of <strong>challenge points</strong> depending on the difficulty of the challenge. Your position in the ranking is determined by your number of challenge points.
            {challengeDifficulties && <ul>
              {
                challengeDifficulties.map((difficulty,i) => <li>
                  A challenge <strong>{difficulty.name}</strong> gives you <strong>{plural("%n pt%s", difficulty.reward)}</strong>.
                </li>)
              }
            </ul>}
          </> : <>
            Les <Link href="/challenges">défis</Link> sont des actions à réaliser sur le jeu (Ex : &quot;Finir un circuit en moins de 1:30&quot;).
            Ils sont créés par les membres via l'<strong>éditeur de défis</strong>. N'importe qui peut créer des défis, vous aussi !<br />
            Lorsque vous réussissez un défi, vous gagnez un certain nombre de <strong>points défis</strong> en fonction de la difficulté. Ce sont ces points défis qui déterminent votre place dans le classement.
            {challengeDifficulties && <ul>
              {
                challengeDifficulties.map((difficulty,i) => <li>
                  Un défi <strong>{difficulty.name}</strong> rapporte <strong>{plural("%n pt%s", difficulty.reward)}</strong>.
                </li>)
              }
            </ul>}
          </>
        }
				</div>}
      </div>
      <Ad width={728} height={90} bannerId="4919860724" />
      <form method="post" name="player" action={`/leaderboard/challenges`} onSubmit={handleSearch}>
        <blockquote>
          <div>
            <label htmlFor="joueur">
              <strong>{t("See_player_")}</strong>
            </label>{" "}
            <Autocomplete
              type="text"
              name="player"
              id="joueur"
              value={player}
              items={playerSearchOptions}
              onChange={(value) => setPlayer(value)}
              onSelect={(_, e) => doSubmit(router, e.target.form)}
            />{" "}
            <input
              type="submit"
              value={t<string>("common:validate")}
              className={commonStyles.action_button}
            />
          </div>
        </blockquote>
      </form>
      {recordsPayload.count ? (
        <Skeleton loading={recordsLoading}>
        <table>
          <tbody>
          <tr id={styles.titres}>
            <td>Place</td>
            <td>{t("common:Nick")}</td>
            <td>Score</td>
          </tr>
          {recordsPayload.data.map((record, i) => (
            <tr className={i % 2 ? styles.clair : styles.fonce} key={record.id}>
              <td
                dangerouslySetInnerHTML={{
                  __html: formatRank(language, record.rank),
                }}
              />
              <td>
                <a
                  href={"/profil.php?id=" + record.id}
                  className={styles.recorder}
                >
                  {record.country?.code && (
                    <img
                      src={`/images/flags/${record.country.code}.png`}
                      alt={record.country.code}
                      onError={(e) => (e.currentTarget.style.display = "none")}
                    />
                  )}{" "}
                  {record.name}
                </a>
              </td>
              <td className={styles["cell-auto"]}>{record.score}</td>
            </tr>
          ))}
          <tr>
            <td colSpan={3} id={styles.page}>
              {playerQuery ? <CurrentPage urlPrefix={`/leaderboard/challenges`} page={Math.ceil(recordsPayload.data[0]?.rank / resPerPage)} onSetPage={setCurrentPage} /> : <Pager
                page={currentPage}
                paging={paging}
                count={recordsPayload.count}
                onSetPage={setCurrentPage}
              />}
            </td>
          </tr>
          </tbody>
        </table>
        </Skeleton>
      ) : (
        <p>
          <strong>{t("No_result_for_this_search")}</strong>
        </p>
      )}
      <p>
        <Link href={"/challenges"}>
          {t("Back_to_challenges")}
        </Link>
        <br />
        <Link href="/"><a className={styles.retour}>{t("common:Back_to_mario_kart_pc")}</a></Link>
      </p>
    </ClassicPage>
  );
};

function useLeaderboardData(paging: any, player?: string) {  
  const { data: recordsPayload, loading: recordsLoading } = useSmoothFetch(
    "/api/online-game/leaderboard",
    {
      placeholder: () => ({
        data: Placeholder.array(player ? 1 : 20, (id) => ({
          id,
          name: Placeholder.text(10, 20),
          score: Placeholder.number(1000, 20000),
          rank: id,
          country: null,
        })),
        count: 1,
      }),
      requestOptions: postData({
        name: player,
        mode: "challenge",
        paging
      }),
      reloadDeps: [paging, player],
    }
  );

  return { recordsPayload, recordsLoading };
}

function usePlayerSearchData(search: string) {
  const debouncedPlayer = useDebounce(search, 300);

  const { data: playerSearchData } = useSmoothFetch(
    "/api/user/find",
    {
      placeholder: () => ({
        data: Placeholder.array(0, (id) => ({
          id,
          name: Placeholder.text(10, 20)
        })),
      }),
      requestOptions: postData({
        filters: [{
          key: "name",
          operator: "~",
          value: debouncedPlayer
        }, {
          key: "deleted",
          operator: "=",
          value: 0
        }],
        paging: {
          page: 1,
          limit: 5
        }
      }),
      reloadDeps: [debouncedPlayer],
      disabled: !debouncedPlayer
    }
  )
  const playerSearchOptions = useMemo(() => {
    if (!debouncedPlayer) return [];
    if (!playerSearchData) return [];
    return playerSearchData?.data.map((player) => ({
      id: player.id,
      label: player.name
    }
  ))}, [debouncedPlayer, playerSearchData]);

  return { playerSearchOptions, playerSearchData };
}

interface CurrentPageProps {
  urlPrefix: string;
  page: number;
  onSetPage: (page: number) => void;
}
function CurrentPage({ urlPrefix, page, onSetPage }: CurrentPageProps) {
  return <>
    Page :{"  "}
    <Link href={`${urlPrefix}?page=${page}`}><a onClick={() => onSetPage?.(page)}>{page}</a></Link>
  </>;
}

export const getServerSideProps = withServerSideProps({ localesNs });

export default ChallengeLeaderboard;
