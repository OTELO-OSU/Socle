type AuthBridge = {
  getToken?: () => string | null;
  setToken?: (token: string | null) => void;
  clearToken?: () => void;
};

const bridge: AuthBridge = {};

export const registerAuthBridge = (b: AuthBridge) => {
  Object.assign(bridge, b);
};

export const getAuthBridge = () => bridge;