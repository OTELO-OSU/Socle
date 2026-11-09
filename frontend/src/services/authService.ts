import { symfony } from "../utils/axios";

export async function connexion(email: string, mdp: string) {
    const { data, status } = await symfony.post('/connexion', {}, {
        auth: {
            username: email,
            password: mdp,
        }
    });

    return { data, status };
}

export async function refreshSession() {
    const { data, status } = await symfony.post('/refresh', {}, { withCredentials: true });

    return { data, status };
}

export async function inscription(prenom: string, nom: string, email: string, mdp: string, role: number, unite_id: number, plateforme_id: number | null) {
    let rolesValue;
    if (role === 2) {
        rolesValue = {
            directeur_unite_demande: true
        };
    }
    const { data, status } = await symfony.post('/inscription', {
        prenom,
        nom,
        email,
        mdp,
        unite_id,
        plateforme_demande_id: plateforme_id,
        ...rolesValue
    });

    return { data, status };
}

export async function inscriptionSsMdp(prenom: string, nom: string, email: string, role: number, token: string | null, unite_id: number, plateforme_id: number | null) {
    let rolesValue;
    if (role === 2) {
        rolesValue = {
            directeur_unite_demande: true
        };
    }
    const { data, status } = await symfony.post('/inscription?ssmdp=true', {
        prenom,
        nom,
        email,
        token,
        unite_id,
        plateforme_demande_id: plateforme_id,
        ...rolesValue
    });

    return { data, status };
}

export async function deconnexion(token: string) {
    const { status } = await symfony.post('/deconnexion', {}, {
        headers: {
            'Authorization': `Bearer ${token}`,
        }
    });

    return { status };
}

export async function verifierMail(token: string) {
    const { data, status } = await symfony.post('/verifie-mail', {
        token
    });

    return { data, status };
}

export async function motDePasseOubliePhase1(email: string) {
    const { data } = await symfony.post<{ message: string }>('/mdp-oublie', {
        email
    });

    return data;
}

export async function motDePasseOubliePhase2(token: string, mdp: string) {
    const { data } = await symfony.patch<{ message: string }>('/mdp-oublie', {
        token,
        mdp
    });

    return data;
}