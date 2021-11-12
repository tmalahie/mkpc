import { language } from "../hooks/useLanguage";

const localeString = language ? "en-US":"fr-FR";

type DateOptions = {
  mode?: "short" | "date" | "time" | "datetime";
  prefix?: boolean;
  pretty?: boolean;
  includeSeconds?: boolean;
  includeYear?: "always" | "never" | "if-old";
  case?: "lowercase" | "uppercase" | "capitalize";
}
// s must be a capitalized string, like "Today" (not "today" or "TODAY")
function handleCase(s: string, options?: DateOptions) {
  switch (options.case) {
    case "capitalize":
      return s;
    case "uppercase":
      return s.toUpperCase();
    default:
      return s.toLowerCase();
  }
}
function sameDay(d1: Date, d2: Date) {
  return d1.getFullYear() === d2.getFullYear() &&
    d1.getMonth() === d2.getMonth() &&
    d1.getDate() === d2.getDate();
}
function isOlderThanOneYear(d1: Date, d2: Date) {
  const yearDiff = d2.getFullYear() - d1.getFullYear();
  const monthDiff = d2.getMonth() - d1.getMonth();
  const dayDiff = d2.getDate() - d1.getDate();
  if (yearDiff > 1)
    return true;
  if (yearDiff === 1 && monthDiff > 0)
    return true;
  if (yearDiff === 1 && monthDiff === 0 && dayDiff > 0)
    return true;
  return false;
}
function formatDay(d: Date, options: DateOptions) {
  const today = new Date();
  if (sameDay(today, d))
    return handleCase(language ? "Today" : "Aujourd'hui", options);
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate()-1);
  if (sameDay(yesterday, d))
    return handleCase(language ? "Yesterday" : "Hier");
  const includeYearOption = options.includeYear || "if-old";
  const includeYear = includeYearOption === "always" || (includeYearOption === "if-old" && isOlderThanOneYear(d, today));
  
  let res = d.toLocaleDateString(localeString, {
    year: includeYear ? "numeric" : undefined,
    month: '2-digit', day: '2-digit'
  });
  if (options.prefix)
    res = handleCase((language ? "On":"Le") + " ", options) + res;
  return res;
}
function formatHour(d: Date, options: DateOptions) {
  let res = d.toLocaleTimeString(localeString, {
    hour: "2-digit",
    minute: "2-digit"
  });
  if (options.prefix)
    res = handleCase(language ? "at":"à", options) + " " + res;
  return res;
}
function formatDate(d: Date | string | number, options: DateOptions = {}) {
  let res = "";
  const date = new Date(d);
  switch (options.mode) {
  case "short":
    if (new Date().getTime()-date.getTime() < 86400000)
      res = formatHour(date, options);
    else
      res = formatDay(date, options);
    break;
  case "date":
    res = formatDay(date, options);
    break;
  case "time":
    res = formatHour(date, options);
    break;
  case "datetime":
    res = formatDay(date, options)+" "+formatHour(date, {...options,case:"lowercase"});
    break;
  }
  return res;
}

export default formatDate;