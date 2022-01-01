import styles from "./TrackCreationCard.module.scss"
import useLanguage, { plural } from "../../hooks/useLanguage";

import userIcon from "../../images/icons/user.png"
import commentIcon from "../../images/icons/comment.png"
import previewIcon from "../../images/icons/preview.png"
import Rating from "../Rating/Rating";
import { MouseEvent } from "react";

export type TrackCreation = {
  id: number,
  author: string,
  cicon: string,
  icons: string[],
  href: string,
  isCup: boolean,
  name: string,
  nbComments: number,
  publicationDate: number,
  rating: number,
  nbRatings: number
}

type Props = {
  creation: TrackCreation;
  onPreview: (creation: TrackCreation) => void;
}
function TrackCreationCard({ creation, onPreview }: Props) {
  const language = useLanguage();

  function handlePreview(e: MouseEvent, creation: TrackCreation) {
    e.preventDefault();
    onPreview(creation);
  }

  return <a href={creation.href} title={creation.name} className={styles["circuit-poster"]}
    style={{ backgroundImage: creation.icons ? creation.icons.map(src => `url('images/creation_icons/${src}')`).join(",") : undefined }}>
    <div className={styles["circuit-name"]}>
      <div className={styles["circuit-title"]}>{creation.name || (language ? "Untitled" : "Sans titre")}</div>
      {creation.author && <div className={styles["circuit-author"]}>
        <img src={userIcon.src} alt={language ? "Author" : "Auteur"} />
        <span>{creation.author}</span>
      </div>}
    </div>
    <div className={styles["circuit-rate"]}>
      <Rating rating={creation.rating} nbRatings={creation.nbRatings} />
    </div>
    <div className={styles["circuit-nbcomments"]} title={plural(language ? "%n comment%s" : "%n commentaire%s", creation.nbComments)}>
      <img src={commentIcon.src} alt={language ? "Comments" : "Commentaires"} /><span> {creation.nbComments}</span>
    </div>
    <div className={styles["circuit-preview"]} title={language ? "Preview" : "Aperçu"} onClick={(e) => handlePreview(e, creation)}>
      <img src={previewIcon.src} alt={language ? "Preview" : "Aperçu"} />
    </div>
  </a>
}

export default TrackCreationCard;