let tokenExternal: string | null = null;
let setTokenExternal: ((token: string | null) => void) | null = null;
let clearTokenExternal: (() => void) | null = null;

export const registerTokenSetter = (fn: (token: string | null) => void) => {
  setTokenExternal = fn;
};

export const registerTokenClearer = (fn: () => void) => {
  clearTokenExternal = fn;
};

export const syncTokenOutside = (token: string | null) => {
  tokenExternal = token;
};

export const getTokenOutside = () => tokenExternal;

export const setTokenOutside = (token: string) => {
  tokenExternal = token;
  setTokenExternal?.(token);
};

export const clearTokenOutside = () => {
  tokenExternal = null;
  clearTokenExternal?.();
};