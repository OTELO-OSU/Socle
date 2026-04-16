let navigateExternal: ((path: string) => void) | null = null;

export const registerNavigator = (fn: ((path: string) => void) | null) => {
  navigateExternal = fn;
};

export const navigateOutside = (path: string) => {
  if (navigateExternal) {
    navigateExternal(path);
    return;
  }

  if (window.location.pathname !== path) {
    window.history.pushState({}, '', path);
    window.dispatchEvent(new PopStateEvent('popstate'));
  }
};
