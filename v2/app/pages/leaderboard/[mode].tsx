import { NextPage } from "next";
import ClassicPage, {
  commonStyles,
} from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/Leaderboard.module.scss";
import useLanguage from "../../hooks/useLanguage";
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
import Autocomplete from "../../components/Autocomplete/Autocomplete";
import useDebounce from "../../hooks/useDebounce";
import useFormSubmit, { doSubmit } from "../../hooks/useFormSubmit";
import useAuthUser from "../../hooks/useAuthUser";

const localesNs = ["leaderboard", "common"];

enum LeaderboardType {
  VS = "vs",
  BATTLE = "battle",
}
const OnlineLeaderboard: NextPage = () => {
  const language = useLanguage();
  const user = useAuthUser();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const mode = (router.query.mode as LeaderboardType) ?? LeaderboardType.VS;
  const isBattle = mode === LeaderboardType.BATTLE;

  const { paging, currentPage, setCurrentPage, resPerPage } = usePaging();

  const playerQuery = router.query.player?.toString();
  const { recordsPayload, recordsLoading } = useLeaderboardData(mode, paging, playerQuery);

  const [player, setPlayer] = useState<string>(playerQuery || (user?.id ? user.name : ""));
  const { playerSearchOptions } = usePlayerSearchData(player);

  const handleSearch = useFormSubmit();

  return (
    <ClassicPage
      title={t("Online_mode_leaderboard")}
      className={styles.Leaderboard}
      page="game"
    >
      <h1>{t("Leaderboard_Mario_Kart_PC")}</h1>
      <div className={styles["ranking-modes"]}>
        {isBattle ? (
          <>
            <Link href="/leaderboard/vs">{t("VS_mode")}</Link>
            <span>{t("Battle_mode")}</span>
          </>
        ) : (
          <>
            <span>{t("VS_mode")}</span>
            <Link href="/leaderboard/battle">{t("Battle_mode")}</Link>
          </>
        )}
      </div>
      <form method="post" name="player" action={`/leaderboard/${mode}`} onSubmit={handleSearch}>
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
      <Ad width={728} height={90} bannerId="4919860724" />
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
              <td>{record.score}</td>
            </tr>
          ))}
          <tr>
            <td colSpan={4} id={styles.page}>
              {playerQuery ? <CurrentPage urlPrefix={`/leaderboard/${mode}`} page={Math.ceil(recordsPayload.data[0]?.rank / resPerPage)} onSetPage={setCurrentPage} /> : <Pager
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
        <a href={`/online.php${isBattle ? "?battle":""}`}>
          {t("Back_to_online_mode")}
        </a>
        <br />
        <Link href="/"><a className={styles.retour}>{t("common:Back_to_mario_kart_pc")}</a></Link>
      </p>
    </ClassicPage>
  );
};

function useLeaderboardData(mode: string, paging: any, player?: string) {  
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
        mode,
        paging
      }),
      reloadDeps: [mode, paging, player],
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

export default OnlineLeaderboard;
