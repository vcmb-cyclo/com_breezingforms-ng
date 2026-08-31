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
| QuickMode — règles de bascule | Parsing `toggleFields` mutualisé entre les quatre renderers, avec API publique conservée et valeurs multi-mots couvertes | Commit `37c554755` |
| QuickMode — options calendrier | Normalisation des booléens, formats Pickadate, premier jour et années mutualisée entre les quatre renderers | Commit `a2e025609` |
| QuickMode — adaptateurs calendrier | Cinq adaptateurs privés de calendrier mutualisés dans un trait commun aux quatre renderers | Commit `05090635f` |
| QuickMode OnePage — validation précédente | Callback `bf_validate_prevpage` extrait dans un builder pur, avec branches d’erreur et commentaire historique couverts | Commit `f19eb5f89` |
| QuickMode OnePage — validation suivante | Callback `bf_validate_nextpage` extrait dans un builder pur, avec erreur, focus, Ladda et activation de la page couverts | Commit `114dd4d17` |
| QuickMode OnePage — validation soumission | Callback `bf_validate_submit` extrait dans un builder pur, avec erreurs, focus, restauration du bouton et soumission couverts | Commit `280fccd6e` |
| QuickMode OnePage — restauration soumission | Callback `bf_restore_submitbutton` extrait dans un builder pur, avec clonage, remplacement et rebinding Ladda couverts | Commit `bfe93521e` |
| QuickMode — markup d’erreur Bootstrap | Conteneur `bfErrorMessage` partagé entre Bootstrap et OnePage, avec classes de thème injectées et sortie couverte | Commit `9d5a288c2` |
| QuickMode — widget de progression | Markup et script `bfUpdateProgress()` partagés entre Bootstrap et OnePage, avec variante page Thank You couverte | Commit `5cb2da294` |
| QuickMode — configuration runtime des erreurs | Déclaration `bfUseErrorAlerts`/`bfShowDefaultErrors`/`bfErrorPageScoped` partagée, avec portée de page explicite | Commit `45fc45988` |
| QuickMode OnePage — fermeture Thank You | Redirection `bf_remodal_close()` extraite dans un builder pur, avec panier et URL formulaire conservés | Commit `ca2b8f42d` |
| QuickMode OnePage — soumission AJAX | Callback `bf_ajax_submit()` extrait dans un builder pur, avec échec, succès direct et page Thank You couverts | Commit `01ab2d4bd` |
| QuickMode — miniature upload | Callback `bfUploadImageThumb()` partagé entre les quatre renderers, avec chemin Moxie et repli FileReader couverts | Commit `704ccbea2` |
| QuickMode — entrée queue upload | Première boucle `FilesAdded` partagée entre les quatre renderers, avec callback externe et libellé nettoyé couverts | Commit `e6956c7d5` |
| QuickMode — ligne queue upload | Markup de ligne `bfFileQueueItem` mutualisé avec variantes de nom et d’attribut image couvertes | Commit `e081b2f44` |
| QuickMode — progression upload | Callback plupload `UploadProgress` partagé entre les quatre renderers, avec mise à jour de la barre et du pourcentage couverte | Commit `1e28a79b2` |
| QuickMode — fin upload | Callback plupload `FileUploaded` partagé entre les quatre renderers, avec message serveur et nettoyage de ligne couverts | Commit `6d3bfbb27` |
| QuickMode — validation upload | Validation client de taille/extensions et nettoyage des lignes invalides partagé entre les quatre renderers | Commit `fea7df7b6` |
| QuickMode — annulation upload | Annulation, arrêt/redémarrage des uploaders et réactivation du bouton partagés, avec variantes historiques couvertes | Commit `6cf0473ad` |
| QuickMode — expression éditeur | Construction de l’expression JavaScript de lecture des éditeurs mutualisée entre Bootstrap et OnePage, API publique conservée | Commit `43592c8bd` |
| QuickMode — mapping Bootstrap | Mapping Bootstrap 5 des classes mutualisé entre Bootstrap et OnePage, résolution publique `bsClass()` conservée | Commit `658078588` |
| Finalisation — champs de soumission | Champs cachés communs frontend/backend/preview extraits avec conservation des différences `act`/`ff_frame` | Commit `be602b94f` |
| Finalisation — caractérisation des modes | Sorties exactes des champs de soumission frontend, backend et preview verrouillées par tests | Commit `dc73ec0fc` |
| Finalisation — choix mobile | Construction du script et du markup de choix mobile extraite, calcul de l’URL conservé dans l’orchestrateur | Commit `39a4c1da0` |
| Finalisation — wrapper ReCaptcha | Markup du wrapper ReCaptcha historique extrait, activé uniquement pour l’enveloppe legacy et couvert | Commit `092a2a9fa` |
| Finalisation — balise form QuickMode | Assemblage de la balise `<form>` extrait, calcul d’URL conservé dans l’orchestrateur et classe historique préservée | Commit `4f5b8559d` |
| ContentBuilder — wrapper readonly | Enveloppe du script des champs non éditables extraite et couverte, avec marqueurs historiques conservés | Commit `3e1723d15` |
| Finalisation — wrapper enregistrement éditable | Chargement JavaScript de l’enregistrement éditable extrait dans un builder, avec nettoyage historique de `seccode` couvert | Commit `f3b6f8d97` |
| Validation — enveloppe JavaScript | Ouverture/fermeture du script global de validation extraite, contrôles extensions/CAPTCHA conservés dans l’orchestrateur | Commit `9b92acb55` |
| Validation — extensions de fichiers | Génération du validateur JavaScript des uploads extraite dans un builder pur, avec variantes template configuré/non configuré couvertes | Commit `30dd41889` |
| Finalisation — ouverture caractérisée | Initialisation du rendu testée avec remise à zéro des états Query List et wrapper ReCaptcha legacy | Commit `0d33f0253` |
| `RenderingEngine::view()` — validation | Extensions de fichiers, valeurs par défaut et scripts CAPTCHA extraits et couverts | Builders de validation dédiés |
| `RenderingEngine::view()` — CAPTCHA | Sélection `Captcha` / `ReCaptcha`, endpoints site/admin et générateurs JavaScript isolés, ordre historique préservé | Commits `4a070774`, `8e3e9a7a`, `4563ae11`, `d328c4f8`, `75c0ca2b`, `1cea0c84` |
| `RenderingEngine::view()` — Query List | Préparation extraite et variantes par défaut/checkbox/résultat vide couvertes | Commits `4070ec0f`, `b358e7e9` |
| `RenderingEngine::view()` — Query List sélection globale | Callback JavaScript paginé `ff_selectAllQueryRows` extrait et couvert, avec conservation des sélections de page et de la ligne globale | Commit `0b1229d37` |
| `RenderingEngine::view()` — Query List navigation | Markup JavaScript des liens première/précédente/suivante/dernière page extrait et couvert avec libellés injectés | Commit `c93d06eb7` |
| `RenderingEngine::view()` — Query List lignes | Calcul de page et rafraîchissement JavaScript des lignes visibles/masquées extrait et couvert | Commit `7ee50bccc` |
| `RenderingEngine::view()` — Query List fin de pagination | Resynchronisation des checkbox, redimensionnement et défilement extraits et couverts par variantes | Commit `c942cfe90` |
| `RenderingEngine::view()` — Query List état JavaScript | Initialisation ordonnée des tableaux d’état Query List extraite et injection de `qcode` couverte | Commit `70c6aa58b` |
| `RenderingEngine::view()` — hydratation d'un enregistrement éditable | Nettoyage conservé dans l'orchestrateur, génération JavaScript extraite et couverte par famille de contrôle | Commit `a8325a7c` |
| Rendu HTML classique — texte statique, rectangle, image, infobulle et icône | Markup `Static Text/HTML`, `Rectangle`, `Image`, `Tooltip` et `Icon` extrait et couvert sans modifier le HTML produit | Commits `43bc8b9d`, `ec310eabb`, `08f6bc4ee`, `1e23287f8`, `3acfe4d66` |
| Rendu HTML classique — champ caché | Markup `Hidden Input` extrait et couvert avec conservation du nom et de la valeur historiques | Commit `0bacb6fc7` |
| Rendu HTML classique — champ texte | Markup `Text` extrait et couvert pour texte/mot de passe, dimensions, maxlength, événements et états readonly/disabled | Commit `24776d379` |
| Rendu HTML classique — textarea | Markup `Textarea` extrait et couvert pour états, dimensions, événements et ajustement Mozilla des lignes | Commit `593e018b5` |
| Rendu HTML classique — choix élémentaire | Contrôle commun `Checkbox`/`Radio Button` extrait et couvert pour états, classes, labels et événements | Commit `fcc72f2aa` |
| Rendu HTML classique — liste de sélection | Parsing et markup `Select List` extraits et couverts pour options, échappement, sélection, taille et états | Commit `771f3d014` |
| Rendu HTML classique — bouton régulier | Markup `Regular Button` extrait et couvert pour états et événements | Commit `974fcf0db` |
| Rendu HTML classique — bouton graphique | Markup `Graphic Button` extrait et couvert pour les dispositions d’image, états, dimensions et événements | Commit `cf09129e0` |
| Rendu HTML classique — upload | Markup `File Upload` extrait et couvert pour taille, longueur, accept, état disabled et événements | Commit `205127c81` |
| Rendu HTML classique — CAPTCHA | Markup image/saisie/rechargement extrait et couvert pour endpoint, dimensions et mode compressé | Commit `c260c2eb9` |
| Rendu HTML classique — Query List | Interprétation des paramètres de présentation extraite et couverte pour attributs, classes et pagination | Commit `ff720c4dc` |
| Rendu HTML classique — Query List header | Markup des en-têtes, spans, styles et sélecteur de lignes extrait et couvert | Commit `a940525b7` |
| Rendu HTML classique — Query List cellules | Construction d’une cellule extraite et couverte pour alignements, spans, styles et contrôles de sélection | Commit `c047a0779` |
| Rendu HTML classique — Query List lignes | Boucle de ligne extraite, réutilisant le builder de cellule et conservant l’arrêt `dying` | Commit `58dec7178` |
| Rendu HTML classique — Query List pied et pagination | Markup du pied de tableau et liens de pagination extrait, avec variantes de navigation et mode compact couverts | Commit `76351beae` |
| Rendu HTML classique — Query List enveloppe | Ouverture/fermeture du conteneur et du tableau extraite et couverte avec styles, attributs et formatage historiques | Commit `bd59533be` |
| `RenderingEngine::view()` — scripts d'icônes | Extraction committée et trois chemins `bury()` couverts | Commit `0a908143` |
| `RenderingEngine::view()` — callbacks d'éléments | Extraction committée ; ordre `init` / `action` / `validate` et trois arrêts `bury()` couverts | Commit `413cb1cb` |
| `RenderingEngine::view()` — métadonnées classiques | Comptage icônes/infobulles et scan `Static Text/HTML` extraits et couverts | Commit `51e86824` |
| `RenderingEngine::view()` — première boucle classique | Intégration callbacks, scan statique et identifiants draggable couverte | Commit `a254e895` |
| ContentBuilder — valeurs éditables | Générateurs d’hydratation par famille créés, couverts et branchés dans `view()` | Commits `f685ff5e`, `c2ae9a76`, `084fc749`, `e8d531cd`, `152def4c` |
| ContentBuilder — champs non éditables | Générateur indépendant créé, couvert et branché dans `view()` | Commits `8bfd520e`, `21a0a812` |
| ContentBuilder — résolution des signatures | Résolution, lecture et encodage des fichiers isolés et testés | Commits `b31b5c47`, `e9386794` |
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
l'absence de résultat, le chargement des sous-enregistrements et les filtres
SQL critiques via `dc68404bb` et `fd27c67dc` ; le contrat SQL est désormais
isolé derrière un service, y compris la liaison du sous-enregistrement et le
cas utilisateur invité. La génération du JavaScript `bfLoadEditable()` est maintenant
extraite dans `EditableRecordHydrationScriptBuilder` (`a8325a7c`) et couverte
pour les champs simples, checkbox/radio, listes, valeurs vides et types
inconnus. Son enveloppe de chargement et le nettoyage historique de
`ff_nm_seccode[]` sont maintenant isolés dans
`EditableRecordScriptWrapperBuilder` (`f3b6f8d97`) ; le nettoyage
`InputFilter` reste explicitement dans `view()`.

- La recherche du dernier enregistrement, les requêtes
  `#__facileforms_records`/`#__facileforms_subrecords` et le résultat typé sont
  désormais portés par `EditableRecordLoader`.
- Caractériser encore l'enregistrement archivé, l'utilisateur invité et le
  parcours complet dans `view()` lorsque le runtime Joomla est disponible.

Critère de sortie : `RenderingEngine` ne construit plus directement les
requêtes de chargement d'un enregistrement éditable.

### 2.2 Génération des valeurs éditables BreezingForms

État : générateur indépendant committé dans `f685ff5e` et branché dans
`RenderingEngine::view()` par `c2ae9a76`, avec l'enveloppe
`bfLoadEditable()` isolée dans `f3b6f8d97`.

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
- La résolution du fichier existant est isolée dans
  `ContentBuilderSignatureFileResolver` via `b31b5c47`, et l'encodage est
  désormais confié à `ContentBuilderSignatureImageEncoder` via `e9386794`.
  Le service de génération JavaScript reçoit une image déjà encodée.
- L'hydratation JavaScript des valeurs simples et des calendriers est
  désormais isolée dans `ContentBuilderValueHydrationScriptBuilder` via
  `084fc749`, avec conservation du fallback JQuery/native et du délai des
  calendriers.
- L'hydratation des checkbox/radio est isolée dans
  `ContentBuilderChoiceHydrationScriptBuilder` via `e8d531cd`, et celle des
  listes dans `ContentBuilderSelectHydrationScriptBuilder` via `152def4c` ;
  les valeurs multiples, l'échappement JSON et les déclenchements historiques
  sont couverts par des tests unitaires.
- L'enveloppe `bfLoadContentBuilderEditable`, le compteur Flash et le nettoyage
  legacy du champ seccode sont désormais assemblés par
  `ContentBuilderEditableScriptWrapperBuilder` via `ec113799`, avec les scripts
  de validation et d'hydratation injectés sans changement de contrat.
- Isoler la présentation des fichiers existants et les cases de suppression.
- Vérifier explicitement les chemins, noms de fichiers et valeurs absentes.
- Caractériser `bfLoadContentBuilderEditable()` avant toute normalisation du
  JavaScript historique.

Critère de sortie : l'hydratation ContentBuilder n'est plus implémentée dans
`view()` et chaque famille de champs possède un test ciblé.

Le générateur de contrôles de fichiers est traité par `2c72231e`. La lecture
et la normalisation des fins de ligne de `recValue` sont désormais isolées
dans `ContentBuilderFileValueParser` via `b426525c`, avec conservation du
comptage historique testé. La résolution du nom affiché est désormais isolée
dans `ContentBuilderFileDisplayNameBuilder` via `40c7fd89`; l'échappement des
balises inattendues et la restauration des `<br>` sont couverts. La
validation QuickMode est désormais centralisée dans
`ContentBuilderFlashUploadValidationBuilder` via `6d4afd92`, avec émission
unique du callback `ff_flashupload_not_empty`. Les parcours runtime complets
des fichiers restent à éprouver avec un harnais ContentBuilder/Joomla. La
phase de restauration JavaScript des contrôles de fichiers est désormais
isolée dans `ContentBuilderFileHydrationScriptBuilder` via `757f27446`.

La résolution et l'encodage des fichiers de signature couvrent explicitement
les valeurs vides, les fichiers absents et les fichiers présents ; la lecture
et l'encodage sont isolés dans des services unitaires et branchés dans
`RenderingEngine` via `e9386794`. Le parseur de valeurs de fichiers couvre
également les fins de ligne CRLF, les lignes vides et les noms Unicode via
`e8582b59`. L'intégration runtime complète des signatures reste à éprouver
avec un harnais ContentBuilder/Joomla.

Pour le CAPTCHA, la construction des endpoints image, validation legacy et
ReCaptcha est désormais isolée dans `CaptchaEndpointBuilder` via `8e3e9a7a`.
L’URL de base du champ CAPTCHA réutilise également ce service via `4563ae11`,
ce qui supprime la dernière construction directe de cet endpoint dans
`RenderingEngine`.
La sélection du nœud de validation est isolée dans
`CaptchaValidationRowSelector` (`d328c4f8`) : le premier `Captcha` est
prioritaire et le dernier `ReCaptcha` est conservé, comme dans le flux
historique. Les valeurs par défaut sont produites par
`CaptchaValidationDefaultsBuilder` (`41612fa2`). Les deux générateurs de
JavaScript sont désormais extraits dans `CaptchaLegacyValidationScriptBuilder`
et `CaptchaReCaptchaValidationScriptBuilder` (`75c0ca2b`, `1cea0c84`) ; les
variantes et leurs interpolations sont couvertes par les tests unitaires et le
test de caractérisation de `RenderingEngineViewCharacterizationTest`.
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

1. Texte statique, rectangle, image, infobulle et icône
   (`ClassicStaticTextBuilder` extraits par `43bc8b9d`, `ec310eabb`,
   `08f6bc4ee`, `1e23287f8` et `3acfe4d66`) ; le champ caché est isolé par
   `ClassicHiddenInputBuilder` (`0bacb6fc7`). Les champs techniques viennent
   ensuite.
2. Champ texte (`ClassicTextInputBuilder`, `24776d379`) et textarea
   (`ClassicTextareaBuilder`, `593e018b5`), puis nombre et champ caché.
3. Contrôle commun checkbox/radio (`ClassicChoiceBuilder`, `fcc72f2aa`), puis
   liste de sélection (`ClassicSelectBuilder`, `771f3d014`). Les groupes
   restent représentés par les contrôles d’options élémentaires.
4. Bouton régulier (`ClassicRegularButtonBuilder`, `974fcf0db`) et bouton
   graphique (`ClassicGraphicButtonBuilder`, `cf09129e0`), puis navigation
   entre pages.
5. Upload classique (`ClassicFileUploadBuilder`, `205127c81`) et CAPTCHA
   (`ClassicCaptchaBuilder`, `c260c2eb9`), puis paramètres Query List
   (`ClassicQueryListSettingsBuilder`, `ff720c4dc`), en-tête
   (`ClassicQueryListHeaderBuilder`, `a940525b7`), cellules
   (`ClassicQueryListCellBuilder`, `c047a0779`), puis boucle des lignes
   (`ClassicQueryListRowBuilder`, `58dec7178`) et pied/pagination
   (`ClassicQueryListFooterBuilder`, `76351beae`), puis enveloppe du tableau
   (`ClassicQueryListMarkupBuilder`, `bd59533be`).
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

La garde d'entrée de `RenderingEngine::view()` impose désormais le mode
QuickMode avant le chargement des métadonnées ; la condition interne
redondante a été supprimée afin que l'orchestrateur n'entretienne pas un
chemin impossible.

État : `PostRenderScriptBuilder` extrait et branché dans `view()` par
`ad9dd75f`, avec couverture des trois fonctions différées et de leur garde
JQuery/`bfToggleFieldsLoaded`.

- Extraire l'appel différé à `bfLoadEditable()`.
- Extraire l'appel différé à `bfLoadContentBuilderEditable()`.
- Extraire l'appel à `bfDisableContentBuilderFields()`.
- Caractériser les variantes avec et sans jQuery et ToggleFields.

### 4.2 Champs techniques et paiement

État : le builder pur du champ `ff_payment_method` est extrait et branché par
`3d45e1e2`. La détection des types de paiement est désormais isolée dans
`PaymentProviderDetector` et branchée dans `view()` par `b5d1a55e`, avec les
trois providers couverts. Les champs de contexte ContentBuilder
(`cb_form_id`, `cb_record_id`, `cbIsNew`) sont maintenant produits par
`ContentBuilderTechnicalFieldsBuilder`, branché dans les trois modes par
`c53a457e`.

- Détection PayPal, Sofort et Stripe extraite dans `PaymentProviderDetector`
  (`b5d1a55e`).
- Champ caché `ff_payment_method` extrait dans `PaymentMethodFieldBuilder`
  (`3d45e1e2`).
- Paramètres ContentBuilder transmis au formulaire extraits dans
  `ContentBuilderTechnicalFieldsBuilder` (`c53a457e`).
- Paramètres additionnels et jeton CSRF Joomla extraits respectivement dans
  `AdditionalHiddenFieldsBuilder` (`bb469b88`) et `FormTokenFieldBuilder`
  (`462b2984`).

### 4.3 Variantes frontend, backend et preview

État : les champs cachés communs sont mutualisés par
`FormSubmissionFieldsBuilder` (`be602b94f`) et les sorties des trois variantes
sont verrouillées par `dc73ec0fc`. Les différences de route, iframe, cible,
bordure et template restent dans les branches d'orchestration ; la
finalisation complète par mode doit encore être caractérisée avec un runtime
Joomla réel.

- Ajouter une stratégie de finalisation par mode d'exécution après cette
  caractérisation runtime.
- Conserver les différences de route, iframe, cible, bordure et template.
- Étendre les tests de sortie aux parcours ContentBuilder et Query List.

### 4.4 Fermeture et traçage

État : les sorties anticipées nettoient désormais les tampons et restaurent le
gestionnaire d'erreurs via `abortViewRendering()` (`2d4f75ef`). La finalisation
normale et le vidage du trace buffer sont regroupés dans
`finishViewRendering()` (`ced03d7a`), avec tests de caractérisation des deux
ordres de traçage.

- Compléter les tests avec les chemins ContentBuilder et Query List.
- Vérifier les variantes où Joomla interrompt le rendu pendant un callback.

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

1. `bfTextfield` et `bfNumberInput` — partagé et branché via
   `QuickModeInputBuilder` (`065cef94`, `f3d04e55`).
2. `bfTextarea` et compteur de longueur — le contrôle textarea est partagé et
   branché via `QuickModeTextareaBuilder` (`620b7efe`); le compteur est
   désormais partagé via `QuickModeMaxLengthCounterBuilder` (`bdb7d910`).
3. `bfCheckbox` — partagé et branché via `QuickModeCheckboxBuilder`
   (`18a3e7ea`).
4. `bfSelect` — partagé et branché via `QuickModeSelectBuilder`
   (`8a891fb5`).
5. Groupes checkbox et radio — contrôle d'option partagé via
   `QuickModeGroupOptionBuilder` (`a85bed34`); les enveloppes, labels et règles
   `wrap` restent propres aux renderers.
6. Boutons et champs cachés restants — `bfHidden` était déjà partagé par
   `HiddenFieldTrait`; le bouton de champ `bfSubmitButton` est désormais
   partagé via `QuickModeSubmitButtonBuilder` (`92b4af6f`). Les boutons de
   navigation générale utilisent désormais les actions partagées de
   `QuickModePagingActionBuilder` (`10d7dc6a`, `04e09cf6`), y compris
   l’annulation. Les variantes de pages intermédiaires et finales des quatre
   renderers sont maintenant caractérisées par `063c39e7`; les enveloppes de
   thème restent propres à chaque renderer.
7. Calendriers et calendriers responsives — le bouton de déclenchement est
   partagé via `QuickModeCalendarButtonBuilder` (`7911701f`), et l'input texte
   est partagé via `QuickModeCalendarInputBuilder` (`1d623384`). Les
   initialisations Pickadate et responsive sont désormais centralisées via
   `QuickModeCalendarInitScriptBuilder` (`1de4b129`).
8. Uploads, CAPTCHA, signatures et paiements — l'URL de l'image CAPTCHA est
   désormais partagée via `QuickModeCaptchaUrlBuilder` (`21afa208`) et le
   markup image/réponse via `QuickModeCaptchaMarkupBuilder` (`176af451`) et le
   script de rechargement via `QuickModeCaptchaReloadScriptBuilder`
   (`4d706e95`). Les actions de navigation et de soumission sont maintenant
   mutualisées via `QuickModePagingActionBuilder` (`10d7dc6a`, `04e09cf6`) et
   `QuickModeSubmitActionBuilder` (`4dd3e3dd`). La miniature image est
   maintenant partagée via `QuickModeUploadThumbnailScriptBuilder`
   (`704ccbea2`) ; le markup et les callbacks de configuration complète de
   l'uploader restent à traiter. Les scripts de validation complets restent à
   traiter ; le callback AJAX OnePage est maintenant isolé
   dans `QuickModeAjaxSubmitScriptBuilder`, avec ses branches succès/échec
   caractérisées.

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

Le périmètre PHPCS couvre désormais aussi les services extraits de CAPTCHA,
ContentBuilder et les actions QuickMode (`CaptchaEndpointBuilder`, builders de
fichiers/champs cachés, `ContentBuilderSignatureFileResolver`,
`QuickModePagingActionBuilder` et `QuickModeSubmitActionBuilder`). Ces huit
fichiers passent PHPCS sans erreur ni warning. Le ruleset complet passe
désormais également sans erreur ni warning (`61/61`), après le nettoyage ciblé
des traits et builders QuickMode ; aucun reformatage global n'a été appliqué.

Le premier nettoyage ciblé de cette baseline PHPCS est réalisé dans
`QuickModeSelectBuilder`, `QuickModeCaptchaMarkupBuilder` et
`QuickModeUploadOptionsBuilder` (`eaf8b2d1`) : leurs sorties sont inchangées et
leurs warnings de longueur sont supprimés. Le nettoyage de
`BootstrapStyleFieldTrait` est désormais terminé dans les lots
`d1638610`, `9a93d10c` et `aaabc40c`.

Le nettoyage du trait Bootstrap a couvert les méthodes de résumé, calendrier,
champs, confirmation, paiement, signature et groupes (`750dd432`, `6b0a8532`,
`82b42a90`, `d1638610`, `9a93d10c`, `aaabc40c`) ; leurs sorties restent
couvertes par les snapshots. Le compteur est passé de 64 à 0 warnings de
longueur pour ce trait. Les quatre renderers passent toujours la
caractérisation PHPUnit et le sous-périmètre QuickMode passe PHPStan.

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

## Phase 8 — Consolider les builders triviaux

Ajoutée le 2026-08-31, en retour sur les phases 1 à 5 : l'extraction
systématique de chaque bloc de `RenderingEngine::view()` en sa propre classe
a, dans une minorité de cas, produit des classes disproportionnées par
rapport à ce qu'elles font — un fichier, une propriété et un getter
lazy-init dans `RenderingEngine`, et un fichier de test, pour une seule
chaîne statique sans branchement ni réutilisation. `PaymentMethodFieldBuilder`
en est l'exemple type :

```php
public function build(string $indentation): string
{
    return $indentation . '<input type="hidden" name="ff_payment_method" id="bfPaymentMethod" value=""/>' . "\n";
}
```

### Critère de consolidation

Une classe extraite est candidate à un regroupement quand elle cumule :

1. **Aucune branche conditionnelle** (ou une seule, triviale) — une pure
   fonction de mise en forme, sans logique à isoler.
2. **Un seul appelant** — aucun bénéfice de réutilisation qui justifierait
   un fichier séparé (à l'inverse des builders `QuickMode*Builder` partagés
   entre les 4 renderers, dont l'extraction élimine une vraie duplication et
   reste hors périmètre de cette phase).
3. **Un thème réel commun** avec d'autres classes du même profil — sans
   thème partagé, forcer un regroupement ne ferait que déplacer le problème
   vers une classe fourre-tout, ce qui n'est pas non plus souhaitable.

Analyse quantitative (branches conditionnelles + nombre de fichiers
appelants, sur l'ensemble des classes `*Builder` de
`components/com_breezingformsng/src/Service/Rendering`) : cinq
regroupements cohérents sont désormais réalisés. Les classes restantes sans
partenaire honnête (`ClassNameResolver`, `MobileChoiceMarkupBuilder`,
`QueryListStateLibraryBuilder`, `ProcessorHeaderRenderer`) restent isolées.

### 8.1 `HiddenFormFieldsBuilder` — champs `<input type="hidden">`

Consolide 6 classes qui construisaient chacune des champs cachés du
formulaire rendu, sans logique substantielle :
`PaymentMethodFieldBuilder`, `FormTokenFieldBuilder`,
`AdditionalHiddenFieldsBuilder`, `FormRoutingFieldsBuilder`,
`FormSubmissionFieldsBuilder`, `FormContextFieldsBuilder` → méthodes
`paymentMethod()`, `token()`, `additional()`, `routing()`, `submission()`,
`context()`.

### 8.2 `LegacyScriptTagWrapperBuilder` — habillage `<script>` historique

Consolide 4 classes qui enveloppaient chacune un fragment JavaScript dans le
tag `<script><!-- ... //--></script>` hérité du rendu historique :
`ContentBuilderReadonlyScriptWrapperBuilder`,
`FormValidationScriptWrapperBuilder`, `EditableRecordScriptWrapperBuilder`,
`ContentBuilderEditableScriptWrapperBuilder` → méthodes
`contentBuilderReadonly()`, `formValidationOpen()`/`formValidationClose()`,
`editableRecord()`, `contentBuilderEditable()`.

### 8.3 `ContentBuilderFileSupportBuilder`

Consolide `ContentBuilderFileDisplayNameBuilder`,
`ContentBuilderFileValueParser` et `ContentBuilderSignatureFileResolver` dans
`ContentBuilderFileSupportBuilder` (`9db625e10`). Les sorties d'échappement,
de parsing des lignes et de résolution des signatures sont couvertes dans un
test commun ; les trois anciens fichiers et leurs tests ont été supprimés.

### 8.4 `QuickModeOnePageCallbackScriptBuilder`

Consolide `QuickModeSubmitButtonRestoreBuilder` et
`QuickModeRemodalCloseScriptBuilder` dans
`QuickModeOnePageCallbackScriptBuilder` (`ea627bbdf`). Les callbacks de
restauration Ladda et de fermeture du modal conservent leur sortie historique
et sont testés ensemble.

### 8.5 `CaptchaSupportBuilder`

Consolide `CaptchaEndpointBuilder` et `CaptchaValidationDefaultsBuilder` dans
`CaptchaSupportBuilder` (`5bf333ab8`). Le wrapper `legacy_wrap` avait déjà été
supprimé en phase 10 ; il n'a donc pas été recréé dans ce regroupement. Les
endpoints frontend/administrateur et les valeurs de validation sont testés.

### Méthode de vérification

Chaque corps de méthode a été comparé octet pour octet à la classe d'origine
avant toute suppression (extraction automatisée des deux corps, comparaison
stricte) — aucune des 10 méthodes déplacées n'a changé de comportement. Les
propriétés/getters lazy-init et points d'appel de `RenderingEngine.php` ont
été mis à jour vers les nouvelles classes ; les anciens fichiers concernés et
leurs tests ont été supprimés après fusion des cas de test dans trois fichiers
de test consolidés. Suite complète verte, PHPStan propre et PHPCS vert sur les
nouveaux services.

### Note sur les mentions historiques

Les phases 1 à 6 ci-dessus continuent de citer les 10 anciennes classes par
leur nom (avec leur hash de commit) : ce sont des enregistrements
historiques du travail effectué à l'époque, exacts au moment où ils ont été
écrits — ils n'ont pas été réécrits rétroactivement pour ne pas fausser la
traçabilité. Les classes qu'ils nomment sont désormais des méthodes de
`HiddenFormFieldsBuilder` ou `LegacyScriptTagWrapperBuilder` (voir 8.1/8.2
ci-dessus pour la correspondance).

## Phase 9 — Suppression du rendu mobile historique

Ajoutée le 2026-08-31. La détection de terminal mobile (`bf_is_mobile()`) et
le renderer QuickMode dédié qu'elle déclenchait dataient de l'époque
pré-responsive du projet (~2010-2014) : la regex de détection ciblait des
plateformes toutes abandonnées depuis 3 à plus de 15 ans (BlackBerry OS,
Windows Phone, Symbian, WebOS, Firefox Mobile/Fennec, et un bloc de repli au
format `detectmobilebrowsers.com` reconnaissant des modèles de téléphones
WAP des années 2000). Le projet utilisant Bootstrap 5 (responsive) sur
l'ensemble de ses thèmes, cette voie de rendu séparée n'a plus de raison
d'être — décision : suppression complète plutôt que refactorisation.

### Périmètre supprimé

- `bf_is_mobile()` (détection User-Agent, `helpers.php`).
- `libraries/crosstec/constants.php` et son chargement : les constantes Mambo
  (`_ISO`, `_VALID_MOS`, `_MOS_*`) étaient orphelines dans le code Joomla 6.
- `MobileRenderer` (renderer QuickMode dédié, 1463 lignes) et sa façade de
  compatibilité `BFQuickModeMobile` (`class BFQuickModeMobile extends
  MobileRenderer {}`), ainsi que son chargement dans
  `breezingformsng.php`.
- `MobileChoiceMarkupBuilder` (lien « voir la version mobile ») et son seul
  appelant, `RenderingEngine::renderMobileChoice()`.
- Les méthodes orphelines de `RenderingEngine` :
  `createMobileRenderer()`, `syncMobileSessionPreference()`,
  `applyMobileMode()`, `mobileChoiceType()`.
- Le bloc de détection mort dans `SubmissionEngine` (`$dataObject`,
  `$rootMdata`, `$is_device` n'étaient lus nulle part ailleurs).
- Les propriétés `isMobile` du `processor_facade` (deux déclarations
  distinctes) et la classe `bfMobile` inutilisée du même fichier.
- Les 3 champs d'administration (`mobileEnabled`, `forceMobile`,
  `forceMobileUrl`) dans le formulaire QuickMode par défaut, le panneau
  « Avancé » (`advanced_form.php`) et leur synchronisation JS
  (`quickmode-app-properties.js`).
- 5 fichiers JS site dédiés au thème mobile
  (`quickmode-*-mobile.js`), devenus orphelins après la suppression de
  `MobileRenderer`.
- Les clés de traduction `COM_BREEZINGFORMSNG_MOBILE_ENABLED`,
  `COM_BREEZINGFORMSNG_FORCE_MOBILE`, `COM_BREEZINGFORMSNG_FORCE_MOBILE_URL`,
  `COM_BREEZINGFORMSNG_QM_MOBILE_FORMS`,
  `COM_BREEZINGFORMSNG_QM_MOBILE_FORMS_FORCE`,
  `COM_BREEZINGFORMSNG_QM_MOBILE_FORMS_URL` et
  `COM_BREEZINGFORMSNG_MOBILE_VERSION` (devenue orpheline avec
  `renderMobileChoice()`), dans les 8 langues.
- Les tests de caractérisation dédiés
  (`MobileRendererCharacterizationTest`, `MobileChoiceMarkupBuilderTest`) et
  les 4 méthodes de test de `RenderingEngineViewCharacterizationTest`
  couvrant les branches mobiles désormais supprimées ; mise à jour de
  `PublicFacadeApiTest` (retrait des attentes sur `BFQuickModeMobile`).

### Effet de bord conservé : `legacy_wrap`

`applyMobileMode()` portait un effet de bord sans rapport avec la détection
mobile : `$processor->legacy_wrap = false` pour les thèmes Bootstrap
(bascule entre le balisage historique en `<table>` et le balisage en `<div>`
moderne, lu par `RenderingEngine` et `FormClosingMarkupBuilder`). Cet effet
n'était appliqué que dans la branche *sans* détection mobile — un
oubli probable de l'implémentation d'origine. Il est conservé, extrait en
bloc autonome dans `view()`, et s'applique désormais **sans condition** pour
tout thème Bootstrap, ce qui corrige implicitement ce défaut latent.

### Risque connu — façade `BFQuickModeMobile`

`BFQuickModeMobile` était documentée comme façade de compatibilité
ascendante pour du PHP potentiellement stocké en base
(`facileforms_pieces.code`, `forms.piece*code`) référençant la classe par
son nom. Sa suppression ne peut pas être validée par grep sur le dépôt — un
tel code vivrait uniquement en base de données. Risque accepté sur demande
explicite de l'utilisateur (« supprimer tout cela »), à surveiller si un
formulaire existant en production référence cette classe.

### Vérification

Suite complète verte (403 tests, 0 échec), PHPStan niveau 2 propre, `php -l`
sur tous les fichiers modifiés, vérification live du panneau QuickMode
« Avancé » (absence des 3 champs mobiles, `joomlaHint` intact,
zéro erreur console/réseau), build + validation du package.

## Phase 10 — Suppression de `legacy_wrap`

Ajoutée le 2026-08-31, à la suite de la phase 9. `legacy_wrap` pilotait deux
variantes d'habillage du formulaire QuickMode : le thème Bootstrap recevait
un simple `<div>`, tandis que le thème Classic (`ClassicRenderer`) recevait
un balisage à 9 `<div>` imbriqués (`bfPage-tl/tr/t/l/r/m/bl/br/b`) — un
bricolage CSS pré-CSS3 destiné à simuler des coins arrondis via des images
de fond, avant l'existence de `border-radius` — plus un `<table
style="display:none">` caché pour le widget reCAPTCHA (`bfReCaptchaWrap`).

Constat : la feuille de style réellement chargée par `ClassicRenderer`
(`themes/quickmode/system.css`) ne stylait déjà plus aucune de ces classes
`bfPage-*` — le fichier qui les stylait (`themes/quickmode/aqua/theme.css`)
n'était référencé par aucun chemin de code. Le balisage à 9 divs et le
`<table>` caché étaient donc déjà visuellement inertes avant cette phase :
leur suppression ne change aucun rendu visible actuel. `#bfReCaptchaWrap`/
`#bfReCaptchaDiv` n'étaient lus par aucun JS ni CSS du dépôt.

### Périmètre supprimé

- La propriété `$legacy_wrap` du `processor_facade`.
- La branche legacy de `FormOpeningMarkupBuilder` et
  `FormClosingMarkupBuilder` (signatures simplifiées : plus de paramètre
  booléen ; `FormOpeningMarkupBuilder` perd aussi son paramètre `$newline`,
  devenu inutilisé une fois le balisage réduit à un seul `<div>`).
- `CaptchaWrapperMarkupBuilder` entièrement (son seul appelant a disparu) et
  son test.
- Le calcul de `$legacyWrap` dans `RenderingEngine::view()` (les deux
  méthodes privées `initializeFormRendering()`/`closeFormRendering()` ne
  prennent plus de paramètre).
- Les tests de caractérisation dédiés aux deux branches (fusionnés en tests
  à comportement unique).

### Remplacement CSS

Un `border-radius: 8px` a été ajouté sur `.bfFormDiv` dans
`themes/quickmode/system.css`, pour porter l'intention visuelle d'origine
(coins arrondis) via la propriété CSS moderne plutôt que via le balisage à
9 divs.

### Vérification

Suite complète verte (399 tests, 0 échec — les 4 tests de caractérisation
legacy/moderne fusionnés en 2), PHPStan niveau 2 propre, `php -l` sur tous
les fichiers modifiés, build + validation du package. Les assertions des
tests `FormOpeningMarkupBuilderTest`/`FormClosingMarkupBuilderTest`/
`RenderingEngineViewCharacterizationTest` couvrent le contenu exact,
octet pour octet, du nouveau balisage.

## Phase 11 — Nettoyage des assets QuickMode orphelins

Ajoutée le 2026-08-31, à la suite de la phase 10. En investiguant l'origine
visuelle du balisage à 9 divs supprimé en phase 10, découverte que
`components/com_breezingformsng/themes/quickmode/` contenait un ensemble
d'assets totalement hors d'atteinte du code actuel :

- 6 dossiers de skins d'origine BreezingForms (2010-2013) : `aqua/`,
  `breeze/`, `default/`, `glossy_blue/`, `glossy_gray/`, `qmtheme/`.
- `system.ie6.css`, `system.ie7.css`, `system.ie.css` — feuilles
  conditionnelles pour Internet Explorer 6/7.
- `mobile-system.css` — résidu de la phase 9 (suppression du rendu mobile).
- `images/` (icônes, spinner ajax-loader) et les doublons
  `ajax-loader.gif/png`, `cancel.png`, `upload.png`, `img/icon_info_old.png`
  à la racine (`ClassicRenderer` charge en réalité ses icônes cancel/upload
  depuis `media/breezingforms/themes/`, pas ce dossier).

Vérification de la logique de sélection réelle
(`QuickmodeModel::scanThemeDir()`) : le menu déroulant « Thème » de l'admin
scanne dynamiquement `media/breezingforms/themes/` (Classic, un seul skin
réel aujourd'hui : `vcmb_j5`, le thème maison) et
`media/breezingforms/themes-bootstrap5/` (Bootstrap/OnePage, dossier
inexistant). Aucun de ces deux chemins ne pointe vers
`components/com_breezingformsng/themes/quickmode/<skin>/` — les 6 dossiers
supprimés n'étaient donc sélectionnables par aucune voie du code actuel.

### Conservé

`system.css` (seule feuille chargée par `ClassicRenderer`, ligne 184) et
`img/icon_info.png` (seule image qu'il référence, via une unique règle
`url()` dans `system.css`).

### Vérification

252 fichiers supprimés (uniquement des assets CSS/images/HTML, aucun PHP).
Suite complète verte (399 tests), PHPStan niveau 2 propre, build +
validation du package (assets absents de l'archive, `system.css` présent).

## Phase 12 — Restauration des 6 skins Classic dans `media/breezingforms/themes/`

Ajoutée le 2026-08-31, correction de la phase 11. Les 6 skins supprimés en
phase 11 (`aqua`, `breeze`, `default`, `glossy_blue`, `glossy_gray`,
`qmtheme`) n'étaient inatteignables qu'à cause de leur emplacement — pas
parce qu'ils étaient sans valeur. Sur demande explicite, restaurés depuis
l'historique git (`git checkout d88a6ea91 --`) puis déplacés vers
`media/breezingforms/themes/<skin>/`, aux côtés de `vcmb_j5`, le seul
chemin réellement scanné par `QuickmodeModel::scanThemeDir()` pour peupler
le sélecteur « Thème » de l'admin (voir phase 11 pour le détail du
mécanisme de scan).

Point notable retrouvé en creusant l'historique : `media/breezingforms/
themes/<skin>/` était en fait leur **emplacement d'origine** — le commit
`d6655c526` (« Legacy files moves ») les avait déplacés vers
`components/com_breezingforms(ng)/themes/quickmode/` bien avant le
renommage `com_breezingforms → com_breezingformsng`. Cette restauration
les ramène donc à leur chemin historique initial ; `git log --follow`
sur le nouveau chemin remonte sans interruption jusqu'au commit initial.

### Réserve connue

Le skin `aqua` (seul des 6 à référencer les classes `bfPage-tl/tr/t/l/r/
m/bl/br/b`, supprimées du balisage en phase 10) a environ 10 règles CSS
qui ne trouveront plus d'élément à cibler — sans effet visuel néfaste,
juste inertes. Les 5 autres skins ne référençaient pas ce balisage.

### Vérification

Vérification live : les 7 thèmes (`vcmb_j5` + les 6 restaurés) apparaissent
dans le sélecteur `#bfTheme` de l'admin, zéro erreur console/réseau. Suite
complète verte (399 tests, aucun fichier PHP touché), PHPStan niveau 2
propre, build + validation du package (skins présents dans l'archive sous
`media/breezingforms/themes/`, absents de l'ancien chemin).

## Phase 13 — Nettoyage des règles `bfPage-*` mortes dans `aqua/theme.css`

Ajoutée le 2026-08-31, à la suite de la phase 12. Comme signalé dans la
réserve de la phase 12, `aqua/theme.css` était le seul des 6 skins
restaurés à référencer les classes `bfPage-tl/tr/t/l/r/m/bl/br/b`,
supprimées du balisage en phase 10 : ~10 règles CSS sans effet, ciblant
des éléments qui ne sont plus émis. Supprimées, avec les 8 images
`bg_page-*.png` qu'elles étaient les seules à référencer (`img/`
conserve les images encore utilisées : boutons, légendes, fieldsets,
rollover).

### Vérification

Vérification live : `media/breezingforms/themes/aqua/theme.css` répond
200, plus aucune règle `bfPage-*` dans le contenu servi. Build +
validation du package (images supprimées absentes de l'archive,
`theme.css` présent). Suite complète verte (399 tests, aucun fichier PHP
touché).

## Phase 14 — Correction du chevauchement des radios natives dans l'onglet Options

Ajoutée le 2026-08-31. Régression signalée par l'utilisateur : dans l'onglet
QuickMode « Options » → « Général », le champ « Publié(s) » (Oui/Non,
markup Bootstrap 5 natif de `forms/edit.php`) affichait le cercle radio
chevauchant le texte du label.

Cause : `media/com_breezingformsng/css/custom.css` contient un skin CSS
personnalisé pour les radios/checkboxes de l'éditeur QuickMode (technique
`opacity:0` sur l'input natif + `<span>` dessiné à la place, un procédé du
même esprit pré-Bootstrap que le balisage `bfPage-*` supprimé en phase 10).
Une exemption `:not(#bfOptionsFieldsWrap *)` avait déjà été ajoutée sur la
toute première règle du skin (`opacity`/`width`) pour laisser les radios
Bootstrap natives de l'onglet Options intactes — mais **pas** sur les 7
autres règles du même bloc (`+ label`, `+ label > span`, `:checked + label
> span`, etc.), documentée par erreur comme complète (« so its normal
Bootstrap sizing applies too, not just visibility »). La règle
`main input[...]:not(old) + label { margin-left: -1.5em; }`, non exemptée,
s'appliquait quand même aux labels Bootstrap natifs de `#bfOptionsFieldsWrap`
et les tirait par-dessus le cercle du radio.

Corrigé : ajout de `:not(#bfOptionsFieldsWrap *)` aux 7 règles restantes du
bloc, pour que l'exemption corresponde enfin à ce que le commentaire du
bloc décrivait déjà.

### Vérification

Vérification live avant/après (capture de la ligne « Publié(s) ») :
chevauchement confirmé avant correctif, cercle et texte correctement
séparés après déploiement du CSS corrigé. Suite complète verte (399 tests,
aucun fichier PHP touché), build + validation du package.

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
2. Étendre la stratégie `QuickModeInputBuilder` aux wrappers Bootstrap et
   OnePage, après le premier branchement Classic/Mobile (`065cef94`).
3. Harnais ContentBuilder pour les parcours runtime fichiers/signatures.
4. Rendu HTML classique par famille de nœuds.
5. Réduction progressive des avertissements PHPCS et PHPStan après chaque
   extraction fonctionnelle.

Lots récemment terminés : tests d'intégration des variantes Query List et du
point d'arrêt `bury()` dans la première boucle (`7a19ffeb`), contrôles de
fichiers ContentBuilder (`2c72231e`) et loader d'enregistrement éditable
(`89165de7`). Les scripts post-rendu et les champs techniques sont également
extraits et testés (`ad9dd75f`, `3d45e1e2`, `c53a457e`). Les champs de routage
`return` et `tmpl` sont désormais construits par
`FormRoutingFieldsBuilder` dans les trois branches de finalisation via
`f183a4ce`, avec échappement et absence de paramètres couverts. Le formatage
du token CSRF Joomla est désormais isolé dans `FormTokenFieldBuilder` et
réutilisé dans ces trois branches via `462b2984`, avec sa sortie indentée et
ses retours historiques testés. Il reste à couvrir la finalisation complète
par mode d'exécution, sans modifier leurs différences de routage. La
finalisation des paramètres de routage et du token est désormais isolée. Les
champs de contexte (`ff_contentid`, `ff_applic`, `ff_record_id`,
`ff_module_id` et `ff_runmode`) sont désormais générés par
`FormContextFieldsBuilder` dans les trois branches via `f7d06454`. Les
séparateur de lignes reste fourni par le renderer afin de préserver les
sorties de test et le comportement historique (`0261acd4`). La fermeture des
wrappers moderne et legacy est désormais construite par
`FormClosingMarkupBuilder`, branché dans `closeFormRendering()` via
`301ba9f1`. Les deux sorties restent couvertes par le test de caractérisation
de `RenderingEngine` et par des tests unitaires dédiés ; le cycle complet de
finalisation par mode reste à éprouver de bout en bout. Le markup d'ouverture
du formulaire (wrapper moderne/legacy, identifiant et classe personnalisée)
est désormais construit par `FormOpeningMarkupBuilder` via `e77be68a`, avec
ses deux variantes couvertes par des tests unitaires.

La purge des fichiers temporaires Flash et chunked est désormais mutualisée
par `TemporaryUploadFileCleaner` dans `FormRenderer`. Les suffixes, le critère
de nommage historique et l'expiration sont couverts par des tests unitaires.

Le scan des thèmes de `QuickmodeModel` utilise désormais `DirectoryIterator`,
avec les répertoires techniques explicitement exclus et les cas de répertoire
absent couverts par un test unitaire.

Le nettoyage du cache de paiement est désormais porté par
`PaymentCacheCleaner`, en conservant son critère distinct de nommage à quatre
segments et son expiration à 24 heures ; ses cas limites sont testés.

La lecture du répertoire de polices PDF est mutualisée par
`PdfFontDirectoryScanner` entre le document PDF, l'export et l'export des
enregistrements. La sélection des fichiers réguliers lisibles et le répertoire
absent sont couverts par des tests unitaires.

La résolution des modèles d'e-mail principaux et de mailback est mutualisée
par `MailTemplateResolver`, avec priorité au modèle HTML puis texte du
formulaire avant les modèles par défaut ; ces variantes sont testées.

La résolution des signatures ContentBuilder est désormais limitée à des
fichiers réguliers nommés dans le répertoire prévu; le cas de traversal est
couvert par le test du support fichiers.

L'encodeur d'image de signature vérifie également la lisibilité du fichier et
est couvert par des tests d'encodage et d'absence de fichier.

`QuickModeFormTagBuilder` échappe désormais les valeurs injectées dans les
attributs `action`, `name`, `id` et `class`; le test couvre les guillemets et
les séparateurs HTML.

`QuickModeUploadConfigurationBuilder` échappe désormais les valeurs injectées
dans les chaînes JavaScript de configuration; les champs, tickets, runtimes,
URL et extensions contenant des apostrophes sont testés.

`UploadStorage` vérifie désormais directement le résultat de `chmod`, sans
masquer les erreurs système, et conserve les statuts de stockage testés.

L'assemblage des chunks du callback Flash est désormais isolé dans
`FlashChunkAssembler`; la concaténation des fichiers existants et le rejet
d'un chunk absent sont couverts par des tests unitaires.

La sélection des fichiers Flash terminés par champ et ticket est désormais
isolée dans `FlashUploadFileMatcher`; les suffixes et les fichiers étrangers
sont couverts par des tests unitaires.

`SubmissionEngine` s'appuie désormais directement sur les fichiers filtrés par
le matcher et vérifie explicitement l'accessibilité du répertoire source; les
gardes de matching dupliquées ont été supprimées.

`ImageResizer` dispose désormais d'un filet de tests dédié pour la détection
PNG et les trois géométries de redimensionnement, en complément des tests de
taille mémoire et de capacité déjà présents.

La gestion des buffers de sortie et la lecture de `max_execution_time` dans
`SubmissionEngine` n'utilisent plus de suppression d'erreur; les niveaux de
buffer sont contrôlés avant fermeture.

`ImageResizer` rejette désormais explicitement les chemins absents ou non
lisibles avant d'interroger EXIF/GD, sans suppression d'erreur sur ces lectures.

`IntegratorRuntime` réutilise désormais `StoredPhpExecutor` pour exécuter les
scripts d'intégration dans son scope avec le contexte `$value` explicite; le
retour et le scope d'exécution sont couverts par des tests unitaires.

Les tests d'existence des assets statiques des trois renderers QuickMode
utilisent désormais `is_file()` sans masquage d'erreur; leurs sorties restent
couvertes par les tests de caractérisation des renderers.

Les paramètres additionnels `ff_otherparams` du mode frontend sont désormais
générés par `AdditionalHiddenFieldsBuilder` via `bb469b88`, avec ordre,
encodage URL et échappement HTML couverts. La détection des providers de
paiement est désormais isolée dans `PaymentProviderDetector` via `b5d1a55e`;
les trois providers historiques et l'absence de provider sont couverts par
des tests unitaires. Les champs conditionnels de finalisation restent les
prochains points à caractériser.

Les champs conditionnels `target`, `frame`, `border`, `page`, `align` et `top`
sont désormais générés par `FormOptionalContextFieldsBuilder` via
`fdbf8960`. Les paramètres frontend/backend complets et la variante preview
(page/frame uniquement) sont couverts par des tests unitaires, tandis que les
différences de mode restent exprimées explicitement à l'appel.

Les champs cachés de soumission communs aux trois modes sont désormais
assemblés par `FormSubmissionFieldsBuilder` (`be602b94f`). Frontend, backend et
preview conservent leurs marqueurs spécifiques (`act` ou `ff_frame`), tandis
que le formulaire et la tâche de soumission suivent une construction commune
testée. Les sorties exactes des trois variantes sont désormais verrouillées
par `dc73ec0fc`.

Le markup du choix mobile est désormais construit par
`MobileChoiceMarkupBuilder` (`39a4c1da0`). Le calcul de l'URL et la
normalisation des paramètres restent dans `RenderingEngine`, tandis que la
sortie JavaScript/HTML historique est couverte par un test dédié.

Le callback Query List `ff_selectAllQueryRows` est désormais construit par
`QueryListSelectAllScriptBuilder` (`0b1229d37`). La logique de pagination,
la remise à zéro des lignes hors page et la synchronisation de la case globale
sont couvertes séparément ; le callback de navigation `ff_dispQueryPage` reste
encore partiellement dans l'orchestrateur, mais son markup de navigation est
désormais construit par `QueryListNavigationBuilder` (`c93d06eb7`). Les
libellés traduits sont injectés par le moteur et les liens de navigation sont
couverts indépendamment ; le cœur de mise à jour des lignes reste à isoler.

Le wrapper caché `bfReCaptchaWrap` est désormais construit par
`CaptchaWrapperMarkupBuilder` (`092a2a9fa`). Il reste limité au mode
`legacy_wrap`, et l'absence de saut de ligne historique est explicitement
préservée.

L'assemblage de la balise `<form>` QuickMode est désormais confié à
`QuickModeFormTagBuilder` (`4f5b8559d`). Le calcul de l'action et la
résolution de la classe personnalisée restent dans `RenderingEngine`, tandis
que l'ordre des attributs et la classe `bfQuickMode` sont couverts par des
tests exacts.

L'enveloppe du script ContentBuilder des champs non éditables est désormais
construite par `ContentBuilderReadonlyScriptWrapperBuilder` (`3e1723d15`).
L'enregistrement de l'asset et la génération du contenu restent séparés, et
les marqueurs HTML historiques sont couverts par un test de sortie exacte.

Le wrapper `bfLoadEditable` est désormais construit par
`EditableRecordScriptWrapperBuilder` (`f3b6f8d97`). Le moteur conserve le
nettoyage des valeurs et la génération du contenu d'hydratation, tandis que
le chargement JavaScript, l'identifiant du formulaire et l'effacement
historique de `ff_nm_seccode[]` sont isolés et testés.

Le calcul de page et le rafraîchissement des lignes Query List sont désormais
construits par `QueryListRowsRefreshBuilder` (`7ee50bccc`). Le déplacement
des valeurs, la gestion des colonnes masquées et le masquage des lignes hors
page sont testés indépendamment ; la fonction de navigation conserve encore
son orchestration finale et ses appels de callbacks.

La fin de `ff_dispQueryPage` est désormais construite par
`QueryListPaginationTailBuilder` (`c942cfe90`). Les comportements optionnels
de resynchronisation des checkbox, de redimensionnement, de défilement dans
une iframe et de défilement général sont testés par combinaisons ; la gestion
des variables d'état et l'appel au callback restent dans le moteur.

L'initialisation des tableaux JavaScript Query List est désormais fournie par
`QueryListStateLibraryBuilder` (`70c6aa58b`). L'ordre des sept entrées
(`ff_queryCurrPage` à `ff_queryRows`) et l'injection du code de données restent
explicitement couverts ; l'orchestration de la liaison des callbacks demeure
dans `RenderingEngine`.

Les affectations d'état propres à chaque ligne Query List sont désormais
construites par `QueryListRowStateBuilder`. La préparation conserve dans
`RenderingEngine` la compilation des colonnes et l'exécution de la requête,
tandis que la génération de `ff_queryCurrPage`, `ff_queryCols` et
`ff_queryRows` est pure et couverte par un test de sortie exacte.

L'enveloppe du script global de validation est désormais construite par
`FormValidationScriptWrapperBuilder` (`9b92acb55`). Les contrôles de fichiers
et de CAPTCHA restent générés par leurs services respectifs, tandis que les
marqueurs du bloc JavaScript et leur ordre historique sont testés.

La génération de `checkFileExtensions()` est désormais isolée dans
`FileExtensionsCheckBuilder` (`30dd41889`). Le builder reçoit les lignes du
formulaire, le nombre de lignes, le message déjà traduit et l'indication de
template configuré ; il conserve donc l'orchestration Joomla dans
`RenderingEngine` tout en testant séparément les uploads configurés et la
sortie neutre lorsque le template n'est pas renseigné.

L'initialisation de formulaire est désormais caractérisée de bout en bout au
niveau de sa méthode dédiée par `0d33f0253` : les registres Query List sont
réinitialisés, le wrapper ReCaptcha legacy est présent uniquement dans ce
mode et l'ouverture du formulaire conserve son identifiant.

Les sorties anticipées de `RenderingEngine::view()` après activation du buffer
et du gestionnaire d'erreurs nettoient désormais leur propre état via
`abortViewRendering()` (`2d4f75ef`). Les chemins `bury()` et callbacks sont
couverts par les tests de caractérisation, qui vérifient le niveau de buffer
restitué au code appelant et ne produisent plus de tests PHPUnit risqués. La
finalisation normale est regroupée dans `finishViewRendering()` (`ced03d7a`) :
les ordres `dumpTrace()`/`ob_end_flush()` des modes direct et non direct sont
caractérisés et testés, ainsi que la fermeture `</pre>` du mode direct.

Les champs de contexte et le cycle d'enveloppe de `view()` sont désormais
isolés en builders testés : `FormContextFieldsBuilder` (`f7d06454`),
`FormOpeningMarkupBuilder` (`e77be68a`) et `FormClosingMarkupBuilder`
(`301ba9f1`). La génération du token et des paramètres de routage reste
également partagée (`462b2984`, `f183a4ce`), avec conservation explicite du
séparateur de lignes (`0261acd4`). Les champs conditionnels propres à chaque
mode restent à caractériser avant une éventuelle extraction.

L'ordre final des champs cachés et la fermeture du formulaire sont désormais
assemblés par `FormModeFinalizationBuilder` (lot de finalisation courant) pour les modes
frontend, backend et preview. Les décisions Joomla et la génération des
fragments restent dans `RenderingEngine`, tandis que les variantes d'ordre
et l'absence de sortie preview hors iframe sont couvertes par des tests.

La façade `helpers.php` ne dépend plus des fonctions supprimées ou obsolètes
de PHP 8.3 pour `bf_isUTF8()` et `bf_stripslashes_deep()`. La validation UTF-8
utilise désormais `preg_match('//u', ...)`, la détection de BOM est explicite
pour les tableaux d'octets, et le retrait des magic quotes est un no-op puisque
ce mécanisme n'existe plus. Ces contrats sont couverts par
`LegacyHelpersTest`.

Les appels de messagerie restants utilisent désormais l'API Joomla 6
(`addRecipient`, `addAttachment`, `isHtml` et `send`) dans la façade helpers,
le callback Sofort et le double opt-in de `SubmissionEngine`. Une vérification
automatisée empêche le retour des noms de méthodes historiques.

Le déplacement des fichiers uploadés dans `SubmissionEngine` utilise
désormais `Joomla\\Filesystem\\File::copy()` sans suppression d'erreur et
traite explicitement les retours négatifs de copie et de suppression avant de
valider le déplacement.

Les quatre renderers QuickMode ont encore une baseline PHPCS distincte ; le
contrôle direct fait apparaître des violations de formatage héritées. Ce lot
reste séparé de la mutualisation fonctionnelle pour conserver des commits
réversibles.

`ClassicRenderer` a reçu les corrections automatiques PHPCBF et ses trois
erreurs structurelles manuelles dans `e25d501f`; les avertissements de
longueur restent explicitement hors de ce lot. `MobileRenderer` a reçu le même
traitement dans `14985d4d`; ses snapshots et PHPStan sont verts, avec les
avertissements de longueur conservés pour un lot ultérieur.
`ClassNameResolver` et l'appel multi-ligne restant de `BootstrapRenderer` ne
présentent désormais plus d'erreur PHPCS via `0fc70f33`; les avertissements de
longueur de Bootstrap restent documentés comme hérités.
Les builders de finalisation et de validation ContentBuilder ajoutés dans ces
lots ne présentent plus d'avertissement de longueur via `4d64cc7c`; les
chaînes produites restent inchangées et couvertes par les tests.
`BootstrapRenderer` a été traité dans `288b42a4`; ses snapshots et PHPStan
sont également verts. `OnePageRenderer` a reçu le même traitement dans
`72204865`; ses snapshots et PHPStan sont verts également. Les quatre
renderers ont désormais une baseline sans erreurs PHPCS, les avertissements de
longueur restant à traiter séparément. Le trait partagé
`BootstrapStyleFieldTrait` est désormais inclus dans le périmètre PHPCS par
`212b6450` et ne présente plus d'erreur, avec ses avertissements de longueur
documentés.

Le contrôle partagé `bfTextfield`/`bfNumberInput` est commité dans
`065cef94` et branché dans les wrappers Classic/Mobile. Le trait Bootstrap
commun aux wrappers Bootstrap/OnePage l'utilise désormais aussi via
`f3d04e55`, sans perte de classes, icônes ni attributs de thème. Les autres
familles de champs restent à migrer par ordre de risque.
La décision de type, les traductions, les longueurs et les bornes sont
désormais regroupées dans `QuickModeTextFieldStrategy` (`059885a45`) et
utilisées par les quatre renderers ; les enveloppes et effets annexes restent
propres à chaque thème.

Le contrôle `bfTextarea` est désormais extrait dans
`QuickModeTextareaBuilder` et branché dans les quatre renderers par
`620b7efe`. Les snapshots QuickMode restent verts (`84 tests, 230
assertions`) et le service est couvert par des tests d'échappement, de
placeholder et d'attributs structurels. Le compteur de longueur est désormais
couvert par `QuickModeMaxLengthCounterBuilder` (`bdb7d910`) ; ses événements
restent volontairement dans les renderers jusqu'à caractérisation complète.
La logique commune de textarea brut (traductions, dimensions et callback de
longueur) est également centralisée dans `QuickModeTextareaStrategy`
(`d57debb07`), tandis que l'intégration de l'éditeur HTML reste dans les
renderers.

Les lots 3 à 5 peuvent être préparés en parallèle du lot 6, à condition que le
branchement dans `RenderingEngine.php` soit coordonné.

Le parsing des règles `toggleFields`, commun aux quatre renderers QuickMode,
est désormais confié à `QuickModeToggleFieldsParser` (`37c554755`). Les
méthodes publiques historiques des renderers délèguent au parser afin de
préserver le contrat appelé par la façade ; les règles invalides, les retours
CRLF et les valeurs contenant des espaces sont couverts.

La normalisation des options de calendrier est désormais confiée à
`QuickModeCalendarOptionsBuilder` (`a2e025609`). Les quatre renderers
réutilisent ce service pour les valeurs booléennes, les formats Pickadate, le
premier jour de la semaine et le nombre d'années sélectionnables ; les
snapshots de calendrier restent inchangés.

La construction de l'expression JavaScript d'accès aux éditeurs est désormais
confiée à `QuickModeEditorValueBuilder` (`43592c8bd`). Bootstrap et OnePage
conservent leurs méthodes publiques `getEditorContent()` comme délégations,
avec la sortie historique couverte par un test unitaire.

Le mapping Bootstrap 5 commun à Bootstrap et OnePage est désormais fourni par
`QuickModeBootstrapClassMapBuilder` (`658078588`). La méthode publique
`bsClass()` reste dans chaque renderer et les 54 associations historiques
restent couvertes par un test de mapping.

Le contrôle `bfCheckbox` est désormais extrait dans
`QuickModeCheckboxBuilder` et branché dans les quatre renderers par
`18a3e7ea`. Les variantes checked/unchecked, valeur échappée, attributs
d'événements et états de lecture seule sont couvertes ; les champs cachés
associés à `mailbackAccept` restent dans chaque renderer car ils appartiennent
au comportement d'enveloppe du champ.
La préparation commune du contrôle (valeur, état checked et désactivation
readonly) est maintenant portée par `QuickModeCheckboxStrategy`
(`40bf6269d`) ; les wrappers et les champs mailback restent propres aux
renderers.

Le contrôle `bfSelect` est désormais extrait dans `QuickModeSelectBuilder` et
branché dans les quatre renderers par `8a891fb5`. La stratégie couvre les
options sélectionnées, l'échappement des libellés et valeurs, `multiple`, les
attributs d'événements et les styles de dimensions. La différence historique
de `data-chosen` entre Mobile et les autres renderers est conservée par une
option explicite du builder.

Les contrôles élémentaires des groupes radio et checkbox sont désormais
partagés via `QuickModeGroupOptionBuilder` et branchés dans les quatre
renderers par `a85bed34`. Les différences d'enveloppe, de label, de saut de
ligne et de classe Bootstrap restent dans les renderers ; les valeurs et
attributs des options sont testés avec leurs variantes checked/unchecked.

Le rendu du champ `bfSubmitButton` est désormais partagé via
`QuickModeSubmitButtonBuilder` et branché dans Classic, Mobile et le trait
Bootstrap commun à Bootstrap/OnePage par `92b4af6f`. Les variantes bouton et
image, les attributs de thème et l'ordre HTML historique sont couverts ; les
callbacks JavaScript restent préparés dans chaque renderer.

Le bouton de déclenchement des calendriers est désormais partagé via
`QuickModeCalendarButtonBuilder` et branché dans Classic, Mobile et le trait
Bootstrap commun à Bootstrap/OnePage par `7911701f`. Les ordres d'attributs,
classes, valeurs et contenus d'icône/label propres aux renderers sont
préservés par les paramètres du builder.

L'input texte des calendriers est désormais partagé via
`QuickModeCalendarInputBuilder` et branché dans Classic, Mobile et le trait
Bootstrap commun à Bootstrap/OnePage par `1d623384`. Les classes de thème,
styles de largeur, valeur échappée et espacement historique de l'attribut
`id` sont couverts par les snapshots et le test unitaire du builder.

Les appels d'initialisation des calendriers responsive et Mobile sont désormais
partagés via `QuickModeCalendarInitScriptBuilder` et branchés par `1de4b129`.
Les paramètres JSON, le drapeau `hasYearScroller` et le libellé traduit sont
couverts ; le chargement des assets reste dans les renderers car il dépend du
cycle d'asset Joomla et de l'état local du renderer.

La construction de l'URL du CAPTCHA image est désormais partagée via
`QuickModeCaptchaUrlBuilder` et branchée dans les quatre renderers par
`21afa208`. Les variantes frontend et administrateur sont couvertes ; le
markup d'image, de saisie et de rechargement ainsi que les scripts CAPTCHA
restent séparés en raison de leurs différences de thème et de comportement.

Le markup commun de l'image CAPTCHA et du champ de réponse est désormais
centralisé dans `QuickModeCaptchaMarkupBuilder` et branché dans les quatre
renderers par `176af451`. Les attributs de dimensions, classes de thème,
valeurs échappées et espacements historiques sont couverts par les snapshots
et les tests unitaires ; les contrôles de rechargement restent spécifiques à
chaque renderer.

Le corps JavaScript de rechargement CAPTCHA est désormais centralisé dans
`QuickModeCaptchaReloadScriptBuilder` et branché dans les quatre renderers par
`4d706e95`. Le nettoyage du champ, le focus et l'ajout de
`bfMathRandom` sont couverts par un test unitaire ; les balises et icônes
visuelles restent propres à chaque renderer.

La normalisation des options d'upload est désormais centralisée dans
`QuickModeUploadOptionsBuilder` et branchée dans les quatre renderers par
`94a1345d`. Les extensions autorisées et la taille maximale sont couvertes
par des tests unitaires et alimentent toujours le même JavaScript historique.
Les options `multi_selection` et `runtimes` sont ensuite partagées par
`eaa777e8`, avec couverture des combinaisons HTML5/Flash/HTML4 et de la
sélection multiple. Les dimensions positives et les valeurs par défaut du
bouton d'upload sont partagées par `ddea69f8` dans les renderers Classic et
Mobile. La valeur numérique utilisée par le contrôle client de taille est
également fournie par le builder et consommée par les quatre renderers via
`9535e3a`, supprimant le dernier recalcul PHP de cette limite. Le markup et
les callbacks de configuration complète de l'uploader restent à extraire par
sous-lots.

Le callback JavaScript `bfUploadImageThumb()` est désormais généré par
`QuickModeUploadThumbnailScriptBuilder` (`704ccbea2`) pour les quatre
renderers. Le chemin Moxie, la destruction après intégration, la détection des
extensions image et le repli `FileReader` sont couverts par un test dédié ;
les différences de markup et la configuration plupload restent dans chaque
renderer.

La configuration commune de l'objet `plupload.Uploader` est désormais fournie
par `QuickModeUploadConfigurationBuilder` (`218990e11`) et utilisée par les
renderers Classic, Bootstrap et OnePage. Les différences d'URL SEF sont
injectées explicitement ; les callbacks et les enveloppes visuelles restent
propres à chaque renderer.

Les callbacks contigus d'entrée de file (`FilesAdded` initial et ajout des
fichiers) sont désormais composés par
`QuickModeUploadEntryCallbacksBuilder`; les variantes Bootstrap et Classic sont
couvertes par des tests dédiés.

Le corps commun du callback `FilesAdded` est désormais fourni par
`QuickModeUploadFileAddedHandlerBuilder` (`941f1ed6d`). La boucle de validation,
la création de la file d'attente et l'annulation sont ainsi mutualisées ; les
variantes de markup et de réactivation du bouton restent paramétrées par le
renderer.

Le callback plupload `UploadProgress` est désormais généré par
`QuickModeUploadProgressScriptBuilder` (`1e28a79b2`) pour les quatre renderers.
La mise à jour du pourcentage et de la barre visuelle reste inchangée et est
couverte par un test de sortie ; les callbacks d’ajout, d’erreur et de fin
d’upload restent à traiter par sous-lots.

Le callback plupload `FileUploaded` est désormais généré par
`QuickModeUploadCompletedScriptBuilder` (`6d3bfbb27`) pour les quatre
renderers. La restitution éventuelle de la réponse serveur et la suppression
de la ligne de queue sont couvertes par un test dédié ; la validation de la
liste de fichiers et la configuration complète de l’uploader restent à
extraire.

La validation client des fichiers est désormais générée par
`QuickModeUploadValidationScriptBuilder` (`fea7df7b6`) pour les quatre
renderers. La limite de taille, les extensions autorisées, les messages
d’erreur et le nettoyage des fichiers invalides sont couverts par un test dédié ;
les paramètres propres au champ restent injectés par chaque renderer.

La gestion d’annulation est désormais générée par
`QuickModeUploadCancelScriptBuilder` (`6cf0473ad`) pour les quatre renderers.
L’arrêt puis le redémarrage des uploaders, la suppression des lignes, le
compteur global et les deux variantes de réactivation du bouton sont couverts
par des tests dédiés.

La première boucle `FilesAdded`, qui notifie `bfUploadFileAdded()` et ajoute le
libellé nettoyé à `#bfFileQueue`, est désormais générée par
`QuickModeUploadQueueEntryScriptBuilder` (`e6956c7d5`). La variante Bootstrap
conservant sa ligne vide historique est paramétrée explicitement et la sortie
est couverte par un test dédié.

Le markup de ligne `bfFileQueueItem` est désormais généré par
`QuickModeUploadQueueItemMarkupBuilder` (`e081b2f44`). Les variantes de nom
conditionnel des renderers Bootstrap/OnePage, l’attribut `border` de Mobile et
les chemins d’image d’annulation sont paramétrés explicitement et couverts
par des tests dédiés.

Les quatre renderers ne possèdent plus chacun leurs cinq adaptateurs privés de
calendrier : `CalendarOptionsTrait` (`05090635f`) délègue les booléens, le
format Pickadate, le premier jour et la plage d'années au builder partagé. Les
sorties QuickMode restent inchangées et la duplication d'orchestration est
réduite sans créer de classe de base commune.

Le callback OnePage `bf_validate_prevpage` est désormais construit par
`QuickModePreviousPageValidationBuilder` (`f19eb5f89`). Les chemins d'erreur,
la validation de la page courante, le déplacement vers la page précédente et
le commentaire historique conservé sont couverts par un test de sortie.

Le callback `bf_validate_nextpage` est désormais construit par
`QuickModeNextPageValidationBuilder` (`114dd4d17`). La validation de la page
courante, la gestion des erreurs, l'arrêt de Ladda et l'activation de la page
suivante sont couverts par un test dédié.

Le callback `bf_validate_submit` est désormais construit par
`QuickModeSubmitValidationBuilder` (`280fccd6e`). Les chemins d'erreur et de
succès, le focus, la restauration du bouton et l'appel à `ff_submitForm()`
sont couverts par un test dédié ; les responsabilités de restauration du
bouton sont désormais isolées dans `QuickModeSubmitButtonRestoreBuilder`
(`bfe93521e`) ; elles conservent leurs dépendances JavaScript historiques.

Le conteneur d'erreur Bootstrap est désormais construit par
`QuickModeErrorMessageMarkupBuilder` (`9d5a288c2`) et partagé par Bootstrap et
OnePage. Les classes de thème restent fournies par chaque renderer, tandis
que le markup caché commun est testé indépendamment ; les variantes Classic et
Mobile restent distinctes.

Le widget de progression est désormais construit par
`QuickModeProgressMarkupBuilder` (`5cb2da294`) et partagé entre Bootstrap et
OnePage. Le calcul de progression, la remise à zéro et l'exclusion éventuelle
de la page Thank You sont testés sans déplacer la gestion des assets hors des
renderers.

La déclaration runtime des erreurs est désormais construite par
`QuickModeErrorRuntimeConfigBuilder` (`45fc45988`). Bootstrap et OnePage
partagent les trois variables de configuration, tandis que la portée des
erreurs reste explicitement paramétrée (`false` pour Bootstrap, `true` pour
OnePage) et testée.

La fermeture du modal Thank You est désormais construite par
`QuickModeRemodalCloseScriptBuilder` (`ca2b8f42`). La priorité donnée à
`crbc_cart_url`, puis le retour vers l'URL courante du formulaire, restent
inchangés et sont couverts par un test dédié.

La soumission AJAX OnePage est désormais construite par
`QuickModeAjaxSubmitScriptBuilder`. L'envoi sérialisé, le verrouillage visuel
des pages, la gestion d'échec, la redirection panier/URL courante et les deux
branches de succès (page Thank You ou redirection directe) sont couverts par
un test de sortie ; l'orchestrateur conserve le choix de la page Thank You et
l'injection des messages et de l'URL Joomla.

La baseline PHPCS est maintenant sans erreur sur les services modernes, les
quatre renderers QuickMode et leurs traits (`e25d501f`, `14985d4d`, `288b42a4`,
`72204865`, `212b6450`, `aaabc40c`). Le ruleset complet est vert ; la qualité
de formatage n'est donc plus un prérequis bloquant pour les extractions de
comportement suivantes.

## Vérification obligatoire par lot

## Lots récents

`ContentBuilderSignatureScriptBuilder` protège désormais le sélecteur de champ
par un littéral JavaScript JSON strict ; `QuickModeCaptchaMarkupBuilder`
échappe les valeurs d'attribut HTML et `QuickModeCaptchaReloadScriptBuilder`
protège l'URL injectée dans le callback de rechargement. Les tests couvrent
les entrées contenant des guillemets et du code JavaScript ; les snapshots
CAPTCHA des quatre thèmes ont été actualisés pour l'encodage HTML attendu
(`8ed97fb3c`).

`QuickModeUploadQueueItemMarkupBuilder` échappe maintenant le chemin de
l'image d'annulation à la fois pour le fragment HTML et pour la chaîne
JavaScript qui l'embarque. Une régression d'injection dans ce chemin est
couverte par un test dédié (`5aec57c30`).

`QuickModeUploadThumbnailScriptBuilder` protège la base d'URL de `Moxie.swf`
dans son littéral JavaScript (`a5f57ce15`). Les messages de taille et
d'extension ainsi que la liste d'extensions sont désormais encodés au point
de construction par `QuickModeUploadValidationScriptBuilder`, ce qui retire
le pré-encodage divergent des trois renderers (`474408bee`). Les sorties
normales et les chemins d'upload des quatre thèmes sont caractérisés.

Les endpoints injectés dans les callbacks CAPTCHA historiques sont désormais
échappés comme contenu de littéral JavaScript par
`CaptchaLegacyValidationScriptBuilder` et
`CaptchaReCaptchaValidationScriptBuilder`. Les flux AJAX legacy et ReCaptcha
conservent leurs délimiteurs et leur comportement, avec couverture des URL
hostiles (`b80c88d37`).

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
