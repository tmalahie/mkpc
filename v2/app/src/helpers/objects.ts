export function uniqBy<T>(arr: T[], keyFn: (T) => any) {
  // Filter array so that for each element x, keyFn(x) is unique
  const arrByKey = {};
  const res = [];
  for (const x of arr) {
    const key = keyFn(x);
    if (!arrByKey[key]) {
      arrByKey[key] = x;
      res.push(x);
    }
  }
  return res;
}