import { useEffect, useMemo, useState } from "react";
import { useRouter } from "next/dist/client/router";

export function usePaging(resPerPage = 20, { defaultPage = -1, count = true } = {}) {
  const router = useRouter();
  function getCurrentPage() {
    if (defaultPage !== -1) return defaultPage;
    return +router.query.page || 1;
  }
  const [currentPage, setCurrentPage] = useState(getCurrentPage());
  useEffect(() => {
    setCurrentPage(getCurrentPage());
  }, [router.query.page, defaultPage]);
  const paging = useMemo(() => ({
    offset: (currentPage - 1) * resPerPage,
    limit: resPerPage,
    count
  }), [currentPage, resPerPage]);

  return { paging, currentPage, setCurrentPage };
}