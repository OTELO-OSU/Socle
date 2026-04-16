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

export async function connexionViaOrcid(code: string) {
    const {data, status} = await symfony.post(`/orcid/connexion/${code}`);

    return { data, status };
}

export async function inscription(prenom: string, nom: string, email: string, mdp: string, role: string) {
    let rolesValue;
    if (role === '3') {
        rolesValue = {
            osu_demande: true
        };
    }
    const {data, status} = await symfony.post('/inscription', {
        prenom,
        nom,
        email,
        mdp,
        ...rolesValue
    });

    return { data, status };
}

export async function inscriptionSsMdp(prenom: string, nom: string, email: string, role: string, token: string | null) {
    let rolesValue;
    if (role === '3') {
        rolesValue = {
            osu_demande: true
        };
    }
    const {data, status} = await symfony.post('/inscription?ssmdp=true', {
        prenom,
        nom,
        email,
        token,
        ...rolesValue
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

export async function verifierMail(token: string) {
    const {data, status} = await symfony.post('/verifie-mail', {
        token
    });

    return { data, status };
}
    