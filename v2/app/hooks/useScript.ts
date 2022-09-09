import { useEffect } from 'react';

type ScriptOptions = {
    async?: boolean;
    onload?: () => void;
}
export function insertScript(url, { async, onload }: ScriptOptions = {}) {
    const script = document.createElement('script');

    script.src = url;
    script.onload = onload;
    if (async)
        script.async = true;

    document.body.appendChild(script);

    return () => {
        document.body.removeChild(script);
    }
}

const useScript = (url, options: ScriptOptions = {}) => {
    useEffect(() => {
        insertScript(url, options);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);
};

export default useScript;