import type { SnackbarPayload } from "../provider/SnackbarProvider";

let snackbarHandler: ((payload: SnackbarPayload) => void) | null = null;

export const setSnackbarHandler = (
  fn: (payload: SnackbarPayload) => void
) => {
  snackbarHandler = fn;
};

export const showSnackbar = (payload: SnackbarPayload) => {
  snackbarHandler?.(payload);
};