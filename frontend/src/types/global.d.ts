export { };

declare global {
    interface Window {
        APP_CONFIG: {
            API_URL: string;
            FRONT_URL: string;
        };
    }
}