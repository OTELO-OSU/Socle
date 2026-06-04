import { jwtDecode } from "jwt-decode";

export type JwtPayload = {
  id?: number;
  email?: string;
  prenom?: string;
  nom?: string;
  roles?: string[];
  exp?: number;
};

export function decodeToken(token: string | null): JwtPayload | null {
  if (!token) return null;
  try {
    return jwtDecode<JwtPayload>(token);
  } catch {
    return null;
  }
}