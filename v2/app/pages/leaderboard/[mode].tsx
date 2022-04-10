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
import { useState } from "react";
import Skeleton from "../../components/Skeleton/Skeleton";
import Link from "next/link";
import Ad from "../../components/Ad/Ad";

const localesNs = ["leaderboard", "common"];

enum LeaderboardType {
  VS = "vs",
  BATTLE = "battle",
}
const OnlineLeaderboard: NextPage = () => {
  const language = useLanguage();
  const { t } = useTranslation(localesNs);
  const router = useRouter();
  const mode = (router.query.mode as LeaderboardType) ?? LeaderboardType.VS;
  const isBattle = mode === LeaderboardType.BATTLE;
  const [player, setPlayer] = useState<string | undefined>(undefined);

  const { paging, currentPage, setCurrentPage, resPerPage } = usePaging();

  const initialRank = 1 + resPerPage*(currentPage-1);

  const scoreKey = `pts_${mode}`;

  const { data: recordsPayload, loading: recordsLoading } = useSmoothFetch(
    `/api/user/find`,
    {
      placeholder: () => ({
        data: Placeholder.array(20, (id) => ({
          id,
          name: Placeholder.text(10, 20),
          pts_vs: Placeholder.number(1000, 20000),
          pts_battle: Placeholder.number(1000, 20000),
          country: null,
        })),
        count: 1,
      }),
      requestOptions: postData({
        filters: [{
          key: "deleted",
          operator: "=",
          value: 0
        }],
        sort: {
          key: scoreKey,
          order: "desc"
        },
        paging
      }),
      reloadDeps: [mode, paging]
    }
  );

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
      <form method="post" action="">
        <blockquote>
          <p>
            <label htmlFor="joueur">
              <strong>{t("See_player_")}</strong>
            </label>{" "}
            <input
              type="text"
              name="player"
              id="joueur"
              value={player}
              onChange={(e) => setPlayer(e.target.value)}
            />{" "}
            <input
              type="submit"
              value={t<string>("common:validate")}
              className={commonStyles.action_button}
            />
          </p>
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
                  __html: formatRank(language, i+initialRank),
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
              <td>{record[scoreKey]}</td>
            </tr>
          ))}
          <tr>
            <td colSpan={4} id={styles.page}>
              <Pager
                page={currentPage}
                paging={paging}
                count={recordsPayload.count}
                onSetPage={setCurrentPage}
              />
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

export const getServerSideProps = withServerSideProps({ localesNs });

export default OnlineLeaderboard;
