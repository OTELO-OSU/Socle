# Dictionnaire

Dans SOCLE le dictionnaire des données est géré en base de données.
En cas d'ajout de modification ou de suppression il faut donc modifier des records de base.

> [!CAUTION]
> Pour supprimer un élément du dictionnaire il faudra d'abord supprimer ou modifier tous les endroits où l'id de l'élément est référencé. L'effet cascade peut être extrêmement large.

## Modifier les laboratoires/employeurs/institutions/source de financement

Les laboratoires, les employeurs, les institutions et les sources de financement sont toutes la table entité. Pour définir le record comme une source de financement il faut l'affilier à une categorie de la table categorie.

### Catégorie source de financement
!['Table catégorie'](./assets/categorie_table.png)

!['Exemple appli source de financement'](./assets/source_financement.png)

Ainsi en donnant un `categorie_id` à un record, on l'ajoute dans les sources de financement et dans la bonne catégorie.

> [!NOTE]
> La catégorie 9 'no-child' permet au record d'être référencé à la racine des sous de financement donc sans catégorie.


!['Table entité'](./assets/entite_table.png)

Les booléans vont servir à identifier ce que sera le record.
- `employeur` = true|false. Est-ce que le record est un employeur ? Si c'est un employeur on voudra également remplir le booléan `universite` pour savoir si le record est une université (utilisé pour les universités de rattachement des doctorants) et le boolean `epic` pour savoir l'étblissement est un epic. Si ça peut également être une source financement on devra renseigner une catégorie dans `categorie_id`. On voudra également avoir le `ror_id` de renseigné.

- `laboratoire` = true|false. Est-ce que le record est un laboratoire ? Si c'est un laboratoire on voudra également remplir le `code_unite` et le `numero_rnsr`.

- `universite` = true|false. Est-ce que le record est une université ?

- `epic` = true|false. Est-ce que le record est un epic.

#### Exemple

CNRS (insitution) :

!['Record CNRS'](./assets/rec_cnrs.png)

Université de Lorraine (université) :

!['Record Université de Lorraine'](./assets/rec_ul.png)

OTELo (laboratoire) :

!['Record OTELo'](./assets/rec_otelo.png)

ADEME (epic) :

!['Record ADEME'](./assets/rec_ademe.png)

## Modifier les baps et emplois types

Les records BAP (`bap`) et les records Emplois Types (`emploi_type`) sont reliés par la table pivot `bap_emploi_type`. Ainsi pour ajouter une bap ou un emploi type il suffit simplement d'ajouter un record dans la table concernée puis de lier cette bap ou cet emploi type à l'autre via leurs id dans la table bap_emploi_type.

Bap :

!['Record BAP'](./assets/rec_bap.png)

Emploi Type :

!['Record Emploi Type'](./assets/rec_emplois_types.png)


bap_emploi_type :

!['Pivot Bap Emploi Type'](./assets/table_et_bap.png)

## Modifier les corps et grades

Les records `corps` et les records `grade` sont reliés par la table pivot `corps_grade`. Ainsi pour ajouter un corps ou un grade il suffit simplement d'ajouter un record dans la table concernée puis de lier cet corps ou cet grade à l'autre via leurs id dans la table corps_grade.

Dans la table corps on retrouve 3 booleans avec un seul devant être à true :
- `compatible_bap` : true si le corps peut être étendu par un corps et un grade
- `compatible_cnu` : true si le corps peut être étendu par un CNU
- `compatible_section` : true si le corps peut être étendu par une section_cnrs


Corps :

!['Record Corps'](./assets/rec_corps.png)

Grade :

!['Record Grade'](./assets/rec_grade.png)


corps_grade :

!['Pivot Cops Grade'](./assets/table_corps_grade.png)

## Modifier les CNU

Pour ajouter ou modifier un CNU il suffit d'ajouter ou modifier un record dans la table `cnu`

!['Record CNU'](./assets/rec_cnu.png)

## Modifier les Sections CNRS

Pour ajouter ou modifier une section cnrs il suffit d'ajouter ou modifier un record dans la table `section_cnrs`

!['Record Section CNRS'](./assets/rec_section_cnrs.png)

## Modifier les contrats de maintenance

Les contrats de maintenance sont également des éléments de dictionnaire modifiable depuis la base. Il s'agit de la colonne de gauche.

!['Contrat de maintenance'](./assets/contrat_maintenance.png)

Pour ajouter ou modifier un type de contrat de maintenance il suffit d'ajouter ou modifier un record dans la table `contrat_maintenance`

!['Record Section CNRS'](./assets/rec_contrat_maintenance.png)

## Modifier les méthodes de determination de tarif de la plateforme

Les méthodes de determination de tarif de la plateforme sont également des éléments de dictionnaire modifiable depuis la base.

!['Methode de determination de plateforme'](./assets/determination_tarif_methode.png)

Pour ajouter ou modifier une méthodes de determination de tarif de la plateforme il suffit d'ajouter ou modifier un record dans la table `determination_tarif_methode`

!['Record Methode de determination de plateforme'](./assets/rec_determination_tarif_methode.png)

## Modifier les types d'opérations

Les types d'opérations sont également des éléments de dictionnaire modifiable depuis la base.

!['Types opérations'](./assets/type_operation.png)

Pour ajouter ou modifier un type d'opération il suffit d'ajouter ou modifier un record dans la table `operation`

!['Record Types opérations'](./assets/rec_type_operation.png)

## Modifier les types de contrat

Les types de contrat sont également des éléments de dictionnaire modifiable depuis la base.

!['Types contrat'](./assets/type_contrat.png)

Pour ajouter ou modifier un type de contrat il suffit d'ajouter ou modifier un record dans la table `type_contrat`

!['Record Types contrat'](./assets/rec_type_contrat.png)

## Modifier les villes

Les villes sont également des éléments de dictionnaire modifiable depuis la base.

!['Ville'](./assets/ville.png)

Pour ajouter ou modifier une ville il suffit d'ajouter ou modifier un record dans la table `ville`

!['Record Ville'](./assets/rec_ville.png)