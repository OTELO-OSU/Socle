<?php

namespace App\Service;

use App\Entity\Bap;
use App\Entity\Cnu;
use App\Entity\Corps;
use App\Entity\EmploiType;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\SectionCnrs;
use App\Entity\TypeContrat;
use App\Entity\Utilisateur;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class AgentService
{

    private const AGENT_PAR_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerService $mailerService,
        private readonly Security $security
    ) {}

    function getAgentParPage(int $page, ?string $search): array
    {
        if ($this->security->getUser() === null) {
            throw new Exception("Vous devez être authentifié.", 401);
        }

        $offset = ($page - 1) * self::AGENT_PAR_PAGE;

        $conn = $this->em->getConnection();

        $qb = $conn->createQueryBuilder();
        $qb
            ->select('u.id')
            ->from('utilisateur', 'u');

        if ($search) {
            $qb->where('(
                        u.prenom ILIKE :search
                        OR u.nom ILIKE :search
                        OR u.email ILIKE :search
            )');
            $qb->setParameter('search', '%' . $search . '%');
        }

        $qb
            ->orderBy('u.nom', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::AGENT_PAR_PAGE);

        $resQb = $qb->executeQuery()->fetchAllAssociative();

        $agents = $this->em->getRepository(Utilisateur::class)->findBy(['id' => array_column($resQb, 'id')]);

        $countQb = $conn->createQueryBuilder();
        $countQb
            ->select('COUNT(DISTINCT u.id)')
            ->from('utilisateur', 'u');

        if ($search) {
            $countQb->where('(
                u.prenom ILIKE :search
                OR u.nom ILIKE :search
                OR u.email ILIKE :search
            )');
            $countQb->setParameter('search', '%' . $search . '%');
        }

        $total = (int) $countQb->executeQuery()->fetchOne();

        return [
            'agents' => $agents,
            'page' => $page,
            'pages_total' => ceil($total / self::AGENT_PAR_PAGE),
            'total' => $total
        ];
    }

    function creationUtilisateurParResponsable(string $nom, string $prenom, string $email, int $employeur, int $unite, ?int $corps, ?int $grade, int $typeContrat, bool $doctorant, ?int $universiteRattachement, ?array $baps_ids, ?array $emploi_types_ids, ?int $cnu, ?int $section): Utilisateur
    {
        if (!$this->security->isGranted("SOUMETTRE_AU_DU", Utilisateur::class)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        // On vérifie l'unité
        $u = $this->em->getRepository(Entite::class)->find($unite);
        if (!$u || !$u->isLaboratoire()) {
            throw new Exception("L'unité spécifiée est invalide.", 400);
        }

        if (!$this->security->isGranted("SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            /** @var Utilisateur $sender */
            $sender = $this->security->getUser();
            $uSender = $sender->getUnite();
            if ($uSender != $u) {
                throw new Exception("Vous n'avez pas les droits nécessaires pour créer un utilisateur dans cette unité.", 403);
            }
        }

        // Vérification de l'employeur
        $employeurEntity = $this->em->getRepository(Entite::class)->find($employeur);
        if (!$employeurEntity || !$employeurEntity->isEmployeur()) {
            throw new Exception("L'employeur spécifié est invalide.", 400);
        }

        // Vérification du type de contrat
        /** @var TypeContrat $typeContratEntity */
        $typeContratEntity = $this->em->getRepository(TypeContrat::class)->find($typeContrat);
        if (!$typeContratEntity) {
            throw new Exception("Le type de contrat spécifié est invalide.", 400);
        }

        $corpsEntity = null;
        $gradeEntity = null;
        $bapsEntity = [];
        $emploiTypesEntity = [];
        $cnuEntity = null;
        $sectionEntity = null;
        $univRattachementEntity = null;

        if (!$doctorant) {
            if ($corps === null) {
                throw new Exception("Le corps est obligatoire.", 400);
            } else if ($grade === null) {
                throw new Exception("Le grade est obligatoire.", 400);
            }

            // Vérification du corps
            /** @var Corps $corpsEntity */
            $corpsEntity = $this->em->getRepository(Corps::class)->find($corps);
            if (!$corpsEntity) {
                throw new Exception("Le corps spécifié est invalide.", 400);
            }

            // Vérfication du grade
            /** @var Grade $gradeEntity */
            $gradeEntity = $this->em->getRepository(Grade::class)->find($grade);
            if (!$gradeEntity) {
                throw new Exception("Le grade spécifié est invalide.", 400);
            }

            // Vérification de la liaison corps_grade
            if (!$corpsEntity->getGrades()->contains($gradeEntity)) {
                throw new Exception("Le grade spécifié n'est pas associé au corps sélectionné.", 400);
            }

            foreach ($baps_ids ?? [] as $baps_id) {
                $bapsEntity[] = $this->em->getRepository(Bap::class)->find($baps_id);
            }

            foreach ($emploi_types_ids ?? [] as $emploi_type_id) {
                $emploiTypesEntity[] = $this->em->getRepository(EmploiType::class)->find($emploi_type_id);
            }

            if ($cnu !== null) {
                $cnuEntity = $this->em->getRepository(Cnu::class)->find($cnu);
            }

            if ($section !== null) {
                $sectionEntity = $this->em->getRepository(SectionCnrs::class)->find($section);
            }
        } else {
            if ($universiteRattachement === null) {
                $univRattachementEntity = null;
            } else {
                // Vérification de l'université de rattachement
                /** @var Entite $univRattachementEntity */
                $univRattachementEntity = $this->em->getRepository(Entite::class)->find($universiteRattachement);
                if (!$univRattachementEntity || !$univRattachementEntity->isUniversite()) {
                    throw new Exception("L'université de rattachement spécifiée est invalide.", 400);
                }
            }
        }

        //* Création du User
        $user = new Utilisateur();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setEmployeur($employeurEntity);
        $user->setUnite($u);
        $user->setCorps($corpsEntity);
        $user->setGrade($gradeEntity);
        $user->setTypeContrat($typeContratEntity);
        $user->setDoctorant($doctorant);
        $user->setUniversiteRattachement($univRattachementEntity);
        foreach ($bapsEntity as $baps) {
            $user->addBap($baps);
        }
        foreach ($emploiTypesEntity as $emploiType) {
            $user->addEmploiType($emploiType);
        }
        $user->setCnu($cnuEntity);
        $user->setSectionCnrs($sectionEntity);
        $user->setAttenteApprobationDU(true);

        $this->em->persist($user);
        $this->em->flush();

        //* Envoyer un mail pour informer le DU
        $duMail = $user->getUnite()->getDirecteur() ? $user->getUnite()->getDirecteur()->getEmail() : null;
        if ($duMail) {
            $this->mailerService->sendEmailToUser(
                $duMail,
                "Nouvelle demande de création d'utilisateur",
                "Un responsable a soumis une demande de création d'utilisateur pour " . $user->getPrenom() . " " . $user->getNom() . ". Veuillez vous connecter à l'application pour approuver ou refuser cette demande."
            );
        }

        return $user;
    }

    function modificationUtilisateur(int $utilisateurId, string $nom, string $prenom, string $email, int $employeur, int $unite, ?int $corps, ?int $grade, int $typeContrat, bool $doctorant, ?int $universiteRattachement, ?array $baps_ids, ?array $emploi_types_ids, ?int $cnu, ?int $section): Utilisateur
    {
        /** @var Utilisateur $user */
        $user = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$user) {
            throw new Exception("L'utilisateur spécifié est invalide.", 400);
        }

        if (!$this->security->isGranted(UtilisateurVoter::MODIFIER, $user)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        // On vérifie l'unité
        $u = $this->em->getRepository(Entite::class)->find($unite);
        if (!$u || !$u->isLaboratoire()) {
            throw new Exception("L'unité spécifiée est invalide.", 400);
        }

        // Vérification de l'employeur
        $employeurEntity = $this->em->getRepository(Entite::class)->find($employeur);
        if (!$employeurEntity || !$employeurEntity->isEmployeur()) {
            throw new Exception("L'employeur spécifié est invalide.", 400);
        }

        // Vérification du type de contrat
        /** @var TypeContrat $typeContratEntity */
        $typeContratEntity = $this->em->getRepository(TypeContrat::class)->find($typeContrat);
        if (!$typeContratEntity) {
            throw new Exception("Le type de contrat spécifié est invalide.", 400);
        }

        $corpsEntity = null;
        $gradeEntity = null;
        $bapsEntity = [];
        $emploiTypesEntity = [];
        $cnuEntity = null;
        $sectionEntity = null;
        $univRattachementEntity = null;

        if (!$doctorant) {
            if ($corps === null) {
                throw new Exception("Le corps est obligatoire.", 400);
            } else if ($grade === null) {
                throw new Exception("Le grade est obligatoire.", 400);
            }

            // Vérification du corps
            /** @var Corps $corpsEntity */
            $corpsEntity = $this->em->getRepository(Corps::class)->find($corps);
            if (!$corpsEntity) {
                throw new Exception("Le corps spécifié est invalide.", 400);
            }

            // Vérfication du grade
            /** @var Grade $gradeEntity */
            $gradeEntity = $this->em->getRepository(Grade::class)->find($grade);
            if (!$gradeEntity) {
                throw new Exception("Le grade spécifié est invalide.", 400);
            }

            // Vérification de la liaison corps_grade
            if (!$corpsEntity->getGrades()->contains($gradeEntity)) {
                throw new Exception("Le grade spécifié n'est pas associé au corps sélectionné.", 400);
            }

            foreach ($baps_ids ?? [] as $baps_id) {
                $bapsEntity[] = $this->em->getRepository(Bap::class)->find($baps_id);
            }

            foreach ($emploi_types_ids ?? [] as $emploi_type_id) {
                $emploiTypesEntity[] = $this->em->getRepository(EmploiType::class)->find($emploi_type_id);
            }

            if ($cnu !== null) {
                $cnuEntity = $this->em->getRepository(Cnu::class)->find($cnu);
            }

            if ($section !== null) {
                $sectionEntity = $this->em->getRepository(SectionCnrs::class)->find($section);
            }
        } else {
            if ($universiteRattachement === null) {
                $univRattachementEntity = null;
            } else {
                // Vérification de l'université de rattachement
                /** @var Entite $univRattachementEntity */
                $univRattachementEntity = $this->em->getRepository(Entite::class)->find($universiteRattachement);
                if (!$univRattachementEntity || !$univRattachementEntity->isUniversite()) {
                    throw new Exception("L'université de rattachement spécifiée est invalide.", 400);
                }
            }
        }

        $user->setNom($nom);
        $user->setPrenom($prenom);

        if ($this->security->isGranted(UtilisateurVoter::MODIFIER_EMAIL, $user)) {
            $user->setEmail($email);
        }

        $user->setEmployeur($employeurEntity);
        $user->setUnite($u);
        $user->setCorps($corpsEntity);
        $user->setGrade($gradeEntity);
        $user->setTypeContrat($typeContratEntity);
        $user->setDoctorant($doctorant);
        $user->setUniversiteRattachement($univRattachementEntity);
        $user->getBaps()->clear();
        foreach ($bapsEntity as $baps) {
            $user->addBap($baps);
        }
        $user->getEmploiTypes()->clear();
        foreach ($emploiTypesEntity as $emploiType) {
            $user->addEmploiType($emploiType);
        }
        $user->setCnu($cnuEntity);
        $user->setSectionCnrs($sectionEntity);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    function getUtilisateurEnAttenteDeValidationDuDu(): array
    {
        if (!$this->security->isGranted(UtilisateurVoter::GERER_EN_ATTENTE_DE_VALIDATION_DU_DU, Utilisateur::class)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        $agents = [];

        if ($this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agents = $this->em->getRepository(Utilisateur::class)->findBy(['attenteApprobationDU' => true]);
        } else {
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $unite = $user->getUnite();
            $agents = $this->em->getRepository(Utilisateur::class)->findBy(['attenteApprobationDU' => true, 'unite' => $unite]);
        }

        return $agents;
    }

    function approuveUtilisateurEnAttenteDeValidationDuDu(int $utilisateurId): void
    {
        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU", Utilisateur::class)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        /** @var Utilisateur $agent */
        $agent = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$agent || !$agent->isAttenteApprobationDU()) {
            throw new Exception("L'utilisateur spécifié est invalide ou n'est pas en attente d'approbation DU.", 400);
        }

        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agentUnite = $agent->getUnite();
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $userUnite = $user->getUnite();
            if ($agentUnite != $userUnite) {
                throw new Exception("Vous n'avez pas les droits nécessaires pour approuver cet utilisateur.", 403);
            }
        }

        $agent->setAttenteApprobationDU(false);
        $agent->setAdmis(true);

        $this->em->persist($agent);
        $this->em->flush();

        $this->mailerService->sendEmailToUser(
            $agent->getEmail(),
            "Votre compte a été créé et approuvé",
            "La création de votre compte a été demandé par un responsable d'une plateforme et a été approuvé par le directeur de l'unité. Vous pouvez désormais vous connecter à l'application avec votre adresse email pour accéder à votre compte."
        );
    }

    function refusUtilisateurEnAttenteDeValidationDuDu(int $utilisateurId): void
    {
        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU", Utilisateur::class)) {
            throw new Exception("Vous n'avez pas les droits nécessaires.", 403);
        }

        /** @var Utilisateur $agent */
        $agent = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$agent || !$agent->isAttenteApprobationDU()) {
            throw new Exception("L'utilisateur spécifié est invalide ou n'est pas en attente d'approbation DU.", 400);
        }

        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agentUnite = $agent->getUnite();
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $userUnite = $user->getUnite();
            if ($agentUnite != $userUnite) {
                throw new Exception("Vous n'avez pas les droits nécessaires pour refuser cet utilisateur.", 403);
            }
        }

        $this->em->remove($agent);
        $this->em->flush();
    }
}
