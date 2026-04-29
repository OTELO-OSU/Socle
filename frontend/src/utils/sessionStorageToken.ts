const TOKEN_KEY = "access_token";

export const saveToken = (token: string | null) => {
  if (token) {
    sessionStorage.setItem(TOKEN_KEY, token);
  } else {
    sessionStorage.removeItem(TOKEN_KEY);
  }
};

export const loadToken = (): string | null => {
  return sessionStorage.getItem(TOKEN_KEY);
};