const styles: Record<string,string> = new Proxy({}, {
  get: (_target, prop) => prop
});

export default styles;