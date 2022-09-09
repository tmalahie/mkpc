import useSmoothFetch from "./useSmoothFetch";

export type ChallengeDifficulty = {
  level: number,
  name: string,
  color: string,
  reward: number
}
function useChallengeDifficulties() {
  const { data: difficultiesData } = useSmoothFetch<{ data: ChallengeDifficulty[] }>(`/api/getChallengeDifficulties.php`, {
    cacheKey: "useChallengeDifficulties"
  })
  return difficultiesData?.data;
}
export default useChallengeDifficulties;