<?php

namespace App\Service;

use App\Entity\Corps;
use App\Entity\Entite;
use App\Entity\Grade;
use App\Entity\Utilisateur;
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
            ->select('u.id', 'u.nom, u.prenom, u.email')
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
            'agents' => $resQb,
            'page' => $page,
            'pages_total' => ceil($total / self::AGENT_PAR_PAGE),
            'total' => $total
        ];
    }

    function creationUtilisateurParResponsable(string $nom, string $prenom, string $email, int $employeur, int $unite, int $corps, int $grade): Utilisateur
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
        $corpsEntity->getCorpsGrades()->filter(function ($cg) use ($gradeEntity) {
            return $cg->getGrade()->getId() === $gradeEntity->getId();
        })->first() ?? throw new Exception("Le grade spécifié n'est pas associé au corps sélectionné.", 400);

        //* Création du User
        $user = new Utilisateur();
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setEmail($email);
        $user->setEmployeur($employeurEntity);
        $user->setUnite($u);
        $user->setCorps($corpsEntity);
        $user->setGrade($gradeEntity);

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
}
