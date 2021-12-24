import { useEffect, useState } from "react";
import { useCookies } from "react-cookie";
import useSmoothFetch from "./useSmoothFetch";

type AuthUser = {
  id: number,
  name: string,
  roles: {
    admin?: boolean,
    publisher?: boolean,
    clvalidator?: boolean,
    moderator?: boolean,
    organizer?: boolean,
    manager?: boolean
  }
  banned?: boolean
}
function useAuthUser() {
  const [cookies] = useCookies(["mkp"]);

  const { data, loading } = useSmoothFetch<AuthUser>(`/api/user/me`, {
    cacheKey: "useAuthUser",
    placeholder: () => {
      if (!cookies.mkp) return null;
      return {
        id: 0,
        name: "---",
        roles: {}
      };
    }
  })

  const [user, setUser] = useState<AuthUser>(data);

  useEffect(() => {
    if (user) {
      if (typeof sessionStorage !== "undefined") {
        const authUser = sessionStorage.getItem("authUser");
        if (authUser) setUser(JSON.parse(authUser));
      }
    }
  }, []);

  useEffect(() => {
    if (loading) return;
    sessionStorage.setItem("authUser", JSON.stringify(data));
    setUser(data);
  }, [data, loading]);

  return user;
}
export default useAuthUser;