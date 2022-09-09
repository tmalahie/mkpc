export function buildQuery(params: Record<string, string | number | (string | number)[]>) {
  return Object.entries(params)
    .filter(([_key, value]) => value != null)
    .map(([key, value]) => {
      if (Array.isArray(value)) {
        return value.map((v, i) => `${key}[${i}]=${encodeURIComponent(v)}`).join("&");
      } else {
        return `${key}=${encodeURIComponent(value)}`;
      }
    })
    .join("&");
}