import { Fragment, MouseEvent, useMemo } from "react";
import { useRouter } from 'next/router'
import Link from "next/link";

type Props = {
  page: number;
  paging: {
    limit: number;
    offset: number;
  }
  count: number;
  label?: string;
  onSetPage?: (page: number, e: MouseEvent) => void;
}
function Pager({ paging, page, count, onSetPage, label = "Page :" }: Props) {
  const router = useRouter()
  const nbPages = useMemo(() => {
    return Math.ceil(count / paging.limit)
  }, [count, paging]);

  const urlPrefix = useMemo(() => {
    const baseUrl = router.asPath.replace(/([?&])page=(.+?)(\?|&|$)/g, "$1");
    const queryChar = baseUrl.lastIndexOf("?");
    if (queryChar === -1)
      return baseUrl + "?";
    if (queryChar === baseUrl.length - 1)
      return baseUrl;
    return baseUrl + "&";
  }, [router.asPath]);

  return <>
    {label}
    {" "}
    {Object.keys([...Array(Math.max(0, page - 1))]).map((i) => <Fragment key={i}><PageLink page={+i + 1} urlPrefix={urlPrefix} onSetPage={onSetPage} />   </Fragment>)}
    {page}
    {Object.keys([...Array(Math.max(0, nbPages - page))]).map((i) => <Fragment key={i}>   <PageLink page={+i + page + 1} urlPrefix={urlPrefix} onSetPage={onSetPage} /></Fragment>)}
  </>;
}
type LinkProps = {
  page: number;
  urlPrefix: string;
  onSetPage?: (page: number, e: MouseEvent) => void;
}
function PageLink({ page, urlPrefix, onSetPage }: LinkProps) {
  return <Link href={`${urlPrefix}page=${page}`}><a onClick={(e) => onSetPage?.(page, e)}>{page}</a></Link>;
}

export default Pager;