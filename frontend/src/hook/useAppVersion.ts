import { useQuery } from '@tanstack/react-query';
import { getVersion } from '../services/versionService';

export const useAppVersion = () => {
    return useQuery({
        queryKey: ['app-version'],
        queryFn: getVersion,
        staleTime: 60 * 60 * 1000, // 1 heure
        retry: 1,
    });
};