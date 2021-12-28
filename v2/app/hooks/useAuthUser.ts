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
let initialAuthUser: AuthUser = {
  id: 0,
  name: "---",
  roles: {}
};
function useAuthUser() {
  const [cookies] = useCookies(["mkp"]);

  const { data, loading } = useSmoothFetch<AuthUser>(`/api/user/me`, {
    cacheKey: "useAuthUser",
    placeholder: () => {
      if (!cookies.mkp) return null;
      return initialAuthUser;
    }
  })

  const [user, setUser] = useState<AuthUser>(data);
  function setAuthUser(authUser: AuthUser) {
    initialAuthUser = authUser;
    setUser(authUser);
  }

  useEffect(() => {
    if (user) {
      if (typeof sessionStorage !== "undefined") {
        const authUser = sessionStorage.getItem("authUser");
        if (authUser) setAuthUser(JSON.parse(authUser));
      }
    }
  }, []);

  useEffect(() => {
    if (loading) return;
    sessionStorage.setItem("authUser", JSON.stringify(data));
    setAuthUser(data);
  }, [data, loading]);

  return user;
}
export default useAuthUser;