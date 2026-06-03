<?php

namespace App\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeHistoriquePersonnel;
use App\Entity\Utilisateur;
use App\Security\Voter\PlateformeVoter;
use App\Security\Voter\UtilisateurVoter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;

class OsuService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security
    ) {}

    public function getAdminOsu()
    {
        if (!$this->security->isGranted(UtilisateurVoter::GET_ADMIN_OSU, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $conn = $this->em->getConnection();

        $sqlDirOsu = "SELECT * FROM utilisateur WHERE roles::jsonb @> :role::jsonb";

        $result = $conn->executeQuery($sqlDirOsu, [
            'role' => json_encode(['ROLE_DIR_OSU']),
        ]);

        $dirOsu = $result->fetchAssociative();
        $idDirOsu = $dirOsu ? $dirOsu['id'] : null;

        $sqlDelegueDirOsu = "SELECT * FROM utilisateur WHERE roles::jsonb @> :role::jsonb";

        $result = $conn->executeQuery($sqlDelegueDirOsu, [
            'role' => json_encode(['ROLE_DIR_OSU_DELEGUE']),
        ]);

        $delegueDirOsu = $result->fetchAllAssociative();
        $idDelegueDirOsu = [];
        foreach ($delegueDirOsu as $delegue) {
            $idDelegueDirOsu[] = $delegue['id'];
        }


        $dirOsu = null;
        $dirOsuDelegue = [];
        $entites = $this->em->getRepository(Utilisateur::class)->findBy(['id' => array_merge($idDelegueDirOsu ? [$idDelegueDirOsu] : [], $idDirOsu ? [$idDirOsu] : [])]);
        foreach ($entites as $utilisateur) {
            if (in_array('ROLE_DIR_OSU', $utilisateur->getRoles())) {
                $dirOsu = $utilisateur;
            } else {
                $dirOsuDelegue[] = $utilisateur;
            }
        }
        return [
            'directeur_osu' => $dirOsu,
            'delegue_directeur_osu' => $dirOsuDelegue,
        ];
    }

    public function switchDirOsu(?int $idNewDirOsu)
    {
        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DO, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $newDirOsu = null;

        if ($idNewDirOsu !== null) {
            $newDirOsu = $this->em->getRepository(Utilisateur::class)->find($idNewDirOsu);
            if (!$newDirOsu) throw new Exception("Utilisateur non trouvé.", 404);
        }

        $conn = $this->em->getConnection();
        $sqlCurrentDirOsu = "SELECT * FROM utilisateur WHERE roles::jsonb @> :role::jsonb";
        $result = $conn->executeQuery($sqlCurrentDirOsu, [
            'role' => json_encode(['ROLE_DIR_OSU']),
        ]);
        $currentDirOsu = $result->fetchAllAssociative();

        foreach ($currentDirOsu as $current) {
            $currentDirOsuEntity = $this->em->getRepository(Utilisateur::class)->find($current['id']);
            $currentDirOsuEntity->setRoles(array_diff($currentDirOsuEntity->getRoles(), ['ROLE_DIR_OSU']));

            $this->em->persist($currentDirOsuEntity);
        }

        if ($newDirOsu) {
            $newDirOsu->setRoles(array_diff($newDirOsu->getRoles(), ['ROLE_DIR_OSU_DELEGUE']));
            $newDirOsu->setRoles(array_merge($newDirOsu->getRoles(), ['ROLE_DIR_OSU']));
            $this->em->persist($newDirOsu);
        }

        $this->em->flush();
    }

    public function retirerDelegue(int $idDelegueDirOsu)
    {
        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DO, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $delegueDirOsu = $this->em->getRepository(Utilisateur::class)->find($idDelegueDirOsu);
        if (!$delegueDirOsu) throw new Exception("Utilisateur non trouvé.", 404);

        $delegueDirOsu->setRoles(array_diff($delegueDirOsu->getRoles(), ['ROLE_DIR_OSU_DELEGUE']));
        $this->em->persist($delegueDirOsu);
        $this->em->flush();
    }

    public function ajouterDelegue(int $idDelegueDirOsu)
    {
        if (!$this->security->isGranted(UtilisateurVoter::CHANGER_DELEGUE_DO, Utilisateur::class)) throw new Exception("Vous n'avez pas les droits.", 403);

        $delegueDirOsu = $this->em->getRepository(Utilisateur::class)->find($idDelegueDirOsu);
        if (!$delegueDirOsu) throw new Exception("Utilisateur non trouvé.", 404);

        if (in_array('ROLE_DIR_OSU', $delegueDirOsu->getRoles())) throw new Exception("Cet utilisateur est déjà directeur de l'OSU.", 400);

        $delegueDirOsu->setRoles(array_merge($delegueDirOsu->getRoles(), ['ROLE_DIR_OSU_DELEGUE']));
        $this->em->persist($delegueDirOsu);
        $this->em->flush();
    }
}
