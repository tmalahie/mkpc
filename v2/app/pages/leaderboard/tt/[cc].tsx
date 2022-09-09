import { NextPage } from "next";
import ClassicPage, {
  commonStyles,
} from "../../../components/ClassicPage/ClassicPage";
import styles from "../../../helpers/globalStyles";
import useLanguage from "../../../hooks/useLanguage";
import { useTranslation } from "next-i18next";
import withServerSideProps from "../../../components/WithAppContext/withServerSideProps";
import useSmoothFetch, { Placeholder, postData } from "../../../hooks/useSmoothFetch";
import { formatRank } from "../../../helpers/records";
import { usePaging } from "../../../hooks/usePaging";
import Pager from "../../../components/Pager/Pager";
import { useRouter } from "next/router";
import { useMemo, useState } from "react";
import Skeleton from "../../../components/Skeleton/Skeleton";
import Link from "next/link";
import Ad from "../../../components/Ad/Ad";
import Head from 'next/head';
import Autocomplete from "../../../components/Autocomplete/Autocomplete";
import useDebounce from "../../../hooks/useDebounce";
import useFormSubmit, { doSubmit } from "../../../hooks/useFormSubmit";
import useAuthUser from "../../../hooks/useAuthUser";

import detailsIcon from "../../../images/icons/details.png"

const localesNs = ["leaderboard", "common"];

const TTLeaderboard: NextPage = () => {
  const language = useLanguage();
  const user = useAuthUser();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const cc = +(router.query.cc || 150);

  const { paging, currentPage, setCurrentPage, resPerPage } = usePaging();

  const playerQuery = router.query.player?.toString();
  const { recordsPayload, recordsLoading } = useLeaderboardData(cc, paging, playerQuery);

  const [player, setPlayer] = useState<string>(playerQuery || (user?.id ? user.name : ""));
  const { playerSearchOptions } = usePlayerSearchData(player);

  const handleSearch = useFormSubmit();

  return (
    <ClassicPage
      title={t("Time_trial_leaderboard")}
      className={styles.Leaderboard}
      page="game"
    >
      <Head>
        <link rel="stylesheet" type="text/css" href="/styles/classement.css" />
      </Head>
      <h1>{t("Global_Time_trial_leaderboard")}</h1>
      <p dangerouslySetInnerHTML={{
        __html: t("Leaderboard_tt_explain")
      }} />
      <Ad width={728} height={90} bannerId="4919860724" />
    	<div className={styles["ranking-modes-ctn"]}>
        <div>
          <span>{t("Class")}</span>
          <div className={styles["ranking-modes"]}>
            {(cc != 150) ? (
              <>
                <Link href="/leaderboard/tt/150">150cc</Link>
                <span>200cc</span>
              </>
            ) : (
              <>
                <span>150cc</span>
                <Link href="/leaderboard/tt/200">200cc</Link>
              </>
            )}
          </div>
        </div>
      </div>
      <form method="post" name="player" action={`/leaderboard/tt/${cc}`} onSubmit={handleSearch}>
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
            <td className={styles["cell-xs"]}>{t("Details")}</td>
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
              <td className={styles["cell-auto"]} title={t("See_records")}><a href={`classement.php?user=${record.id}&amp;cc=${cc}&amp;pts`}><img src={detailsIcon.src} className={styles.details} alt="Preview" /></a></td>
            </tr>
          ))}
          <tr>
            <td colSpan={4} id={styles.page}>
              {playerQuery ? <CurrentPage urlPrefix={`/leaderboard/tt/${cc}`} page={Math.ceil(recordsPayload.data[0]?.rank / resPerPage)} onSetPage={setCurrentPage} /> : <Pager
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
        <a href={`/classement.php?cc=${cc}`}>
          {t("Ranking_by_circuit")}
        </a>
        <br />
        <Link href="/"><a className={styles.retour}>{t("common:Back_to_mario_kart_pc")}</a></Link>
      </p>
    </ClassicPage>
  );
};

function useLeaderboardData(cc: number, paging: any, player?: string) {  
  const { data: recordsPayload, loading: recordsLoading } = useSmoothFetch(
    "/api/time-trial/leaderboard",
    {
      placeholder: () => ({
        data: Placeholder.array(player ? 1 : 20, (id) => ({
          id,
          name: Placeholder.text(10, 20),
          score: Placeholder.number(1000, 20000),
          rank: id,
          country: null as { code: string } | null,
        })),
        count: 1,
      }),
      requestOptions: postData({
        name: player,
        cc,
        paging
      }),
      reloadDeps: [cc, paging, player],
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

export default TTLeaderboard;
