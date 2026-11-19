<?php

namespace App\Tests\Service;

use App\Entity\Harvester;
use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeTags;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurMachine;
use App\Entity\UtilisateurTags;
use App\Repository\UtilisateurRepository;
use App\Service\HarvesterService;
use App\Service\PlateformeService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class HarvesterCacheTest extends TestCase
{
    public function testListeMachineContientLesUtilisateursDeInstanceSource(): void
    {
        $machineUser = new Utilisateur();
        $machineUser->addUtilisateurTag(new UtilisateurTags($machineUser, 1));
        $machine = (new UtilisateurMachine())->setUtilisateur($machineUser);
        $machineRepository = $this->createMock(EntityRepository::class);
        $machineRepository->expects($this->exactly(2))->method('findOneBy')
            ->with(['clientId' => 'client', 'clientSecret' => hash('sha256', 'secret')])
            ->willReturn($machine);

        $plateforme = new Plateforme();
        $plateforme->addPlateformeHistorique((new PlateformeHistorique())->setNomOfficiel('Alpha')->setBrouillon(false));
        $ayantAcces = (new Utilisateur())->setPrenom('Alice')->setNom('Distante')->setEmail('alice@example.org');
        $utilisateurs = $this->createMock(UtilisateurRepository::class);
        $utilisateurs->expects($this->once())->method('findUserAyantAccesPlateforme')
            ->with($plateforme)->willReturn([$ayantAcces]);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(static fn(string $class) => match ($class) {
            UtilisateurMachine::class => $machineRepository,
            Utilisateur::class => $utilisateurs,
        });
        $plateformeService = $this->createStub(PlateformeService::class);
        $plateformeService->method('getPlateformeAuxTags')->willReturn([new PlateformeTags($plateforme, 1)]);
        $plateformeService->method('getPlateformePourcentage')->willReturn(50.0);
        $service = new HarvesterService($em, new MockHttpClient(), $this->createStub(Security::class), $plateformeService, $this->createStub(TranslatorInterface::class), new ArrayAdapter());

        $resultat = $service->getPlateformeListePourMachine('client', 'secret');
        self::assertSame([$ayantAcces], $resultat[0]->getAyantAcces());
        self::assertCount(1, $service->getPlateformePubliquePourMachine('client', 'secret'));
    }

    public function testCacheExpireApresUneHeureEtDroitsReevalues(): void
    {
        $harvester = (new Harvester())->setHost('example.org')->setClientId('client')->setClientSecret('secret');
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findAll')->willReturn([$harvester]);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $user = new Utilisateur();
        $tag = new UtilisateurTags($user, 1);
        $user->addUtilisateurTag($tag);
        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);
        $requests = 0;
        $client = new MockHttpClient(function () use (&$requests) {
            ++$requests;
            return new MockResponse(json_encode([
                ['id' => 1, 'tags_ids' => [1], 'ayant_acces' => [['id' => 7, 'prenom' => 'Alice', 'nom' => 'Distante', 'email' => 'alice@example.org']], 'dernier_historique' => ['nom_officiel' => 'Alpha']],
                ['id' => 1, 'tags_ids' => [1], 'dernier_historique' => ['nom_officiel' => 'Alpha']],
                ['id' => 2, 'tags_ids' => [2], 'dernier_historique' => ['nom_officiel' => 'Beta']],
            ], JSON_THROW_ON_ERROR));
        });
        $now = new \DateTimeImmutable('2026-01-01 00:00:00 UTC');
        $clock = $this->createStub(ClockInterface::class);
        $clock->method('now')->willReturnCallback(static function () use (&$now) { return $now; });
        $cache = new ArrayAdapter(clock: $clock);
        $service = new HarvesterService($em, $client, $security, $this->createStub(PlateformeService::class), $this->createStub(TranslatorInterface::class), $cache);

        $liste = $service->getPlateformesMoissonnees();
        self::assertCount(1, $liste['plateformes']);
        self::assertSame([], $liste['instances_indisponibles']);
        self::assertSame('example.org', $liste['plateformes'][0]['instance_host']);
        self::assertSame('alice@example.org', $liste['plateformes'][0]['ayant_acces'][0]['email']);
        $user->removeUtilisateurTag($tag);
        $user->addUtilisateurTag(new UtilisateurTags($user, 2));
        $now = $now->modify('+3599 seconds');
        self::assertSame(2, $service->getPlateformesMoissonnees()['plateformes'][0]['id']);
        self::assertSame(1, $requests);
        $now = $now->modify('+1 second');
        $service->getPlateformesMoissonnees();
        self::assertSame(2, $requests);
    }

    public function testInstancesIndisponiblesRetenteesSansPerdreLesResultatsDisponibles(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findAll')->willReturn([
            (new Harvester())->setHost('erreur.example.org')->setClientId('client')->setClientSecret('secret'),
            (new Harvester())->setHost('ok.example.org')->setClientId('client')->setClientSecret('secret'),
            (new Harvester())->setHost('invalide.example.org')->setClientId('client')->setClientSecret('secret'),
            (new Harvester())->setHost('hors-ligne.example.org')->setClientId('client')->setClientSecret('secret'),
        ]);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);
        $requests = [];
        $client = new MockHttpClient(function (string $method, string $url) use (&$requests) {
            $host = parse_url($url, PHP_URL_HOST);
            $requests[$host] = ($requests[$host] ?? 0) + 1;
            if ($host === 'erreur.example.org' && $requests[$host] === 1) {
                return new MockResponse('', ['http_code' => 503]);
            }
            if ($host === 'invalide.example.org') {
                return new MockResponse('invalid json');
            }
            if ($host === 'hors-ligne.example.org') {
                throw new TransportException('Connexion impossible');
            }
            return new MockResponse(json_encode([
                ['id' => 1, 'tags_ids' => [1], 'dernier_historique' => ['nom_officiel' => 'Alpha']],
            ], JSON_THROW_ON_ERROR));
        });
        $security = $this->createStub(Security::class);
        $user = (new Utilisateur())->setRoles(['ROLE_ADMIN']);
        $security->method('getUser')->willReturn($user);
        $service = new HarvesterService($em, $client, $security, $this->createStub(PlateformeService::class), $this->createStub(TranslatorInterface::class), new ArrayAdapter());
        $premier = $service->getPlateformesMoissonnees();
        self::assertSame(['erreur.example.org', 'invalide.example.org', 'hors-ligne.example.org'], $premier['instances_indisponibles']);
        self::assertSame(['ok.example.org'], array_column($premier['plateformes'], 'instance_host'));

        $second = $service->getPlateformesMoissonnees();
        self::assertSame(['invalide.example.org', 'hors-ligne.example.org'], $second['instances_indisponibles']);
        self::assertSame(['erreur.example.org', 'ok.example.org'], array_column($second['plateformes'], 'instance_host'));
        self::assertSame(['erreur.example.org' => 2, 'ok.example.org' => 1, 'invalide.example.org' => 2, 'hors-ligne.example.org' => 2], $requests);
    }
}
