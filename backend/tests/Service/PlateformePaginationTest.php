<?php

namespace App\Tests\Service;

use App\Entity\Plateforme;
use App\Entity\PlateformeHistorique;
use App\Entity\PlateformeTags;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\HarvesterService;
use App\Service\PlateformeService;
use App\Service\TagService;
use App\Service\UniteService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlateformePaginationTest extends TestCase
{
    private function service(array $distantes, ?Plateforme $locale = null, array $instancesIndisponibles = []): PlateformeService
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchAllAssociative')->willReturn($locale ? [['plateforme_id' => 42]] : []);
        $query = $this->createStub(QueryBuilder::class);
        foreach (['select', 'from', 'innerJoin', 'leftJoin', 'where', 'andWhere', 'setParameter', 'groupBy', 'orderBy'] as $method) {
            $query->method($method)->willReturnSelf();
        }
        $query->method('executeQuery')->willReturn($result);
        $connection = $this->createStub(Connection::class);
        $connection->method('createQueryBuilder')->willReturn($query);

        $plateformes = $this->createStub(EntityRepository::class);
        $plateformes->method('findBy')->willReturn($locale ? [$locale] : []);
        $associations = $this->createStub(EntityRepository::class);
        $associations->method('findBy')->willReturn([]);
        $utilisateurs = $this->createStub(UtilisateurRepository::class);
        $utilisateurs->method('findUserAyantAccesPlateforme')->willReturn([]);
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);
        $em->method('getRepository')->willReturnCallback(static fn(string $class) => match ($class) {
            Plateforme::class => $plateformes,
            PlateformeTags::class => $associations,
            Utilisateur::class => $utilisateurs,
        });

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn(new Utilisateur());
        $security->method('isGranted')->willReturn(true);
        $tags = $this->createStub(TagService::class);
        $tags->method('getTags')->willReturn([['id' => 1, 'nom' => 'Tag 1'], ['id' => 2, 'nom' => 'Tag 2']]);
        $tags->method('getUtilisateurTags')->willReturn([]);
        $unites = $this->createStub(UniteService::class);
        $unites->method('getUtilisateurUnites')->willReturn([]);
        $harvester = $this->createStub(HarvesterService::class);
        $harvester->method('getPlateformesMoissonnees')->willReturn([
            'plateformes' => $distantes,
            'instances_indisponibles' => $instancesIndisponibles,
        ]);

        return new PlateformeService($em, $tags, $security, $this->createStub(TranslatorInterface::class), $unites, $this->createStub(HttpClientInterface::class), $harvester);
    }

    public function testPaginationTrieLesDeuxSourcesAvantDecoupage(): void
    {
        $locale = new Plateforme();
        $historique = (new PlateformeHistorique())->setAnnee(2026)->setNomOfficiel('Alpha');
        $locale->addPlateformeHistorique($historique);
        $distantes = [];
        for ($i = 21; $i >= 1; --$i) {
            $distantes[] = ['id' => $i, 'instance_host' => 'distant', 'tags_ids' => [1],
                'dernier_historique' => ['nom_officiel' => sprintf('Plateforme %02d', $i)]];
        }
        $service = $this->service($distantes, $locale);
        $page1 = $service->getPlateformeParPage(1, null, null);
        $page2 = $service->getPlateformeParPage(2, null, null);

        self::assertSame(22, $page1['total']);
        self::assertEquals(2, $page1['pages_total']);
        self::assertCount(20, $page1['plateformes']);
        self::assertSame($locale, $page1['plateformes'][0]);
        self::assertSame('Plateforme 19', $page1['plateformes'][19]['dernier_historique']['nom_officiel']);
        self::assertSame(['Plateforme 20', 'Plateforme 21'], array_column(array_column($page2['plateformes'], 'dernier_historique'), 'nom_officiel'));
        self::assertSame(21, $page1['filtres'][0]['nombre_plateformes']);
        self::assertSame([], $page1['instances_indisponibles']);
    }

    public function testRechercheEtTagsSurLesMoissonnees(): void
    {
        $distantes = [
            ['id' => 1, 'instance_host' => 'a', 'tags_ids' => [1],
                'dernier_historique' => ['nom_officiel' => 'Étoile', 'forme_developpee_nom_officiel' => 'Laboratoire Marin',
                    'personnels' => [['responsable_technique' => true, 'utilisateur' => ['nom' => 'Dupont', 'email' => 'test@example.org']]]]],
            ['id' => 2, 'instance_host' => 'b', 'tags_ids' => [2], 'pseudoId' => 'XYZ',
                'dernier_historique' => ['nom_officiel' => 'Autre']],
        ];
        $service = $this->service($distantes);
        foreach (['éTOILE', 'marin', 'dupont', 'example.org'] as $search) {
            self::assertSame(1, $service->getPlateformeParPage(1, $search, [1])['total']);
            self::assertSame(0, $service->getPlateformeParPage(1, $search, [2])['total']);
        }
        self::assertSame(1, $service->getPlateformeParPage(1, 'xyz', [2])['total']);
        self::assertSame([1, 1], array_column($service->getPlateformeParPage(1, null, null)['filtres'], 'nombre_plateformes'));
    }

    public function testListePartielleConserveLesPlateformesEtSignaleLesInstances(): void
    {
        $locale = new Plateforme();
        $locale->addPlateformeHistorique((new PlateformeHistorique())->setAnnee(2026)->setNomOfficiel('Alpha'));
        $ayantAcces = [['id' => 9, 'prenom' => 'Alice', 'nom' => 'Distante', 'email' => 'alice@example.org']];
        $distantes = [['id' => 1, 'instance_host' => 'disponible', 'tags_ids' => [1], 'ayant_acces' => $ayantAcces,
            'dernier_historique' => ['nom_officiel' => 'Beta']]];
        $resultat = $this->service($distantes, $locale, ['indisponible'])->getPlateformeParPage(1, null, null);

        self::assertSame(2, $resultat['total']);
        self::assertSame($locale, $resultat['plateformes'][0]);
        self::assertSame('Beta', $resultat['plateformes'][1]['dernier_historique']['nom_officiel']);
        self::assertSame($ayantAcces, $resultat['plateformes'][1]['ayant_acces']);
        self::assertSame(['indisponible'], $resultat['instances_indisponibles']);
    }
}
