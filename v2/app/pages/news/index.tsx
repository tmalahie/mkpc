import { NextPage } from "next";
import ClassicPage, { commonStyles } from "../../components/ClassicPage/ClassicPage";
import styles from "../../styles/News.module.scss";
import cx from "classnames";
import Link from "next/link"
import useLanguage from "../../hooks/useLanguage";
import { formatDate } from "../../helpers/dates";
import Ad from "../../components/Ad/Ad";
import WithAppContext from "../../components/WithAppContext/WithAppContext";
import useSmoothFetch, { Placeholder } from "../../hooks/useSmoothFetch";
import Skeleton from "../../components/Skeleton/Skeleton";
import useAuthUser from "../../hooks/useAuthUser";
import { usePaging } from "../../hooks/usePaging";
import { postData } from "../../hooks/useFetch";
import Pager from "../../components/Pager/Pager";
import { useMemo } from "react";
import { useRouter } from "next/router";
import useFormSubmit from "../../hooks/useFormSubmit";

const NewsList: NextPage = () => {
  const language = useLanguage();
  const user = useAuthUser();
  const router = useRouter();
  const handleSearch = useFormSubmit();
  const search = useMemo(() => router.query.search?.toString(), [router.query.search]);

  return (
    <ClassicPage title="News Mario Kart PC" className={styles.News} page="home">
      <h1>{language ? "Mario Kart PC - News list" : "Mario Kart PC - Liste des news"}</h1>
      <p className={styles.pub}>
        <Ad width={728} height={90} bannerId="4919860724" />
      </p>
      <form method="get" action="/news" className={styles["news-search"]} onSubmit={handleSearch}>
        <p>
          <label htmlFor="search-content">
            {language ? 'Search' : 'Recherche '}:{" "}
          </label>
          <input type="text" id={styles["search-content"]} placeholder={language ? 'News title' : 'Titre de la news'} name="search" />
          {" "}
          <input type="submit" value="Ok" className={cx(styles.action_button, commonStyles.action_button)} />
        </p>
      </form>
      <p className={styles.newsButtons}>
        {user && <a href="addNews.php" className={cx(styles.action_button, commonStyles.action_button)}>{language ? 'Add a news' : 'Créer une news'}</a>}
      </p>

      <PublishedNews search={search} />
      {!!user?.id && <MyPendingNews />}
      {user?.roles.publisher && <OtherPendingNews />}

      <p className={styles.newsButtons}>
        {user && <a href="addNews.php" className={cx(styles.action_button, commonStyles.action_button)}>{language ? 'Add a news' : 'Créer une news'}</a>}
        <Link href="/">{language ? "Back to home" : "Retour à l'accueil"}</Link>
      </p>
    </ClassicPage>
  );
}

function newsPlaceholder(count: number) {
  return () => ({
    data: Placeholder.array(count, (id) => ({
      id,
      title: Placeholder.text(25, 45),
      nbComments: Placeholder.number(5, 99),
      publicationDate: Placeholder.date(),
      status: "published",
      category: {
        name: Placeholder.text(5, 10),
        color: "#0000FF"
      },
      author: {
        id: 0,
        name: Placeholder.text(10, 20)
      }
    })),
    count: 0
  });
}
type NewsListProps = {
  search?: string;
}
function PublishedNews({ search }: NewsListProps) {
  const language = useLanguage();
  const { paging, currentPage, setCurrentPage } = usePaging(50);
  const filters = useMemo(() => {
    let res = [{
      key: "status",
      operator: "=",
      value: "accepted"
    }];
    if (search) {
      res.push({
        key: "title",
        operator: "%",
        value: search
      });
    }
    return res;
  }, [search]);

  const { data: newsPayload, loading: newsLoading } = useSmoothFetch("/api/news/find", {
    placeholder: newsPlaceholder(80),
    requestOptions: postData({
      filters,
      sort: {
        key: "publicationDate",
        order: "desc"
      },
      paging
    }),
    reloadDeps: [paging, filters]
  });

  return <Skeleton loading={newsLoading}>
    <table className={cx(styles.listNews, styles.listPublish)}>
      <colgroup>
        <col className={styles["listNews-types"]} />
        <col className={styles["listNews-infos"]} />
        <col className={styles["listNews-author"]} />
        <col className={cx(styles["listNews-nbcoms"], styles["news-nopo"])} />
        <col className={cx(styles["news-nomo"], styles["listNews-publish"])} />
      </colgroup>
      <thead>
        <tr className={styles["listNews-titres"]}>
          <td>{language ? 'Type' : 'Type'}</td>
          <td>{language ? 'Info' : 'Info'}</td>
          <td>{language ? 'Author' : 'Auteur'}</td>
          <td className={styles["news-nopo"]}>{language ? 'Coms nb' : 'Nb coms'}</td>
          <td className={styles["news-nomo"]}>{language ? 'Date' : 'Date'}</td>
        </tr>
      </thead>
      <tbody>
        {newsPayload.data.map((news, i) => <tr key={news.id} className={(i % 2) ? styles.fonce : styles.clair}>
          <td style={{ color: news.category.color }}>{news.category.name}</td>
          <td>
            <a className={styles.fulllink} href={'news.php?id=' + news.id}>{news.title}</a>
          </td>
          <td className={styles["news-publisher"]}>
            {news.author ? <a href={"/profil.php?id=" + news.author.id}>{news.author.name}</a> : <em>{language ? 'Deleted account' : 'Compte supprimé'}</em>}
          </td>
          <td className={styles["news-nopo"]}>{news.nbComments}</td>
          <td className={styles["news-nomo"]}>{formatDate(news.publicationDate, {
            language,
            mode: "datetime",
            prefix: true,
            case: "capitalize",
            includeYear: "always",
            includeSeconds: true
          })}</td>
        </tr>)}
        {!newsPayload.data.length && <tr className={styles.clair}>
          <td colSpan={3}>{language ? 'No result found for this search' : 'Aucun résultat trouvé pour cette recherche'}</td>
          <td className={styles["news-nopo"]}></td>
          <td className={styles["news-nomo"]}></td>
        </tr>}
      </tbody>
    </table>
    <div className={styles.newsPages}>
      <p>
        <Pager page={currentPage} paging={paging} count={newsPayload.count} onSetPage={setCurrentPage} />
      </p>
    </div>
  </Skeleton>
}
function MyPendingNews() {
  const language = useLanguage();
  const user = useAuthUser();

  const { data: newsPayload, loading: newsLoading } = useSmoothFetch("/api/news/find", {
    placeholder: newsPlaceholder(0),
    requestOptions: postData({
      me: true,
      filters: [{
        key: "status",
        operator: "in",
        value: ["pending", "rejected"]
      }],
      sort: {
        key: "id",
        order: "desc"
      }
    })
  });

  return (newsPayload.data.length > 0) && <>
    <h2>{language ? "My pending news" : "Vos news en attente"}</h2>
    <table className={cx(styles.listNews)}>
      <colgroup>
        <col className={styles["listNews-cats"]} />
        <col className={styles["listNews-infos"]} />
        <col className={styles["listNews-dates"]} />
      </colgroup>
      <thead>
        <tr className={styles["listNews-titres"]}>
          <td>{language ? 'Type' : 'Type'}</td>
          <td>{language ? 'Info' : 'Info'}</td>
          <td>{language ? 'Status' : 'Statut'}</td>
        </tr>
      </thead>
      <tbody>
        {newsPayload.data.map((news, i) => <tr key={news.id} className={(i % 2) ? styles.fonce : styles.clair}>
          <td style={{ color: news.category.color }}>{news.category.name}</td>
          <td>
            <a className={styles.fulllink} href={'news.php?id=' + news.id}>{news.title}</a>
          </td>
          <td className={cx(styles["news-status"], styles["news-" + news.status])}>
            <NewsStatus news={news} />
          </td>
        </tr>)}
      </tbody>
    </table>
  </>
}
function OtherPendingNews() {
  const language = useLanguage();
  const user = useAuthUser();

  const { data: newsPayload, loading: newsLoading } = useSmoothFetch("/api/news/find", {
    placeholder: newsPlaceholder(0),
    requestOptions: postData({
      filters: [{
        key: "status",
        operator: "=",
        value: "pending"
      }],
      sort: {
        key: "publication_date"
      }
    })
  });

  return (newsPayload.data.length > 0) ? <>
    <h2 id="pending-news">{language ? "News pending validation" : "News en attente de validation"}</h2>
    <table className={cx(styles.listNews)}>
      <colgroup>
        <col className={styles["listNews-cats"]} />
        <col className={styles["listNews-infos"]} />
        <col className={styles["listNews-writer"]} />
        <col className={cx(styles["listNews-nomo"], styles["listNews-dates"])} />
      </colgroup>
      <thead>
        <tr className={styles["listNews-titres"]}>
          <td>{language ? 'Type' : 'Type'}</td>
          <td>{language ? 'Info' : 'Info'}</td>
          <td>{language ? 'Author' : 'Auteur'}</td>
          <td className={styles["news-nomo"]}>{language ? 'Date' : 'Date'}</td>
        </tr>
      </thead>
      <tbody>
        {newsPayload.data.map((news, i) => <tr key={news.id} className={(i % 2) ? styles.fonce : styles.clair}>
          <td style={{ color: news.category.color }}>{news.category.name}</td>
          <td>
            <a className={styles.fulllink} href={'news.php?id=' + news.id}>{news.title}</a>
          </td>
          <td className={styles["news-publisher"]}>
            {news.author ? <a href={"/profil.php?id=" + news.author.id}>{news.author.name}</a> : <em>{language ? 'Deleted account' : 'Compte supprimé'}</em>}
          </td>
          <td className={styles["news-nomo"]}>{formatDate(news.publicationDate, {
            language,
            mode: "datetime",
            prefix: true,
            case: "capitalize",
            includeYear: "always",
            includeSeconds: true
          })}</td>
        </tr>)}
      </tbody>
    </table>
  </> : <div className={cx(styles.listNews)} id="pending-news" />
}
function NewsStatus({ news }) {
  console.log(news);
  const language = useLanguage();

  switch (news.status) {
    case "pending":
      return <>{language ? 'Waiting for validation' : 'En attente de validation'}</>;
    case "rejected":
      return <>
        {language ? 'Rejected' : 'Refusée'}
        {" "}
        <a className={styles["news-reject-details"]} href={'/news.php?id=' + news.id + '#news-status'}>[?]</a>
      </>;
  }
  return <></>;
}

export default WithAppContext(NewsList);