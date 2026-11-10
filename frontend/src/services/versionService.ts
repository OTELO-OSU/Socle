import { symfony } from "../utils/axios";

export interface VersionInfo {
    current: string;
    latest: string | null;
    updateAvailable: boolean;
}

export const getVersion = async (): Promise<VersionInfo> => {
    const response = await symfony.get<VersionInfo>('/version');
    return response.data;
};