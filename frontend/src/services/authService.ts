import { symfony } from "../utils/axios";

export async function connexion(email: string, mdp: string) {
    const {data, status} = await symfony.post('/connexion', {}, {
        auth: {
            username: email,
            password: mdp,
        }
    });

    return { data, status };
}

export async function deconnexion(token: string) {
    const {status} = await symfony.post('/deconnexion', {}, {
        headers: {
            'Authorization': `Bearer ${token}`,
        }
    });

    return { status };
}
    