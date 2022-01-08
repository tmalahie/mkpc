import styles from "./RatingControl.module.scss"
import { useEffect, useMemo, useState } from "react";
import star0 from "../../images/icons/star0.png"
import star1 from "../../images/icons/star1.png"

type Props = {
  defaultValue?: number;
  value?: number;
  onChange?: (value: number) => void;
}
function RatingControl({ value, defaultValue, onChange }: Props) {
  const [rating, setRating] = useState(defaultValue);
  const [previewedRating, setPreviewedRating] = useState<number>();
  useEffect(() => {
    if (value !== undefined)
      setRating(value);
  }, [value]);
  const shownRating = useMemo(() => {
    if (previewedRating !== undefined)
      return previewedRating;
    else
      return rating;
  }, [rating, previewedRating]);

  function handleSetRating(newRating: number) {
    if (newRating === rating)
      newRating = undefined;
    setRating(newRating);
    onChange?.(newRating);
  }

  return <div className={styles.RatingControl}>
    {[...Array(5)].map((_, i) => <img src={i <= shownRating ? star1.src : star0.src} alt="Star" onMouseOver={() => setPreviewedRating(i)} onMouseOut={() => setPreviewedRating(undefined)} onClick={() => handleSetRating(i)} />)}
  </div>
}

export default RatingControl;