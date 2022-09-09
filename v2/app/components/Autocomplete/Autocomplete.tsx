import styles from "./Autocomplete.module.scss"
import cx from "classnames";
import { InputHTMLAttributes, useMemo, ChangeEvent, Key } from "react";

type AutocompleteOption<T extends Key> = {
  id: T;
  label: string;
}

interface Props<T extends Key> extends Omit<InputHTMLAttributes<HTMLInputElement>, "onChange" | "onSelect"> {
  inputClassName?: string;
  items: AutocompleteOption<T>[];
  debounce?: number;
  onChange?: (value: string) => void;
  onSelect?: (option: AutocompleteOption<T>, e: ChangeEvent<HTMLInputElement>) => void;
}
function Autocomplete<T extends Key>({ className, inputClassName, type="text", name, items, onChange, onSelect, ...props }: Props<T>) {
  let autocompleteId = useMemo(() => {
    let res = name || "autocomplete";
    //if (typeof document === "undefined") return res;
    //let i = 0;
    //while (document.getElementById(res))
    //  res = `${res}${i++}`;
    return res;
  }, [name]);

  function handleChange(e: ChangeEvent<HTMLInputElement>) {
    const value = e.target.value;
    onChange?.(value);
    if (e.nativeEvent instanceof InputEvent) return;
    let selectedItem = items.find(({ label }) => label === value);
    if (selectedItem) onSelect?.(selectedItem, e);
  }

  return <div className={cx(styles.Autocomplete, className)}>
    <input className={cx(styles.Input, inputClassName)} type={type} name={name} list={autocompleteId} autoComplete="off" onChange={handleChange} {...props} />
    <datalist id={autocompleteId}>
      {items.map(item => <option key={item.id} value={item.label} />)}
    </datalist>
  </div>
}

export default Autocomplete;