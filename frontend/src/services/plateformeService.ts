import { symfony } from "../utils/axios";

export type RetourFetchPlateformeParPage = {
    plateformes: PlateformeLight[];
    page: number;
    pages_total: number;
    total: number;
}

export type PlateformeLight = {
    id: number;
    pseudo_id: string;
    lastHistorique: DernierHistorique;
}

export type PlateformeCree = {
    message: string;
    plateforme_id: number;
}

export async function fetchPlateformeListeParPage(page: number, recherche: string) {
    const { data } = await symfony.get<RetourFetchPlateformeParPage>('/plateforme', {
        params: {
            page: page,
            search: recherche,
        }
    });

    return data;
}

export async function creerPlateforme(nom: string) {
    const { data } = await symfony.post<PlateformeCree>('/plateforme', {
        nom: nom
    });

    return { message: data.message, plateforme_id: data.plateforme_id };
}

export async function patchResponsablePlateforme(plateformeId: number, responsableScientifiquesIds: number[], responsableTechniquesIds: number[], responsablePlateformeIds: number[]) {
    await symfony.patch(`/plateforme/responsables`, {
        plateformeId: plateformeId,
        responsableScientifiquesIds: responsableScientifiquesIds,
        responsableTechniquesIds: responsableTechniquesIds,
        responsablePlateformeIds: responsablePlateformeIds,
    });
}

export type SourceFinancement = {
    financeur_id: number | null;
    taux: number;
}

export type PosteFutur = {
    employeur_id: number | null;
    corps_id: number | null;
    baps_ids: number[];
    emploi_types_ids: number[];
    quotite: number | null;
    rang_numerateur: number | null;
    rang_denominateur: number | null;
}

export type Publication = {
    id?: number;
    numero: number;
    doi: string;
}

export type Instrument = {
    id: number | string;
    nom: string;
    marque: string | null;
    modele: string | null;
    annee_acquisition?: number | null;
    cout: number | null;
    valeur_remplacement?: number | null;
    duree_utilisation?: number | null;
    operation_id?: number | null;
    annee_achat_envisagee?: number | null;
    futur: boolean;
    source_financement: SourceFinancement[]
    contrat_maintenance_id?: number | null;
    commentaire_nature_contrat?: string | null;
    fournisseur_contrat?: string | null;
    mutualise_autre_instrument?: boolean | null;
    mutualise_autre_instrument_ids?: number[];
    mutualise_umr_osu?: boolean | null;
    mutualise_site?: boolean | null;
    cout_maintenance?: number | null;
}

export type DernierHistorique = {
    id: number;
    annee: number | undefined;
    nom_officiel: string;
    forme_developpee_nom_officiel: string | null;
    site_web: string | null;
    ville_id: number | null;
    logo_url: string | null;
    autre_type_labellisation: string | null;
    laboratoire_operateur_id: number | null;
    tutelles_principales_ids: number[];
    tutelles_secondaires_ids: number[];
    latitude: number | null;
    longitude: number | null;
    espace_bureau: number | null;
    espace_laboratoire: number | null;
    personnels: {
        utilisateur_id: number;
        utilisateur: {
            id: number;
            nom: string;
            prenom: string;
            email: string;
        }
        quotite: number;
        responsable_scientifique: boolean;
        responsable_technique: boolean;
        responsable_plateforme: boolean;
    }[];
    postes_futurs: PosteFutur[];
    instruments: Instrument[];
    materiel_consommable: number | null;
    contrat_entretien: number | null;
    etude_prestation: number | null;
    fluide_energie: number | null;
    mission_frais_generaux: number | null;
    determination_tarif_plateforme_id: number | null;
    contribution_tutelle_taux: number | null;
    contribution_tutelle_detail: SourceFinancement[];
    contribution_fond_public_taux: number | null;
    contribution_fond_prive_taux: number | null;
    contribution_credit_taux: number | null;
    contribution_projet_recherche_taux: number | null;
    contribution_structure_pia_taux: number | null;
    dotation_instrument_national: number | null | undefined;
    labellisation_qualite: boolean | null;
    nom_labellisation_qualite: string | null;
    procedure_transparente_tous: boolean | null;
    procedure_transparente_prestation_exterieure: boolean | null;
    accueil_international_taux: number | null;
    modele_77_2424_taux: number | null;
    modele_77_1224_taux: number | null;
    modele_57_2424_taux: number | null;
    modele_57_1224_taux: number | null;
    utilisation_fonctionnement_taux: number | null;
    utilisation_investissement_taux: number | null;
    utilisation_developpement_taux: number | null;
    utilisation_panne_taux: number | null;
    utilisation_temps_mort_taux: number | null;
    utilisation_equipe_operante_taux: number | null;
    utilisation_collaboration_interne_taux: number | null;
    utilisation_collaboration_externe_taux: number | null;
    utilisation_prestation_service_taux: number | null;
    bges_realise: boolean | null;
    bges_domicile_travail: number | null;
    bges_mission: number | null;
    bges_mission_ext: number | null;
    bges_consommable: number | null;
    bges_equipement: number | null;
    bges_envoi_echantillon: number | null;
    bges_batiment: number | null;
    bges_dechet: number | null;
    publication_equipe_operante: number | null;
    publication_collaboration_interne: number | null;
    publication_collaboration_externe: number | null;
    publication_autre_type: number | null;
    publication_these: number | null;
    publications: Publication[];
}

export type plateformeDetails = {
    id: number;
    instrument_national: boolean;
    pseudo_id: string;
    dernier_historique: DernierHistorique;
}

export async function fetchPlateformeDetails(plateformeId: number) {
    const { data } = await symfony.get<plateformeDetails>(`/plateforme/${plateformeId}`);

    return data;
}

export async function enregistrerPlateformeBrouillon(plateformeId: number, data: DernierHistorique) {
    const { data: { message } } = await symfony.put<{ message: string }>(`/plateforme`, {
        ...data,
        id: plateformeId,
        brouillon: true,
    });

    return { message: message };
}

export async function enregistrerPlateformeValidee(plateformeId: number, data: DernierHistorique) {
    const { data: { message } } = await symfony.put<{ message: string }>(`/plateforme`, {
        ...data,
        id: plateformeId,
        brouillon: false,
    });

    return { message: message };
}

export async function testerPlateforme(plateformeId: number, data: DernierHistorique) {
    const { data: { message } } = await symfony.post<{ message: string }>(`/plateforme/test`, {
        ...data,
        id: plateformeId,
    });

    return { message: message };
}