# Plan de migration de l'architecture PHP

> Feuille de route de la modernisation Joomla 6 / PHP 8.3+. Les anciennes
> versions de Joomla et PHP ne font pas partie du périmètre.

## Objectifs

- Réduire progressivement les façades historiques sans réécriture globale.
- Isoler le rendu, l'accès aux données et la génération JavaScript dans des
  services testables.
- Supprimer les duplications entre les quatre renderers QuickMode.
- Étendre PHPCS et réduire la baseline PHPStan à mesure que les blocs sont
  extraits.
- Préserver les sorties HTML et JavaScript tant qu'un changement de
  comportement n'est pas explicitement demandé et caractérisé.

## Principes de migration

- Extraire une seule responsabilité par lot.
- Ajouter les tests de caractérisation avant l'extraction, ou dans le même
  commit lorsque le déplacement est strictement mécanique.
- Comparer les sorties HTML et JavaScript avec des snapshots lorsque leur
  format exact constitue le contrat.
- Préserver les contrats publics encore appelés par le PHP stocké dans les
  formulaires jusqu'à leur migration explicite.
- Utiliser les API Joomla 6 natives et des types PHP stricts pour tout nouveau
  service.
- Ne pas introduire de fallback, de polyfill, de contrôle de version ou de
  compatibilité avec les anciennes versions de Joomla ou PHP.
- Éviter les reformatages massifs : un bloc historique n'est normalisé qu'une
  fois isolé et couvert.

## État actuel

| Domaine | État | Référence |
|---|---|---|
| Uploads, callbacks, exports et permissions | Modernisés et couverts par lots | Tests de services Site/Admin |
| Renderers QuickMode | Snapshots disponibles pour Classic, Bootstrap, Mobile et OnePage | `tests/Site/Service/Rendering/QuickMode/` |
| Champs cachés QuickMode | Mutualisés entre les quatre renderers | `HiddenFieldTrait` |
| Champs Bootstrap/OnePage | Mutualisation partielle | `BootstrapStyleFieldTrait` |
| `RenderingEngine::header()` | Extrait et couvert | `ProcessorHeaderRenderer` |
| `RenderingEngine::view()` — entrée QuickMode | Gardes, métadonnées, session, mode mobile et choix du renderer couverts | `RenderingEngineViewCharacterizationTest` |
| `RenderingEngine::view()` — enveloppe | Initialisation, fermeture et wrappers extraits et couverts | `RenderingEngine` |
| `RenderingEngine::view()` — pièces | Pièces Before/After, code personnalisé et sorties `bury()` couverts | `RenderingEngine` |
| `RenderingEngine::view()` — scripts | Bibliothèques, callbacks formulaire et `onload` extraits et couverts | `RenderingEngine` |
| `RenderingEngine::view()` — validation | Extensions de fichiers et valeurs CAPTCHA par défaut extraites et couvertes | `RenderingEngine` |
| `RenderingEngine::view()` — CAPTCHA | Sélection `Captcha` / `ReCaptcha` extraite, ordre historique préservé | Commit `4a070774` |
| `RenderingEngine::view()` — Query List | Préparation extraite et variantes par défaut/checkbox/résultat vide couvertes | Commits `4070ec0f`, `b358e7e9` |
| `RenderingEngine::view()` — scripts d'icônes | Extraction committée et trois chemins `bury()` couverts | Commit `0a908143` |
| `RenderingEngine::view()` — callbacks d'éléments | Extraction committée ; ordre `init` / `action` / `validate` et trois arrêts `bury()` couverts | Commit `413cb1cb` |
| `RenderingEngine::view()` — métadonnées classiques | Comptage icônes/infobulles et scan `Static Text/HTML` extraits et couverts | Commit `51e86824` |
| `RenderingEngine::view()` — première boucle classique | Intégration callbacks, scan statique et identifiants draggable couverte | Commit `a254e895` |
| ContentBuilder — valeurs éditables | Générateur indépendant créé, couvert et branché dans `view()` | Commits `f685ff5e`, `c2ae9a76` |
| ContentBuilder — champs non éditables | Générateur indépendant créé, couvert et branché dans `view()` | Commits `8bfd520e`, `21a0a812` |
| PHPCS | Actif sur les services modernes, les builders ContentBuilder et `HiddenFieldTrait` | `phpcs.xml.dist`, commit `2e58c4bb` |
| PHPStan | Niveau 2 sur le composant, avec baseline | `phpstan.neon.dist`, 251 entrées dans la baseline |

## Phase 1 — Terminer la préparation des éléments classiques

### 1.1 Stabiliser l'extraction Query List

État : extraction et couverture committées dans `4070ec0f` et `b358e7e9`.

Déjà couvert :

- colonnes visibles et masquées ;
- pagination personnalisée et taille de page ;
- export de lignes non vides.

La couverture vérifie les modes avec et sans checkbox, la pagination par
défaut ou personnalisée et les résultats vides ou non vides. L'appel à
`bury()` reste dans le flux de `view()` au même endroit qu'avant l'extraction.

Critère de sortie : `view()` délègue toute la préparation d'une ligne
`Query List`, et le JavaScript produit reste identique.

### 1.2 Extraire la préparation des scripts des éléments

État : extraction des scripts d'icônes committée dans `0a908143`, des callbacks
dans `413cb1cb` et des métadonnées/du scan statique dans `51e86824`. Les trois
chemins `bury()` des callbacks et du registre d'icônes sont couverts.

- Ajouter les tests d'intégration des variantes Query List et `bury()` de la
  première boucle.

Critère de sortie : la première boucle sur les éléments ne contient plus de
construction de script directement dans `view()`.

## Phase 2 — Extraire l'édition et l'intégration ContentBuilder

Cette phase doit rester distincte du rendu HTML classique. Elle manipule des
données, construit du JavaScript d'hydratation et dépend des permissions
ContentBuilder.

### 2.1 Chargement d'un enregistrement BreezingForms éditable

État : `EditableRecord` et `EditableRecordLoader` sont extraits et branchés
dans `RenderingEngine::view()` par `89165de7`. Le loader est couvert pour
l'absence de résultat et le chargement des sous-enregistrements ; le contrat
SQL est désormais isolé derrière un service.

- Extraire la recherche du dernier enregistrement de l'utilisateur.
- Isoler les requêtes `#__facileforms_records` et
  `#__facileforms_subrecords` derrière un service dédié.
- Retourner un objet de résultat explicite plutôt que modifier plusieurs
  propriétés de `RenderingEngine` implicitement.
- Caractériser l'absence d'enregistrement, l'enregistrement archivé,
  l'utilisateur invité et l'enregistrement valide.

Critère de sortie : `RenderingEngine` ne construit plus directement les
requêtes de chargement d'un enregistrement éditable.

### 2.2 Génération des valeurs éditables BreezingForms

État : générateur indépendant committé dans `f685ff5e` et branché dans
`RenderingEngine::view()` par `c2ae9a76`.

- Extraire le JavaScript de remplissage des champs simples : texte, textarea,
  nombre, champ caché et calendrier.
- Extraire les stratégies checkbox, groupe de checkbox, radio et select.
- Conserver le nettoyage `InputFilter` et le déclenchement des événements
  `change`.
- Caractériser les valeurs vides, Unicode, HTML nettoyé et valeurs multiples.

Critère de sortie : le bloc `bfLoadEditable()` est produit par un service pur à
partir d'une collection d'entrées.

### 2.3 Génération des valeurs ContentBuilder

État : générateurs indépendants pour valeurs simples/non éditables, committés
dans `f685ff5e`, `8bfd520e`, `c2ae9a76` et `21a0a812`. Les générateurs de
signatures et de contrôles de fichiers sont maintenant extraits et branchés
dans `view()` par `4f475561` et `2c72231e`, avec couverture dédiée.

- Séparer les stratégies par type de champ :
  - valeurs simples et calendriers ;
  - checkbox et groupes de checkbox ;
  - radio et groupes radio ;
  - listes de sélection ;
  - fichiers ;
  - signatures.
- La lecture et l'encodage des signatures restent dans `view()` ; le service
  de génération JavaScript reçoit une image déjà encodée.
- Isoler la présentation des fichiers existants et les cases de suppression.
- Vérifier explicitement les chemins, noms de fichiers et valeurs absentes.
- Caractériser `bfLoadContentBuilderEditable()` avant toute normalisation du
  JavaScript historique.

Critère de sortie : l'hydratation ContentBuilder n'est plus implémentée dans
`view()` et chaque famille de champs possède un test ciblé.

Le générateur de contrôles de fichiers est traité par `2c72231e`. La lecture
du contenu `recValue`, la résolution du nom affiché et la validation QuickMode
restent à caractériser avant une extraction supplémentaire.

La phase 2.3 est donc couverte pour les scripts de signature et de contrôles
de fichiers ; l'intégration complète ContentBuilder reste conditionnée à un
harnais Joomla/ContentBuilder permettant de tester les dépendances runtime.

### 2.4 Champs ContentBuilder non éditables

État : générateur indépendant committé dans `8bfd520e` et branché dans
`RenderingEngine::view()` par `21a0a812`, en respectant le cycle de création de
`bfDeactivateField`.

- Extraire la récupération des identifiants non éditables.
- Extraire le script de désactivation et de masquage des contrôles.
- Couvrir les champs avec contrôle visible, sans contrôle visible et les
  groupes de contrôles.
- Préserver les règles de lecture seule et les suffixes frontend/admin des
  permissions.

Critère de sortie : le script `bfDisableContentBuilderFields()` est construit
et testé indépendamment de `view()`.

## Phase 3 — Découper le rendu HTML classique

Le rendu classique doit être migré après les scripts d'édition afin de réduire
la quantité d'état implicite traversant la grande boucle actuelle.

### 3.1 Créer le contexte de rendu

- Regrouper les dépendances de lecture nécessaires au rendu d'un nœud :
  processeur, ligne, page, valeurs courantes et options d'affichage.
- Éviter un tableau générique non documenté : utiliser un objet typé ou un
  ensemble réduit de paramètres explicites.
- Ne pas déplacer les écritures globales ou les sorties `echo` sans test de
  caractérisation.

### 3.2 Extraire les familles de nœuds

Ordre conseillé, du plus simple au plus risqué :

1. Texte statique, image, icône et infobulle.
2. Champs texte, textarea, nombre et champ caché.
3. Checkbox, radio et listes de sélection.
4. Boutons et navigation entre pages.
5. Uploads, CAPTCHA et signatures.
6. Query List et ses tableaux paginés.

Chaque famille doit disposer d'un snapshot ou d'assertions structurelles avant
son extraction.

### 3.3 Extraire la sélection du renderer QuickMode

- Conserver dans `RenderingEngine` uniquement le choix Classic, Bootstrap,
  Mobile ou OnePage.
- Déplacer la préparation du contexte dans une factory ou un service dédié.
- Conserver le choix de version mobile et le comportement de soumission.

Critère de sortie : `view()` orchestre le rendu mais ne construit plus le HTML
d'un champ ou d'un nœud.

## Phase 4 — Finaliser le formulaire et le cycle de rendu

### 4.1 Scripts post-rendu

État : `PostRenderScriptBuilder` extrait et branché dans `view()` par
`ad9dd75f`, avec couverture des trois fonctions différées et de leur garde
JQuery/`bfToggleFieldsLoaded`.

- Extraire l'appel différé à `bfLoadEditable()`.
- Extraire l'appel différé à `bfLoadContentBuilderEditable()`.
- Extraire l'appel à `bfDisableContentBuilderFields()`.
- Caractériser les variantes avec et sans jQuery et ToggleFields.

### 4.2 Champs techniques et paiement

État : le builder pur du champ `ff_payment_method` est extrait et branché par
`3d45e1e2`. La détection des types de paiement reste dans `view()` et demeure
à caractériser avec les trois providers. Les champs de contexte ContentBuilder
(`cb_form_id`, `cb_record_id`, `cbIsNew`) sont maintenant produits par
`ContentBuilderTechnicalFieldsBuilder`, branché dans les trois modes par
`c53a457e`.

- Extraire la détection PayPal, Sofort et Stripe.
- Extraire le champ caché `ff_payment_method`.
- Extraire les paramètres ContentBuilder transmis au formulaire.
- Extraire les paramètres additionnels et le jeton CSRF Joomla.

### 4.3 Variantes frontend, backend et preview

- Créer une stratégie de finalisation par mode d'exécution.
- Mutualiser les champs cachés réellement identiques.
- Conserver les différences de route, iframe, cible, bordure et template.
- Couvrir chaque mode par un test de sortie complet.

### 4.4 Fermeture et traçage

- Garantir la fermeture des tampons de sortie sur chaque chemin de retour.
- Garantir la restauration du gestionnaire d'erreurs.
- Extraire le vidage du trace buffer.
- Ajouter des tests qui échouent si un tampon ou un gestionnaire reste actif.

Critère de sortie de la phase : `RenderingEngine::view()` devient une méthode
d'orchestration courte, composée d'étapes nommées et testées.

## Phase 5 — Mutualiser les quatre renderers QuickMode

Les snapshots des quatre renderers constituent les filets de sécurité. La
mutualisation peut donc avancer par type de champ, sans modifier les wrappers
propres à chaque thème.

### État de la mutualisation

- `HiddenFieldTrait` : partagé par les quatre renderers.
- `BootstrapStyleFieldTrait` : partagé par Bootstrap et OnePage.
- Classic et Mobile : logique de champs encore largement dupliquée.
- Les quatre classes restent volumineuses, pour un total d'environ 6 900
  lignes avec leurs traits.

### Stratégie cible

- Introduire une stratégie par type ou famille de champs.
- Séparer le HTML du contrôle de son enveloppe de thème.
- Fournir à la stratégie un contexte typé : nom, identifiant, valeur, état
  readonly, événements, tabindex et options spécifiques.
- Laisser chaque renderer construire ses wrappers, classes CSS et disposition.
- Ne pas créer une classe de base massive remplaçant une duplication par une
  nouvelle façade.

### Ordre de migration

1. `bfTextfield` et `bfNumberInput`.
2. `bfTextarea` et compteur de longueur.
3. `bfCheckbox`.
4. `bfSelect`.
5. Groupes checkbox et radio.
6. Boutons et champs cachés restants.
7. Calendriers et calendriers responsives.
8. Uploads, CAPTCHA, signatures et paiements.

Pour chaque type :

1. Vérifier les snapshots des quatre renderers.
2. Ajouter les variantes manquantes : valeur préremplie, readonly, événements
   et attributs optionnels.
3. Extraire la stratégie partagée.
4. Relancer tous les snapshots.
5. Supprimer uniquement la duplication devenue réellement inaccessible.

Critère de sortie : chaque type de champ possède une implémentation commune du
contrôle et uniquement des adaptations d'enveloppe dans les renderers.

## Phase 6 — PHPCS et PHPStan

### 6.1 Étendre PHPCS progressivement

Le périmètre actuel couvre principalement quelques services modernes. Il ne
doit pas être étendu d'un seul coup aux grandes classes historiques.

Ordre conseillé :

1. Nouveaux services extraits de `RenderingEngine`.
2. Services ContentBuilder extraits.
3. Stratégies de champs QuickMode.
4. Traits QuickMode déjà mutualisés, en commençant par `HiddenFieldTrait`.
5. Renderers, un par un après réduction de leur taille.
6. `RenderingEngine` lorsque les blocs historiques principaux ont disparu.

Chaque ajout au périmètre PHPCS doit être accompagné des corrections du seul
groupe concerné, sans reformatage transversal.

### 6.2 Réduire la baseline PHPStan

- Classer les 251 entrées actuelles par fichier et par catégorie.
- Corriger d'abord les erreurs dans les services extraits et les nouveaux DTO.
- Distinguer les défauts des stubs Joomla des erreurs réelles du composant.
- Ne jamais ajouter une entrée de baseline pour un nouveau code.
- Supprimer les entrées devenues obsolètes après chaque lot.
- Passer progressivement du niveau 2 aux niveaux supérieurs une fois les
  groupes principaux nettoyés.

Critère de sortie : aucun nouveau service n'est couvert par la baseline et le
nombre d'entrées diminue à chaque phase de migration.

## Phase 7 — Réduire les façades historiques

Cette phase commence seulement lorsque leurs responsabilités ont été extraites
et que les appels publics nécessaires sont identifiés.

- Maintenir `HTML_facileFormsProcessor` comme façade mince tant que le PHP
  stocké en base l'appelle encore.
- Déplacer les implémentations vers des services injectables.
- Inventorier les méthodes publiques réellement appelées par les formulaires,
  plugins et callbacks.
- Déprécier en interne les méthodes sans appel avant de les supprimer dans un
  lot explicite.
- Ne pas conserver de chemin alternatif pour les anciennes versions de Joomla
  ou PHP.

## Travail en parallèle

| Couloir | Fichiers principaux | Peut avancer avec |
|---|---|---|
| A — `RenderingEngine` | `RenderingEngine.php`, tests de caractérisation de `view()` | Couloirs B et C |
| B — Stratégies QuickMode | `QuickMode/`, snapshots des quatre renderers | Couloirs A et C |
| C — Qualité | `phpcs.xml.dist`, baseline PHPStan, groupes de services déjà extraits | Couloirs A et B |
| D — ContentBuilder | Nouveaux services et tests ContentBuilder | Couloir B, mais coordination requise avec A pour le branchement dans `view()` |

Règles de coordination :

- Une seule personne modifie `RenderingEngine.php` à la fois.
- Les tests partagés ne sont déplacés qu'après intégration des branches en
  cours.
- Chaque couloir produit des commits autonomes et réversibles.
- Le branchement d'un nouveau service dans `view()` appartient au couloir A,
  même si le service a été préparé dans le couloir D.

## Ordre recommandé des prochains lots

1. Harnais ContentBuilder pour le parcours complet des fichiers et des
   signatures, puis validation de la lecture SQL de l'enregistrement.
2. Premier lot Strategy QuickMode : `bfTextfield` et `bfNumberInput`, après
   comparaison des quatre variantes déjà couvertes par snapshots.
3. Extraction des scripts post-rendu et des champs techniques.
4. Rendu HTML classique par famille de nœuds.
5. Extension PHPCS et réduction PHPStan après chaque service stabilisé.

Lots récemment terminés : tests d'intégration des variantes Query List et du
point d'arrêt `bury()` dans la première boucle (`7a19ffeb`), contrôles de
fichiers ContentBuilder (`2c72231e`) et loader d'enregistrement éditable
(`89165de7`).

Les quatre renderers QuickMode ont encore une baseline PHPCS distincte ; le
contrôle direct fait apparaître des violations de formatage héritées. Ce lot
reste séparé de la mutualisation fonctionnelle pour conserver des commits
réversibles.

`ClassicRenderer` a reçu les corrections automatiques PHPCBF et ses trois
erreurs structurelles manuelles dans `e25d501f`; les avertissements de
longueur restent explicitement hors de ce lot.

Les lots 3 à 5 peuvent être préparés en parallèle du lot 6, à condition que le
branchement dans `RenderingEngine.php` soit coordonné.

## Vérification obligatoire par lot

1. Test PHPUnit ciblé du service ou du renderer modifié.
2. `php -l` sur chaque fichier PHP modifié.
3. Suite PHPUnit complète.
4. PHPCS sur le périmètre configuré et sur tout nouveau service.
5. PHPStan ciblé sur le code de production modifié.
6. `git diff --check` avant commit.
7. Vérification des snapshots lorsque du HTML ou du JavaScript est déplacé.
8. Smoke test Joomla lorsque le routage, le conteneur de services, les assets
   ou le cycle complet de soumission sont modifiés.

## Définition de « terminé »

Un lot est terminé lorsque :

- sa responsabilité est nommée et isolée ;
- son comportement historique utile est couvert ;
- les sorties HTML et JavaScript restent stables, sauf changement demandé ;
- aucun nouveau fallback ou code de compatibilité n'est introduit ;
- PHPUnit, PHPCS et PHPStan passent sur le périmètre concerné ;
- la documentation et la baseline sont mises à jour si nécessaire ;
- le commit ne contient aucun changement sans rapport avec le lot.

## Hors périmètre immédiat

- Réécriture globale de `HTML_facileFormsProcessor`.
- Suppression des façades encore appelées par le PHP stocké en base.
- Changement volontaire du JavaScript historique sans caractérisation et
  validation fonctionnelle.
- Refonte visuelle des formulaires pendant la migration d'architecture.
- Compatibilité avec Joomla 5 ou antérieur et PHP 8.2 ou antérieur.
