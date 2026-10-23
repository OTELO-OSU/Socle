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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AgentService
{

    private const AGENT_PAR_PAGE = 20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MailerService $mailerService,
        private readonly Security $security
    ) {}

    public function getAgentParPage(int $page, ?string $search = null): array
    {
        if ($this->security->getUser() === null) {
            throw new UnauthorizedHttpException(
                'Bearer',
                'Vous devez être authentifié.'
            );
        }

        if (!$this->security->isGranted(UtilisateurVoter::LISTER, Utilisateur::class)) {
            throw new AccessDeniedHttpException(
                "Vous n'avez pas les droits nécessaires."
            );
        }

        if ($page < 1) {
            throw new BadRequestHttpException(
                'Le numéro de page doit être supérieur ou égal à 1.'
            );
        }

        $search = trim($search ?? '');
        $offset = ($page - 1) * self::AGENT_PAR_PAGE;

        $conn = $this->em->getConnection();

        // Construction des critères communs aux deux requêtes
        $applyFilters = static function ($qb) use ($search): void {
            $qb->andWhere(
                'NOT EXISTS (
                SELECT 1
                FROM utilisateur_machine um
                WHERE um.utilisateur_id = u.id
            )'
            );

            if ($search !== '') {
                $qb->andWhere('(
                u.prenom ILIKE :search
                OR u.nom ILIKE :search
                OR u.email ILIKE :search
            )');

                $qb->setParameter('search', '%' . $search . '%');
            }
        };

        // Récupération des identifiants paginés
        $qb = $conn->createQueryBuilder();

        $qb
            ->select('u.id')
            ->from('utilisateur', 'u');

        $applyFilters($qb);

        $qb
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->addOrderBy('u.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::AGENT_PAR_PAGE);

        $ids = array_column(
            $qb->executeQuery()->fetchAllAssociative(),
            'id'
        );

        // Récupération des entités Doctrine
        $agents = [];

        if ($ids !== []) {
            $agentsTrouves = $this->em
                ->getRepository(Utilisateur::class)
                ->findBy(['id' => $ids]);

            // Réindexation par identifiant pour conserver l'ordre SQL
            $agentsParId = [];

            foreach ($agentsTrouves as $agent) {
                $agentsParId[(string) $agent->getId()] = $agent;
            }

            foreach ($ids as $id) {
                $key = (string) $id;

                if (isset($agentsParId[$key])) {
                    $agents[] = $agentsParId[$key];
                }
            }
        }

        // Comptage total avec les mêmes critères
        $countQb = $conn->createQueryBuilder();

        $countQb
            ->select('COUNT(u.id)')
            ->from('utilisateur', 'u');

        $applyFilters($countQb);

        $total = (int) $countQb
            ->executeQuery()
            ->fetchOne();

        return [
            'agents' => $agents,
            'page' => $page,
            'pages_total' => (int) ceil($total / self::AGENT_PAR_PAGE),
            'total' => $total,
        ];
    }

    function creationUtilisateurParResponsable(string $nom, string $prenom, string $email, int $employeur, int $unite, ?int $corps, ?int $grade, int $typeContrat, bool $doctorant, ?int $universiteRattachement, ?array $baps_ids, ?array $emploi_types_ids, ?int $cnu, ?int $section): Utilisateur
    {
        if (!$this->security->isGranted("SOUMETTRE_AU_DU", Utilisateur::class)) {
            throw new AccessDeniedException("Vous n'avez pas les droits nécessaires.");
        }

        // On vérifie l'unité
        $u = $this->em->getRepository(Entite::class)->find($unite);
        if (!$u || !$u->isLaboratoire()) {
            throw new NotFoundHttpException("L'unité spécifiée n'existe pas");
        }

        if (!$this->security->isGranted("SOUMETTRE_AU_DU_DANS_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            /** @var Utilisateur $sender */
            $sender = $this->security->getUser();
            $uSender = $sender->getUnite();
            if ($uSender != $u) {
                throw new AccessDeniedException("Vous n'avez pas les droits nécessaires pour créer un utilisateur dans cette unité.", 403);
            }
        }

        // Vérification de l'employeur
        $employeurEntity = $this->em->getRepository(Entite::class)->find($employeur);
        if (!$employeurEntity || !$employeurEntity->isEmployeur()) {
            throw new NotFoundHttpException("L'employeur spécifié n'existe pas.");
        }

        // Vérification du type de contrat
        /** @var TypeContrat $typeContratEntity */
        $typeContratEntity = $this->em->getRepository(TypeContrat::class)->find($typeContrat);
        if (!$typeContratEntity) {
            throw new NotFoundHttpException("Le type de contrat n'existe pas.");
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
                throw new BadRequestHttpException("Le corps est obligatoire.");
            } else if ($grade === null) {
                throw new BadRequestHttpException("Le grade est obligatoire.");
            }

            // Vérification du corps
            /** @var Corps $corpsEntity */
            $corpsEntity = $this->em->getRepository(Corps::class)->find($corps);
            if (!$corpsEntity) {
                throw new NotFoundHttpException("Le corps spécifié n'existe pas.");
            }

            // Vérfication du grade
            /** @var Grade $gradeEntity */
            $gradeEntity = $this->em->getRepository(Grade::class)->find($grade);
            if (!$gradeEntity) {
                throw new NotFoundHttpException("Le grade spécifié n'existe pas.");
            }

            // Vérification de la liaison corps_grade
            if (!$corpsEntity->getGrades()->contains($gradeEntity)) {
                throw new BadRequestHttpException("Le grade spécifié n'est pas associé au corps sélectionné.");
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
                    throw new NotFoundHttpException("L'université de rattachement spécifiée n'existe pas.");
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
            throw new NotFoundHttpException("L'utilisateur n'existe pas.");
        }

        if (!$this->security->isGranted(UtilisateurVoter::MODIFIER, $user)) {
            throw new AccessDeniedException("Vous n'avez pas les droits nécessaires.");
        }

        // On vérifie l'unité
        $u = $this->em->getRepository(Entite::class)->find($unite);
        if (!$u || !$u->isLaboratoire()) {
            throw new NotFoundHttpException("L'unité spécifiée n'existe pas.");
        }

        // Vérification de l'employeur
        $employeurEntity = $this->em->getRepository(Entite::class)->find($employeur);
        if (!$employeurEntity || !$employeurEntity->isEmployeur()) {
            throw new NotFoundHttpException("L'employeur spécifié n'existe pas.");
        }

        // Vérification du type de contrat
        /** @var TypeContrat $typeContratEntity */
        $typeContratEntity = $this->em->getRepository(TypeContrat::class)->find($typeContrat);
        if (!$typeContratEntity) {
            throw new NotFoundHttpException("Le type de contrat n'existe pas.");
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
                throw new BadRequestHttpException("Le corps est obligatoire.");
            } else if ($grade === null) {
                throw new BadRequestHttpException("Le grade est obligatoire.");
            }

            // Vérification du corps
            /** @var Corps $corpsEntity */
            $corpsEntity = $this->em->getRepository(Corps::class)->find($corps);
            if (!$corpsEntity) {
                throw new NotFoundHttpException("Le corps spécifié n'existe pas.");
            }

            // Vérfication du grade
            /** @var Grade $gradeEntity */
            $gradeEntity = $this->em->getRepository(Grade::class)->find($grade);
            if (!$gradeEntity) {
                throw new NotFoundHttpException("Le grade spécifié n'existe pas.");
            }

            // Vérification de la liaison corps_grade
            if (!$corpsEntity->getGrades()->contains($gradeEntity)) {
                throw new BadRequestHttpException("Le grade spécifié n'est pas associé au corps sélectionné.");
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
                    throw new NotFoundHttpException("L'université de rattachement spécifiée n'existe pas.");
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
            throw new AccessDeniedHttpException("Vous n'avez pas les droits nécessaires.");
        }

        $agents = [];

        if ($this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agents = $this->em->getRepository(Utilisateur::class)->findBy(['attenteApprobationDU' => true]);
        } else {
            $unites = [];
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $user->getEntites()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            $user->getDeleguesDirecteur()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            $agents = $this->em->getRepository(Utilisateur::class)->findBy(['attenteApprobationDU' => true, 'unite' => $unites]);
        }

        return $agents;
    }

    function approuveUtilisateurEnAttenteDeValidationDuDu(int $utilisateurId): void
    {
        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU", Utilisateur::class)) {
            throw new AccessDeniedHttpException("Vous n'avez pas les droits nécessaires.");
        }

        /** @var Utilisateur $agent */
        $agent = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$agent || !$agent->isAttenteApprobationDU()) {
            throw new NotFoundHttpException("L'utilisateur spécifié n'existe pas.");
        }

        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agentUnite = $agent->getUnite();
            $unites = [];
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $user->getEntites()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            $user->getDeleguesDirecteur()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            if (!in_array($agentUnite, $unites)) {
                throw new AccessDeniedHttpException("Vous n'avez pas les droits nécessaires pour approuver cet utilisateur.");
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
            throw new AccessDeniedHttpException("Vous n'avez pas les droits nécessaires.");
        }

        /** @var Utilisateur $agent */
        $agent = $this->em->getRepository(Utilisateur::class)->find($utilisateurId);
        if (!$agent || !$agent->isAttenteApprobationDU()) {
            throw new NotFoundHttpException("L'utilisateur spécifié n'existe pas.");
        }

        if (!$this->security->isGranted("GERER_EN_ATTENTE_DE_VALIDATION_DU_DU_N_IMPORTE_QUELLE_UNITE", Utilisateur::class)) {
            $agentUnite = $agent->getUnite();
            $unites = [];
            /** @var Utilisateur $user */
            $user = $this->security->getUser();
            $user->getEntites()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            $user->getDeleguesDirecteur()->map(function (Entite $entite) use (&$unites) {
                $unites[] = $entite;
            });
            if (!in_array($agentUnite, $unites)) {
                throw new AccessDeniedHttpException("Vous n'avez pas les droits nécessaires pour refuser cet utilisateur.");
            }
        }

        $this->em->remove($agent);
        $this->em->flush();
    }
}
