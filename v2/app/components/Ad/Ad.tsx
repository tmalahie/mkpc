import { useEffect } from "react";
import useScript from "../../hooks/useScript";

type Props = {
  width: number;
  height: number;
  bannerId: string;
}
function Ad(props: Props) {
  useScript('//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js');
  useEffect(() => {
    (window["adsbygoogle"] = window["adsbygoogle"] || []).push({});
  }, []);

  const { width, height, bannerId } = props;

  return <ins className="adsbygoogle"
    style={{ display: "inline-block", width, height }}
    data-ad-client="ca-pub-1340724283777764"
    data-ad-slot={bannerId}
  ></ins>
}

export default Ad;