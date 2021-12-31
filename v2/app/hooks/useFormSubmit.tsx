import { useRouter } from "next/router";
import { FormEvent, useCallback } from "react";

function toJson(formData: FormData) {
  let object = {};
  formData.forEach(function (value, key) {
    object[key] = value;
  });
  return object;
}

function useFormSubmit() {
  const router = useRouter();
  return useCallback((e: FormEvent) => {
    const form = e.target;
    if (form instanceof HTMLFormElement) {
      e.preventDefault();
      router.push({
        pathname: form.action,
        query: toJson(new FormData(form))
      });
    }
  }, [router]);
}
export default useFormSubmit;