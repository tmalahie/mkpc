import { useEffect, useRef } from "react";

const useEffectOnUpdate = (callback, dependencies) => {
  const didMount = useRef(false);

  useEffect(() => {
    if (!didMount.current) {
      didMount.current = true;
      return;
    }
    callback(dependencies);
  }, dependencies);
};

export default useEffectOnUpdate;