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
  maxInterval?: number;
  onSetPage?: (page: number, e: MouseEvent) => void;
}
function Pager({ paging, page, count, onSetPage, maxInterval = 3, label = "Page :" }: Props) {
  const router = useRouter()
  const nbPages = useMemo(() => {
    return Math.ceil(count / paging.limit)
  }, [count, paging]);
  const blocks = useMemo(() => {
    if (nbPages <= (maxInterval * 2 + 2)) {
      let block: number[] = [];
      for (let i = 1; i <= nbPages; i++)
        block.push(i);
      return [block];
    }
    let res: number[][] = [];
    let block: number[] = [];
    let start = Math.floor(page) - maxInterval;
    if (start <= 1)
      start = 1;
    else {
      block.push(1);
      if (start != 2) {
        res.push(block);
        block = [];
      }
    }
    let end = start + maxInterval * 2;
    if (end > nbPages) {
      end = nbPages;
      start = end - maxInterval * 2;
    }
    for (let i = start; i <= end; i++)
      block.push(i);
    if (end < nbPages) {
      if (end != (nbPages - 1)) {
        res.push(block);
        block = [];
      }
      block.push(nbPages);
      res.push(block);
    }
    else
      res.push(block);
    return res;
  }, [page, nbPages, maxInterval]);

  const urlPrefix = useMemo(() => {
    const baseUrl = router.asPath.replace(/([?&])page=(.+?)(\?|&|$)/g, "$1");
    const queryChar = baseUrl.lastIndexOf("?");
    if (queryChar === -1)
      return baseUrl + "?";
    if (queryChar === baseUrl.length - 1)
      return baseUrl;
    if (baseUrl.charAt(baseUrl.length - 1) === "&")
      return baseUrl;
    return baseUrl + "&";
  }, [router.asPath]);

  return <>
    {label}
    {" "}
    {blocks.map((block, i) => <Fragment key={i}>
      {!!i && <>...    </>}
      <PageBlock block={block} currentPage={page} urlPrefix={urlPrefix} onSetPage={onSetPage} />
    </Fragment>)}
  </>;
}

type PageBlockProps = {
  block: number[];
  currentPage: number;
  urlPrefix: string;
  onSetPage?: (page: number, e: MouseEvent) => void;
}
function PageBlock({ block, currentPage, urlPrefix, onSetPage }: PageBlockProps) {
  return <>{block.map((page) => {
    return <Fragment key={page}>
      {(page === currentPage) ? page : <Link href={`${urlPrefix}page=${page}`}><a onClick={(e) => onSetPage?.(page, e)}>{page}</a></Link>}
      <>   </>
    </Fragment>
  })}</>
}

export default Pager;