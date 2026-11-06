<?php

namespace App\Service;

use App\Entity\Parametre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ParametreService
{
    public const NOM_APPLICATION = 'nom_application';
    public const COULEUR_PRINCIPALE = 'couleur_principale';
    public const LOGO_APPLICATION = 'logo_application';
    public const VUE_ACCUEIL = 'vue_accueil';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {}

    public function getApplicationSettings(): array
    {
        $settings = [
            self::NOM_APPLICATION => 'POE',
            self::COULEUR_PRINCIPALE => '#ba272c',
            self::LOGO_APPLICATION => null,
            self::VUE_ACCUEIL => 'carte',
        ];

        foreach ($this->em->getRepository(Parametre::class)->findBy(['cle' => array_keys($settings)]) as $setting) {
            $settings[$setting->getCle()] = $setting->getValeur();
        }

        return $settings;
    }

    public function updateApplicationSettings(string $name, string $primaryColor, string $homeView, ?string $logo): array
    {
        if (!$this->security->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedHttpException('Vous devez être administrateur pour modifier les paramètres.');
        }

        $values = [
            self::NOM_APPLICATION => trim($name),
            self::COULEUR_PRINCIPALE => strtolower(trim($primaryColor)),
            self::VUE_ACCUEIL => $homeView,
        ];
        if ($logo !== null) {
            $values[self::LOGO_APPLICATION] = $logo;
        }

        foreach ($values as $key => $value) {
            $setting = $this->em->getRepository(Parametre::class)->findOneBy(['cle' => $key]) ?? (new Parametre())->setCle($key);
            $setting->setValeur($value);
            $this->em->persist($setting);
        }

        $this->em->flush();

        return $this->getApplicationSettings();
    }
}
