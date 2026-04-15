let setTokenExternal: ((token: string) => void) | null = null;
let clearTokenExternal: (() => void) | null = null;

export const registerTokenSetter = (fn: (token: string) => void) => {
  setTokenExternal = fn;
};

export const registerTokenClearer = (fn: () => void) => {
  clearTokenExternal = fn;
};

export const setTokenOutside = (token: string) => {
  setTokenExternal?.(token);
};

export const clearTokenOutside = () => {
  clearTokenExternal?.();
};