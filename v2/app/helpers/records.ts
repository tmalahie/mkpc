// Format time in ms into a human-readable string
// Convert for example 63437 to 1:03:437
export function formatTime(ms: number) {
  let s = Math.floor(ms / 1000);
  let m = Math.floor(s / 60);
  s = s % 60;
  ms = ms % 1000;
  return `${m.toString()}:${s.toString().padStart(2, '0')}:${ms.toString().padStart(3, '0')}`;
}

// Format rank into an ordinal number
// Convert for example 1 to 1st, 2 to 2nd, 3 to 3rd, 4 to 4th, 5 to 5th, etc.
export function formatRank(language, rank) {
  const hundreds = rank % 100;
  let sPlace;
  if (language) {
    if ((hundreds >= 10) && (hundreds < 20))
      sPlace = "th";
    else {
      switch (rank % 10) {
        case 1:
          sPlace = "st";
          break;
        case 2:
          sPlace = "nd";
          break;
        case 3:
          sPlace = "rd";
          break;
        default:
          sPlace = "th";
      }
    }
  }
  else
    sPlace = ((rank > 1) ? "e" : "er");
  return `${rank}<sup>${sPlace}</sup>`;
}