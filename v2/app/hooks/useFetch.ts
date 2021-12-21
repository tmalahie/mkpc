import { useState, useEffect } from 'react';

function useFetch(input: RequestInfo, options?: RequestInit) {
  const [state, setState] = useState({
    data: null,
    loading: true,
    error: null
  });

  useEffect(() => {
    fetch(input, options)
      .then(res => res.json())
      .then(data => setState({ data, loading: false, error: null }))
      .catch(error => setState({ data: null, loading: false, error }));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [input]);

  return state;
}

function jsonData(method: string, data): RequestInit {
  return {
    method,
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  }
}
export function postData(data: any) {
  return jsonData("POST", data);
}
export function putData(data: any) {
  return jsonData("PUT", data);
}
export function deleteData(data: any) {
  return jsonData("DELETE", data);
}

export default useFetch;