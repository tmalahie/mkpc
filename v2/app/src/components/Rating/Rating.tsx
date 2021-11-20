import "./Rating.css"
import useLanguage, { plural } from "../../hooks/useLanguage";

type Props = {
    rating: number;
    nbRatings: number;
    label?: React.ReactNode;
}
function Rating({rating, nbRatings, label}: Props) {
    const language = useLanguage();

    const lastRating = Math.floor(rating);
    const nextRating = Math.ceil(rating);
    const ratingTitle = nbRatings ? (+rating.toFixed(2)+'/5 ' + (language ? 'on':'sur') +' '+ plural("%n vote%s", nbRatings)) : (language ? 'Unrated':'Non noté');
    const rest = rating-lastRating;
    const restW = 3+Math.round(9*rest);

    return <table title={ratingTitle}>
    <tbody>
      <tr>
        {Object.keys([...Array(lastRating)]).map((i) => <td key={i} className="star1"></td>)}
        {(rest>0) && <>
          <td className="startStar" style={{width: restW}}></td>
          <td className="endStar" style={{width: 15-restW}}></td>
        </>}
        {Object.keys([...Array(5-nextRating)]).map((i) => <td key={i} className="star0"></td>)}
        {label && <td>{label}</td>}
      </tr>
    </tbody>
  </table>;
}

export default Rating;