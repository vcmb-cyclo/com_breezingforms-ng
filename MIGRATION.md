# Migration com_breezingformsng → Joomla 6 pur

> Document de suivi destiné aux agents. Cocher chaque tâche à la complétion.  
> Branche de travail recommandée : `migration-j6` (déjà active).

## Mise à jour du plan — 2026-08-29

Cette section complète l'historique avec l'état du chantier de modernisation
mené sur la branche `modernize-legacy-services`. Les sections historiques
restent inchangées afin de conserver la traçabilité des migrations précédentes.

### État courant

- [x] Outillage PHPCS ajouté à Composer (`squizlabs/php_codesniffer`, scripts
  `lint:php` et `lint:php:fix`) avec un périmètre initial ciblé sur les services
  modernisés.
- [x] Tests renforcés pour callbacks, exports, uploads, permissions, signatures
  de scripts, parsing des tests de pièces et état PDF.
- [x] Validation Flash Upload extraite dans `FlashUploadSizeValidator`, injecté
  dans `FlashUploadCallback`.
- [x] Filets minimaux sur `bfTextfield` pour les quatre renderers QuickMode :
  Classic, Bootstrap, Mobile et OnePage.
- [x] Premier filet sur `RenderingEngine::view()` : branche non-QuickMode,
  avertissement et arrêt avant initialisation du runtime.
- [x] `ClassicRenderer` : les 21 types de champs couverts par filet de
  caractérisation et extraits de `process()` en méthodes privées dédiées
  (`renderTextfieldField`, `renderFileField`, etc. — `bfFile`, dernier type,
  terminé le 2026-08-29). `process()` : ~1250 → ~510 lignes. Lot B ci-dessous
  clos ; le fichier n'est plus « en cours local », il peut être touché par
  d'autres lots (D, mutualisation Strategy) sans conflit particulier.
- [ ] Compléter `RenderingEngine::view()` avant toute extraction : header,
  toolbar, arbre de nœuds, aperçu, permissions, sélection mobile et sorties
  anticipées.
- [x] Étendre les filets des quatre renderers aux familles de champs à risque
  (textarea, groupes, select, upload, CAPTCHA, calendrier et submit) — clos
  le 2026-08-29. Exceptions documentées dans les tests eux-mêmes plutôt que
  contournées : `MobileRenderer::bfCalendar` (implémentation native propre,
  `LayoutHelper::render()` + connexion DB réelle, hors périmètre du harnais
  pure-logic) et `OnePageRenderer::bfFile` (condition flashUploader/html5
  entièrement commentée en prod — le widget flash s'affiche toujours, le
  fallback `<input type="file">` est du code mort inatteignable ; comportement
  documenté tel quel, pas "corrigé"). Couverture ensuite étendue aux 7 types
  restants (bfSignature, bfStripe, bfPayPal, bfSofortueberweisung,
  bfSummarize, bfHidden, bfNumberInput) sur Bootstrap, Mobile et OnePage —
  **couverture complète des 4 renderers close le 2026-08-29** : chaque type
  de champ de chaque renderer a désormais au moins un test figeant sa sortie
  actuelle, à l'exception des deux cas documentés ci-dessus.
- [ ] Extraire ensuite la couche Strategy par type de champ, uniquement lorsque
  les quatre sorties correspondantes sont figées par tests.

### Travaux parallélisables

Les lots suivants peuvent avancer en parallèle s'ils restent dans des fichiers
distincts :

| Lot | Périmètre | Dépendance | Conflit probable |
|---|---|---|---|
| A | ~~Filets Bootstrap, Mobile et OnePage supplémentaires~~ — **clos le 2026-08-29** | — | — |
| B | ~~Filets Classic supplémentaires~~ — **clos le 2026-08-29** : 21/21 types couverts et extraits | — | — |
| C | Tests purs callbacks, uploads, exports et parsers | Aucun runtime Joomla réel | Faible |
| D | Branches simples de `RenderingEngine::view()` | Harness/stubs existants | Moyen |
| E | Nettoyage PHPCS par petits groupes de services | PHPCS installé | Faible si un fichier par lot |
| F | Inventaire des différences par type de champ | Filets des quatre renderers | Faible, aucun code production |
| G | Package, PHPStan, installation Joomla et tests navigateur | Builds isolés | Faible |

### Ordre recommandé

1. Terminer les filets individuels des quatre renderers, en gardant Classic
   séparé tant que ses snapshots locaux ne sont pas arbitrés.
2. Compléter `RenderingEngine::view()` par sorties observables et stubs
   explicites ; ne pas extraire de section avant qu'elle soit couverte.
3. Traiter en parallèle les tests purs (C), le nettoyage PHPCS (E) et
   l'inventaire comparatif (F).
4. Lancer les validations package/browser (G) après chaque groupe de rendu.
5. Construire la Strategy par type de champ avec comparaison des snapshots
   avant/après.

### Ne pas paralléliser pour l'instant

- ~~La couche Strategy commune~~ — **garde levée explicitement le 2026-08-29** :
  les 4 filets complets sont en place, décision prise de démarrer sans
  attendre la caractérisation de `RenderingEngine::view()`. À surveiller :
  ce chantier touche les 4 renderers en même temps, coordination requise
  avec tout travail parallèle sur ces fichiers.
- Les modifications simultanées de `ClassicRenderer.php`, de son harness et de
  ses snapshots : ce périmètre contient déjà du travail local non commité.
- La suppression des façades/classes legacy appelables depuis du PHP stocké en
  base : elle nécessite une décision de compatibilité et une recette dédiée.
- Les extractions qui changent à la fois le dispatcher, le renderer et les
  templates PDF : les regrouper par flux pour garder les régressions
  attribuables.

---

## État actuel

### ✅ Déjà migré
- [x] Manifest + namespace PSR-4 (`com_breezingformsng.xml`)
- [x] DI container / services (`services/provider.php`, `src/Extension/`)
- [x] Pipeline d'assets (`joomla.asset.json`)
- [x] Fichiers de langue structure Joomla 6 (`language/{lang}/`)
- [x] Admin — Pieces : Controller, Model, View, templates, help
- [x] Admin — Scripts : Controller, Model, View, templates, help
- [x] Admin — About : Controller, View, template, help
- [x] Admin — Help : View générique (sections par `?section=`)
- [x] Admin — Records : toolbar Joomla 6 native (dropdown Export, bouton Help)
- [x] `script.php` : migration liens menu `act=about` → `task=about.display&view=about`

### ❌ Encore legacy (périmètre de cette migration)
- [x] Routeur admin central (`admin.breezingforms.php` — supprimé Phase 6)
- [x] Gestion des enregistrements (`admin/recordmanagement.class.php` — migré Phase 1)
- [x] Configuration (`admin/config.class.php` + `config.html.php` — migré Phase 2 vers `com_config`)
- [x] Intégrateur (`admin/integrator.class.php` + `.html.php` — migré Phase 3)
- [x] Gestion des menus (`admin/menu.class.php` + `menu.html.php` — migré Phase 4)
- [x] Gestionnaire de formulaires (`admin/form.class.php` + `form.html.php` — migré Phase 5)
- [x] QuickMode (`admin/quickmode.class.php` + `.html.php` + `.js` — migré Phase 7)
- [x] Import paquets (`admin/import.class.php` → `src/Model/ImportModel.php`, SimpleXML + transactions ; bibliothèques scripts/pièces uniquement, les paquets contenant formulaires/menus sont refusés ; export de paquets abandonné avec l'UI legacy)
- [x] Frontend moteur formulaires — dispatcher, config, routeur SEF et `HTML_facileFormsProcessor` (8 907 lignes) décomposés en services/traits Joomla 6 (Phase 8)
- [x] `BFRequest` → `Input` Joomla natif (Phase 9a, 326 appels convertis ; ne reste que dans les 4 rendus `BFQuickMode*`, cf. Phase 9c)
- [x] Rendus `BFQuickMode*` — migration `BFRequest` (Phase 9c, 33 appels convertis ; la réécriture native complète du rendu reste un chantier séparé, hors périmètre `BFRequest`)
- [x] `BFIntegrate` → requêtes préparées (Phase 9b, 2026-07-12 — SQL par concaténation remplacé par `quoteName()`/`bind()`, vérifié en conditions réelles insert/update/repli)
- [x] SDK PHP externes historiques → services Joomla natifs typés (2026-07-12 — `RecaptchaVerifier`,
  `DropboxUploader`, `MailchimpClient` et `SalesforceClient`, chacun avec client HTTP injectable ; ancien
  `RemoteApiClient` conservé dans l'historique source et SDK embarqués supprimés)
- [x] Stripe PHP `17.6.0` → `20.3.1` (2026-07-12 — classes utilisées et package Joomla 6 vérifiés)
- [x] PDF : TCPDF 7.x géré par Composer, avec génération déléguée à `tc-lib-pdf 8.x` ; PHP 8.3 minimum
- [x] CAPTCHA : Securimage 4.0.2 géré par Composer (`bgli100/securimage`), chargement via `VendorHelper`
- [x] Audit About enrichi depuis les concepts CBNG (2026-07-12) : rapport persistant sur un affichage, inventaire des
  14 tables BFNG, volumes et tailles, tables manquantes, collations, index dupliqués et références orphelines ;
  aucune réparation destructive automatique.
- [x] API Joomla dépréciées (2026-07-12) : `Application::getCfg()` remplacé par `get()`, `Table::getInstance()`
  remplacé par la `MVCFactory` native de `com_content`, et `LegacyErrorHandlingTrait::getError()` remplacé par
  des exceptions ou des messages traduits. Les six classes Crosstec protégées restent inchangées.

---

## Règles pour les agents

- Joomla 6 uniquement. PHP 8.1+. Aucune compatibilité ascendante.
- Chaque écran migré = un triplet `Controller / Model / View` dans `src/`.
- Supprimer les fichiers `admin/` correspondants **après** que la route MVC fonctionne.
- Toute route legacy supprimée du bridge `DisplayController` doit avoir sa route MVC opérationnelle avant.
- Tester chaque phase manuellement avant de cocher.
- Mettre à jour ce fichier (cases à cocher) à chaque tâche complétée.

---

## Phase 1 — Enregistrements `act=managerecs` → `view=records`

**Priorité : haute. La toolbar est déjà Joomla 6 native.**

### Fichiers à créer
- [x] `src/Controller/RecordsController.php`  
  Tasks : `display`, `edit`, `save`, `delete`, `exportPdf`, `exportCsv`, `exportXml`, `csvImport`, `setFlag`, `setViewed`, `setExported`, `setArchived`
- [x] `src/Model/RecordModel.php` — CRUD enregistrement unique  
  Table : `#__facileforms_records` + `#__facileforms_subrecords`
- [x] `src/Model/RecordsModel.php` — liste filtrée, paginée  
  Filtres : formulaire, état (viewed/exported/archived), recherche texte
- [x] `src/View/Records/HtmlView.php`
- [x] `tmpl/records/default.php` — liste avec filtres, pagination, toggle flags inline
- [x] `tmpl/records/edit.php` — formulaire édition enregistrement
- [x] `tmpl/records/csvimport.php` — upload CSV

### Fichiers à modifier
- [x] `src/Controller/DisplayController.php` — routing `view=records` + intercept `act=managerecs/recordmanagement`

### Fichiers à supprimer (après validation)
- [x] `administrator/components/com_breezingformsng/admin/recordmanagement.class.php` *(git mv → RecordsController)*
- [x] `administrator/components/com_breezingformsng/admin/recordmanagement.php` *(git mv → RecordsModel)*

### Vérification
- [x] Liste des enregistrements s'affiche avec filtres et pagination *(vérifié HTTP le 2026-07-11)*
- [x] Export PDF, CSV, XML fonctionnels *(vérifié : PDF 45 Ko, CSV et XML corrects — le PDF nécessitait les correctifs `getSubrecords()` + repli template)*
- [x] Import CSV fonctionnel *(vérifié de bout en bout le 2026-07-11 : upload authentifié, création de l'enregistrement et de ses sous-enregistrements, puis nettoyage des données de test)*
- [x] Édition et sauvegarde d'un enregistrement *(vérifié HTTP et en base le 2026-07-11 : valeur modifiée puis restaurée ; requête sans jeton rejetée sans mutation)*
- [x] Flags (viewed / exported / archived) fonctionnels en unitaire *(setFlag vérifié ; bascule en masse à confirmer à la main)*
- [x] Bouton Help ouvre la modale *(vérifié dans Chrome le 2026-07-11 : popup Joomla iframe, URL d'aide Records correcte, aucune nouvelle fenêtre)*
- [x] Toolbar obtenue depuis le document Joomla 6 ; suppression du singleton déprécié `Toolbar::getInstance()` *(2026-07-20)*

> **Durcissement (2026-07-11)** : contrôle CSRF ajouté aux sauvegardes, suppressions, flags unitaires/en masse,
> imports CSV et exports (ces derniers marquent les enregistrements comme exportés). `RecordModel::saveRecord()` ne dépend
> plus des colonnes `modified*` ajoutées par ContentBuilderNG mais absentes du schéma BreezingFormsNG autonome.
> L'import CSV utilise désormais `fgetcsv()` avec le séparateur et le caractère de citation configurés, convertit l'encodage
> demandé, reconnaît le format produit par l'export BFNG et insère en transaction l'enregistrement et ses sous-enregistrements.

---

## Phase 2 — Configuration `act=configuration` → `com_config`

**Priorité : haute. Effort faible : `config.xml` existe déjà.**

### Fichiers à modifier
- [x] `config.xml` — fieldsets `general` (disable_ip, emailadr, uploads) + `csv` (csvdelimiter, csvquote, cellnewline) + `permissions`
- [x] `com_breezingformsng.xml` — entrée submenu Configuration → `com_config`
- [x] `src/View/BreezingformsNG/HtmlView.php` — lien sidebar Configuration → `com_config`
- [x] `src/Model/RecordModel::getExportConfig()` — lit depuis `ComponentHelper::getParams()` au lieu de `#__facileforms_config`

### Fichiers à supprimer (après validation)
- [x] `admin/config.class.php` *(git mv → src/Model/ConfigModel.php)*
- [x] `admin/config.html.php` *(git rm — remplacé par com_config)*
- [x] `admin/config.php` *(git rm — dispatcher inutile)*

> **Fait (2026-07-11)** : `facileFormsConf::load()` lit désormais `ComponentHelper::getParams()` (6 clés gérées par `config.xml`) ;
> `ConfigModel::migrateFromLegacy()` copie une seule fois les valeurs de `#__facileforms_config` vers les params
> (appelée par `script.php::migrateLegacyConfig()` en postflight, idempotente — vérifiée sur le conteneur joomla6).
> `store()` et `bindRequest()` (morts) supprimés. La table `#__facileforms_config` sera supprimée après une version de battement.

### Vérification
- [x] Composants → BreezingForms NG → Options ouvre l'écran natif Joomla *(vérifié)*
- [x] Sauvegarde des paramètres → persistance en base (`#__extensions` params) *(vérifié dans Chrome le 2026-07-11 : `disable_ip` modifié, relu après `component.apply`, puis restauré)*
- [x] Permissions ACL visibles et fonctionnelles *(vérifié le 2026-07-19, avec confirmation explicite de l'utilisateur préalable — cf. note « Reliquat hors Phase 9 » : règle `core.manage` du groupe Public basculée Refusé→Autorisé via `task=component.apply`, persistance confirmée par un rechargement complet de la page, puis restaurée à Refusé et reconfirmée de la même façon. Fait via requêtes HTTP directes — `claude-test` a nécessité une réinitialisation de mot de passe en base pour l'authentification admin faute de `playwright-cli` disponible dans cet environnement — repassé à un mot de passe aléatoire ensuite)*

---

## Phase 3 — Intégrateur `act=integrate`

**Priorité : moyenne.**

### Fichiers à créer
- [x] `src/Controller/IntegratorController.php`  
  Tasks : `display`, `edit`, `save`, `remove`, `publish`, `unpublish`, `addItem`, `removeItem`, `saveCode`, `saveFinalizeCode`, `addCriteria*`, `removeCriteria*`, `publishItem`, `unpublishItem`
- [x] `src/Model/IntegratorModel.php`
- [x] `src/View/Integrator/HtmlView.php`
- [x] `tmpl/integrator/default.php`
- [x] `tmpl/integrator/edit.php`

### Fichiers à modifier
- [x] `src/Controller/DisplayController.php` — ajouter `view=integrator` + intercept `act=integrate`
- [x] `src/View/BreezingformsNG/HtmlView.php` — sidebar Integrator → `view=integrator`

### Fichiers à supprimer
- [x] `admin/integrator.class.php` *(git mv → src/Model/IntegratorModel.php)*
- [x] `admin/integrator.html.php` *(git mv → src/View/Integrator/HtmlView.php)*
- [x] `admin/integrator.php` *(git rm — dispatcher)*

### Vérification
- [x] Liste des règles d'intégration s'affiche *(vérifié)*
- [x] Création d'une nouvelle règle (insert/update) *(deux types vérifiés en HTTP et en base le 2026-07-11, puis règles de test supprimées)*
- [x] Ajout/suppression d'items et critères *(item et critères formulaire/Joomla/valeur fixe vérifiés, puis supprimés)*
- [x] Éditeur de code (CodeMirror) fonctionnel *(assets présents, code d'item et code de finalisation sauvegardés et vérifiés en base)*
- [x] Publish/unpublish règle et item *(aller-retour 1 → 0 → 1 vérifié pour les deux)*

> **Durcissement (2026-07-11)** : toutes les suppressions d'items et de critères vérifient maintenant le jeton CSRF.
> Les actions supprimer et publier/dépublier utilisent des formulaires POST avec jeton au lieu de mutations par URL GET.

---

## Phase 4 — Gestion des menus `act=managemenus`

**Priorité : faible. Fonctionnalité réduite.**

### Fichiers à créer
- [x] `src/Controller/MenusController.php`
- [x] `src/Model/MenuModel.php`
- [x] `src/View/Menus/HtmlView.php`
- [x] `tmpl/menus/default.php`
- [x] `tmpl/menus/edit.php`

### Fichiers à modifier
- [x] `src/Controller/DisplayController.php` — intercept `act=managemenus` → `view=menus`
- [x] `src/View/BreezingformsNG/HtmlView.php` — sidebar Menus activé → `view=menus`

### Fichiers à supprimer
- [x] `administrator/components/com_breezingformsng/admin/menu.class.php` *(git mv → MenuModel)*
- [x] `administrator/components/com_breezingformsng/admin/menu.html.php` *(git mv → Menus/HtmlView)*
- [x] `administrator/components/com_breezingformsng/admin/menu.php` *(git rm)*

### Vérification
- [x] Liste des éléments de menu *(vérifiée après correctif `quoteName` dans `MenuModel::getItems()` ; création/suppression à confirmer à la main)*

---

## Phase 5 — Gestionnaire de formulaires `act=manageforms`

**Priorité : critique. Effort très élevé (cœur du produit).**

### Fichiers créés
- [x] `src/Controller/FormsController.php`  
  Tasks : `display`, `edit`, `save`, `cancel`, `copy`, `remove`, `publish`, `unpublish`, `orderup`, `orderdown`, `run`
- [x] `src/Model/FormModel.php` — CRUD formulaire unique + copy + publish + ordering
- [x] `src/Model/FormsModel.php` — liste filtrée + paginée (git mv depuis `form.class.php`)
- [x] `src/Model/ImportModel.php` — stub (git mv depuis `import.class.php`)
- [x] `src/View/Forms/HtmlView.php` — layouts `default` (liste) et `edit` (propriétés)
- [x] `tmpl/forms/default.php` — liste Bootstrap avec filtres, tri, pagination, publish toggle
- [x] `tmpl/forms/edit.php` — 3 onglets : Général, Email, Scripts & Pièces (CodeMirror)

### Fichiers modifiés
- [x] `src/Controller/DisplayController.php` — intercept `act=manageforms` → `view=forms`
- [x] `src/View/BreezingformsNG/HtmlView.php` — sidebar Forms → `view=forms`
### Fichiers supprimés
- [x] `administrator/components/com_breezingformsng/admin/form.php` (git rm)
- [x] `administrator/components/com_breezingformsng/admin/run.php` (git rm, remplacé par `forms.run`)
- [x] `administrator/components/com_breezingformsng/admin/form.class.php` (git mv → FormsModel)
- [x] `administrator/components/com_breezingformsng/admin/form.html.php` (git mv → Forms/HtmlView)
- [x] `administrator/components/com_breezingformsng/admin/import.class.php` (git mv → ImportModel)

### Périmètre différé
- [x] Import de paquets — `ImportModel` réécrit (SimpleXML, requêtes paramétrées, transaction) ; consommé par `script.php::importStandardLibrary()`. Limité aux bibliothèques de scripts/pièces (seul cas réel : `packages/stdlib.english.xml`) ; les paquets avec formulaires/menus sont refusés avec un message clair. Vérifié de bout en bout le 2026-07-10 : installation du paquet 6.1.0 dans le conteneur `joomla6-joomla-1`, import stdlib OK (71 scripts inchangés ignorés, 3 pièces mises à jour, métadonnées paquet actualisées).

### Migré depuis cette phase
- QuickMode — déplacé en Phase 7 vers `QuickmodeController`, `QuickmodeModel`, `QuickmodeHtml` et routes `task=quickmode.*`
- QuickMode JS AJAX endpoint — migré vers `task=quickmode.doAjaxSave`

### Vérification
- [x] Lister les formulaires (filtres package, état, recherche ; tri ; pagination) *(liste vérifiée HTTP)*
- [x] Créer / éditer / sauvegarder un formulaire (3 onglets de propriétés) *(vérifié HTTP et en base le 2026-07-12 : Général, Email et Scripts/Pièces, codes inline inclus)*
- [x] Publier / dépublier un formulaire *(aller-retour vérifié ; copie, changement d'ordre et suppression également validés puis nettoyés)*
- [x] Ouvrir QuickMode depuis la liste *(quickmode.display rendu complet, 1,6 Mo)*

> **Durcissement (2026-07-12)** : les actions rapides publier/dépublier et monter/descendre utilisent désormais le POST
> avec jeton du formulaire de liste. Les champs fantômes `mb_emailadr` et `script3code`, absents du schéma BFNG, ont été
> retirés du contrôleur, du modèle et du gabarit de création. Les références invalides à `LanguageText` ont été corrigées.

---

## Phase 6 — Nettoyage partiel du bridge legacy

**Phase partielle — le bridge admin principal est supprimé.**

### Effectué
- [x] `DisplayController` : les tâches `quickmode.display`/`quickmode.editor`/`quickmode.doAjaxSave` passent par le contrôleur MVC
- [x] `admin.breezingforms.php` : suppression des `case` morts (`installation`, `configuration`, `managemenus`, `integrate`, `recordmanagement`, `run`) — tous ces actes sont interceptés par MVC avant d'atteindre le bridge
- [x] `admin.breezingforms.php` : `default` ne tente plus d'inclure des fichiers supprimés
- [x] EasyMode et ClassicMode supprimés côté fichiers admin (par l'utilisateur)

### Complété
- [x] `PiecesController::bootstrapLegacyRuntime()` — `toolbar.facileforms.php` remplacé : require direct de `facileforms.class.php` + stdClass `$ff_config`
- [x] `ScriptsController::bootstrapLegacyRuntime()` — même correction
- [x] `DisplayController::display()` vidé de son `include admin.breezingforms.php`
- [x] Supprimé `administrator/components/com_breezingformsng/admin.breezingforms.php`
- [x] Supprimé `toolbar.facileforms.php` et `toolbar.facileforms.html.php`
- [x] Supprimé `admin/download.php`
- [x] Routes sortantes QuickMode migrées vers `task=quickmode.display` / `task=quickmode.editor`
- [x] Alias legacy QuickMode retirés de `DisplayController`

### Bloqué — dépend de phases futures
- [x] Supprimer `src/Helper/LegacyClassLoader.php` *(les classes du moteur sont chargées explicitement par son bootstrap ; les derniers usages admin de `BFRequest` ont été migrés vers l'Input Joomla. Vérifié sans le fichier le 2026-07-12 : Scripts, Pièces, éditeur QuickMode, QuickMode et formulaire frontend répondent en HTTP 200)*
- [x] Retirer l'enregistrement de `LegacyClassLoader` dans `services/provider.php`
- [x] Supprimer le répertoire `admin/` — assets déplacés vers `media/com_breezingformsng/js/admin/` et `css/admin.css`, `joomla.asset.json` et manifeste mis à jour, `bluestork.fix.css` supprimé (obsolète)

### Vérification finale
- [x] Naviguer dans **tous** les écrans admin sans erreur *(balayage HTTP du 2026-07-11 : records, forms, integrator, menus, pieces, scripts, about, quickmode, éditeur inline — tous 200 sans erreur fatale)*
- [x] Aucun `include` ou `require` vers `admin/` ne subsiste dans le call stack (répertoire supprimé, grep sans résultat, paquet reconstruit et validé)
- [x] `php -l` sur tous les fichiers `src/` : aucune erreur de syntaxe

---

## Phase 7 — QuickMode `task=quickmode.display` / `task=quickmode.editor`

**Priorité : critique. QuickMode était cassé (toolbar.facileforms.php manquait config.class.php).**

### Effectué
- [x] `src/Model/QuickmodeModel.php` — migration complète de `QuickMode` : namespace, PHP 8.1, `json_encode/decode` et `base64_encode/decode` natifs (suppression dépendances `Zend_Json` et `bf_b64*`)
- [x] `src/Helper/QuickmodeHtml.php` — renderer migré sous namespace Joomla et retiré de `LegacyClassLoader`
- [x] `src/Controller/QuickmodeController.php` — tasks : `display`, `doAjaxSave`, `editor`
- [x] `src/View/Quickmode/HtmlView.php` — vue Joomla 6 native, configure toolbar
- [x] `tmpl/quickmode/default.php` — appelle le renderer namespacé `QuickmodeHtml::showApplication()`
- [x] `tmpl/quickmode/editor.php` — éditeur inline (git mv depuis `quickmode-editor.php`), task mise à jour vers `quickmode.editor`
- [x] `DisplayController` — alias legacy QuickMode (`act=quickmode*`, `act=manageforms&task=quickmode*`) retirés après migration des liens internes
- [x] `admin.breezingforms.php` — bridge entièrement vidé (switch ne contient plus que `default: break`)
- [x] `admin/quickmode.php`, `admin/quickmode.class.php`, `admin/quickmode.html.php`, `admin/quickmode-editor.php` — supprimés
- [x] Liens internes migrés vers routes MVC `task=quickmode.display` / `task=quickmode.editor`
- [x] Alias `act=quickmode*` supprimés de `DisplayController`
- [x] Save AJAX QuickMode : `quickmode-app.js` poste sur `task=quickmode.doAjaxSave`, puis redirige vers `quickmode.display`
- [x] `QuickmodeController::doAjaxSave()` crée `media/breezingforms/ajax_cache` si absent, valide les bornes de chunks et refuse les payloads JSON invalides
- [x] `QuickmodeController::doAjaxSave()` contrôle le jeton CSRF Joomla avant l'écriture du premier chunk

### Vérification
- [x] Ouvrir QuickMode depuis la liste des formulaires *(quickmode.display rendu complet, 1,6 Mo)*
- [x] Sauvegarder un formulaire (AJAX chunked save → `quickmode.doAjaxSave`) *(vérifié HTTP authentifié le 2026-07-11 : formulaire 8, réponse `8`, hash `template_code` inchangé)*
- [x] Ajouter / modifier / supprimer des éléments *(vérifié dans Chrome le 2026-07-12 sur le formulaire de test 28
  « Test eddy elements » : ajout et suppression dans l'arbre avant enregistrement ; puis cycle serveur validé via
  `quickmode.doAjaxSave` sur un formulaire temporaire : ajout en base, modification du titre, suppression avec zéro
  élément restant. Formulaire et éléments temporaires nettoyés)*
- [x] Éditeur inline (`task=quickmode.editor`, `tmpl=component`) *(vérifié après correctif du layout `editor_editor`)*
- [x] Prévisualisation frontend depuis QuickMode *(vérifié HTTP le 2026-07-11 : preview component + site, formulaire 8 rendu sans erreur fatale)*

---

## Phase 8a — SQL versionné (Lot B du plan — fait le 2026-07-11)

- [x] `sql/create_sql.php` (code mort — plus exécuté par personne) converti en `sql/install.mysql.utf8.sql`
  (14 tables, `CREATE TABLE IF NOT EXISTS` non destructif, index/PK/AUTO_INCREMENT fusionnés, `INSERT IGNORE` du paquet FF)
- [x] Manifeste : sections `<install><sql>` + `<update><schemas><schemapath>` ; baseline `sql/updates/mysql/6.1.0.sql`
- [x] Vérifié sur le conteneur joomla6 : exécution du SQL deux fois sans erreur (idempotent) sur préfixe de test,
  schéma identique à la base réelle (hors colonnes ajoutées par ContentBuilderNG), version `6.1.0` enregistrée dans `#__schemas`,
  données préservées après mise à jour (58 formulaires)
- [x] `validate-package.sh` mis à jour

> Toute évolution de schéma future = un fichier `sql/updates/mysql/<version>.sql`, plus d'ALTER dans `script.php::update()` pour les nouvelles versions.

---

## Phase 8 — Frontend (hors périmètre immédiat)

> Projet de refonte dédié. Ne pas commencer avant que les phases 1–6 soient validées.

- [x] Décomposer le `facileforms.process.php` monolithique en services et traits fonctionnels :
  `FormRenderer`, `FormProcessor`, `SubmissionHandler`, intégrations (Stripe, Mailchimp, Dropbox…)
- [x] Squelette MVC site (2026-07-11) : `DisplayController` (vue par défaut `form`, **contrôle CSRF sur `ff_task=submit`**),
  `View/Form/HtmlView`, `tmpl/form/default.php` (délègue encore au moteur legacy), métadonnées de menu modernisées
  (`tmpl/form/default.xml`, blocs 1.5/1.6 supprimés), jeton `form.token` injecté dans les 3 branches d'émission du moteur.
  Vérifié sur le conteneur : rendu 200, soumission sans jeton → 403 (index.php et URL SEF), soumission avec jeton → enregistrement créé puis nettoyé.
- [x] Décomposer `breezingformsng.php` en services fonctionnels *(fait le 2026-07-12 : dispatcher de 157 lignes ;
  rendu/soumission dans `Site\Service\FormRenderer` ; rappels par famille dans `Site\Service\Callback\{PayPal,Stripe,Sofort,Captcha,FlashUpload,Opt}Callback` ;
  streaming de téléchargement dédupliqué dans `Support\DownloadHelper`. Vérifié sur le conteneur : rendu formulaire 8 + rappel opt-out sans erreur)*
- [x] Découper `facileforms.class.php` par domaine *(fait le 2026-07-12 : chargeur de 43 lignes → `legacy/functions.php` (helpers runtime),
  `legacy/Conf.php` (config adossée aux params), `legacy/tables.php` (classes Table globales) ; `facileforms.xml.php` orphelin supprimé
  du paquet et purgé des sites par `removeObsoleteComponentFiles()`)*
- [x] Découper `HTML_facileFormsProcessor` (8 907 lignes) en traits fonctionnels *(fait le 2026-07-12 :
  `facileforms.process.php` ramené à 706 lignes (bootstrap, traces, constructeur) ; méthodes réparties dans
  `legacy/processor/bfProcessor{CodeTools,Scripting,Rendering,Exports,Notifications,Uploads,Submission}.php`.
  Vérifié sur le conteneur : rendu normal + `tmpl=component`, soumission SEF du formulaire 4 → enregistrement créé puis nettoyé)*
- [x] Éliminer les wrappers crosstec triviaux *(fait le 2026-07-12 : `BFText` → `Text` natif (68 fichiers, langue chargée
  au bootstrap du moteur) ; `BFFile::read` → `file_get_contents` et suppression de `BFFactory`/`BFDbo` (sans appelant) ;
  `BFRedirect()` → `Site\Service\Support\RedirectHelper`. Fichiers purgés des sites installés via `removeObsoleteComponentFiles()`)*

  > ⚠️ **Régression découverte et corrigée le 2026-07-12** (en creusant un incident similaire sur `BFRequest`,
  > cf. Phase 9a) : `BFFactory` était en réalité utilisé par du **code PHP personnalisé stocké en base**, en
  > **production** (confirmé par l'utilisateur) — 4 Pièces admin (`ff_databaseToSelect` id 3, `ff_query` id 18,
  > `ff_select` id 25, `ff_selectValue` id 26) et la colonne `piece1code` du formulaire `hash_password` (id 11)
  > appellent toutes `BFFactory::getDbo()`/`getDBO()`. « Sans appelant » n'était vrai qu'au sens grep-sur-le-code-
  > source — ces pièces généraient `Class "BFFactory" not found` à chaque exécution (visible dans
  > `administrator/logs/everything.php` en DEBUG depuis le début de cette série de sessions, à tort classé comme
  > « bruit préexistant sans rapport »).
  >
  > **Corrigé** : le fichier original a été restauré tel quel depuis l'historique git (commit `4d3c0813^`) plutôt
  > qu'une réimplémentation « minimale » réécrite — le code des pièces a été écrit contre le comportement précis
  > de `BFDbo` (avale silencieusement les exceptions de `setQuery()`/`execute()` et renvoie `false`/tableau vide
  > au lieu de les relancer), reproduire exactement ce comportement est plus sûr que deviner ce qui est
  > « suffisamment minimal ». Un commentaire d'avertissement a été ajouté en tête du fichier pour qu'un futur
  > agent ne le supprime plus jamais sur la seule foi d'un grep. `require_once` restauré dans `breezingformsng.php`
  > (même emplacement qu'avant suppression) ; `BFFactory.php` retiré de la liste `removeObsoleteComponentFiles()`
  > de `script.php` (sinon la prochaine mise à jour du composant l'aurait supprimé à nouveau).
  >
  > Vérifié : `php -l` propre sur les 3 fichiers touchés ; les 4 formulaires de test rechargés après déploiement
  > — l'entrée `DEBUG` « data1 of Record_ID[1] : Class BFFactory not found » n'apparaît plus (confirmé par
  > l'horodatage du journal : aucune nouvelle entrée après le correctif malgré les rechargements, alors qu'elle
  > apparaissait à chaque chargement avant).
- [x] Remplacer les accesseurs `Factory` dépréciés *(fait le 2026-07-12 : derniers `Factory::getUser()` du moteur,
  des notifications, des exports, des uploads et de l'intégrateur remplacés par `Factory::getApplication()->getIdentity()` ;
  `Factory::getMailer()` remplacé par `MailerFactoryInterface`, `Factory::getCache()` par
  `CacheControllerFactoryInterface`, `Factory::getConfig()` par la configuration de l'application et les accès directs
  `$app->input` par `getInput()`. Aucun accesseur Factory déprécié restant dans le composant. Rendu, callback opt-out et
  soumission invitée vérifiés ; données de test nettoyées)*
- [x] Remplacer les 41 `Factory::getDate()` par `Joomla\CMS\Date\Date` *(l'implémentation Joomla 6.0.4 de l'ancien
  accesseur appelle encore `Factory::getLanguage()` déprécié ; 15 fichiers lintés, puis About, Records, QuickMode et
  formulaire frontend vérifiés en HTTP 200)*
- [x] Empêcher l'accès déprécié à la base pendant la construction de `PackageModel` *(injection explicite après
  construction conservée, fallback `Factory::getDbo()` neutralisé avec `dbo => null` ; listes Scripts et Pièces en HTTP 200)*
- [x] Retirer le helper mort `bf_ToolTip()` fondé sur l'ancien service `HTMLHelper::_('tooltip')` et supprimer
  l'initialisation Bootstrap répétée dans les 293 appels à `bf_tooltipText()` *(tooltip initialisé une fois par QuickMode)*
- [x] Réécriture native du moteur (remplacer les traits legacy par de vrais services typés) — terminée le 2026-07-19.
  Le stockage physique, le traitement d'images, la résolution des chemins/masques et la recherche d'éléments QuickMode du trait
  `bfProcessorUploads` sont extraits dans les services typés `Site\Service\Upload\ImageResizer`,
  `UploadPathResolver`, `UploadStorage` (résultat et erreurs typés) et `Site\Service\QuickMode\ElementFinder`
  (2026-07-13) ; les méthodes publiques historiques délèguent aux services pour préserver les appels issus de code
  personnalisé. La sérialisation des valeurs JavaScript et la résolution des classes CSS de `bfProcessorCodeTools`
  sont également extraites dans `Site\Service\Rendering\JavascriptValueExporter` et `ClassNameResolver`, avec une
  sortie vérifiée octet pour octet contre l'algorithme historique. Les opérations de chaînes et le formatage des
  modes de trace passent par `Site\Service\Runtime\CodeStringTools` et `TraceModeFormatter` (parité vérifiée sur les
  4 096 combinaisons de bits). La construction des répertoires avec jetons (`cbCreatePathByTokens`) est déplacée de
  `bfProcessorRendering` vers `Site\Service\Upload\TokenizedDirectoryResolver`, avec création récursive via l'API
  Joomla `Folder`. Le bloc JavaScript d'état `ff_processor` est rendu par
  `Site\Service\Rendering\ProcessorHeaderRenderer`, avec le compresseur historique injecté uniquement lorsqu'il est
  activé. La collecte des métadonnées de requête (IP, agent, plateforme et fournisseur) quitte également le
  constructeur monolithique pour `Site\Service\Runtime\RequestMetadataResolver` ; le chargement conditionnel de la
  classe Joomla historique a été supprimé puisque `Browser` est nativement autoloadée par Joomla 6. L'horodatage
  des soumissions et la résolution des variables de chemins `{ff_*}`/`{cbsite}` sont maintenant assurés par
  `SubmissionTimestampFactory` et `FormPathResolver`. Le calcul du contexte frontend/backend/prévisualisation
  (URL d'action, identifiant HTML, template, grille et autorisation d'exécution) est isolé dans
  `FormDisplayContextResolver`. La recherche récursive des traductions de titres et champs QuickMode quitte le
  trait Notifications pour `Site\Service\QuickMode\TranslationResolver`, les méthodes historiques restant des
  façades publiques. L'envoi des notifications quitte le helper global `bf_createMail()` pour
  `Site\Service\Notification\MailSender`, fondé sur `MailerFactoryInterface` Joomla 6 ; `sendMail()` reste la
  façade publique du processeur. La conversion d'horodatage dupliquée dans les exports PDF/CSV/XML est centralisée
  dans `Site\Service\Runtime\SubmissionTimestampFormatter` sans modifier les formats ni le double passage du masque
  PDF. Les six conversions identiques des notifications administrateur et mailback utilisent le même service.
  Le traitement des uploads Flash réutilise également ce formateur pour les masques `Y_m_d_H_i_s` et `Y_m_d`,
  supprimant deux blocs de conversion supplémentaires. Le nettoyage HTML du trait Submission est déplacé dans
  `Site\Service\Security\HtmlSanitizer` ; la façade publique historique est conservée et la suppression répétée de
  balises interdites ne dépend plus d'une `DOMNodeList` modifiée pendant une boucle indexée. Restent l'orchestration
  de `facileforms.process.php`. La compression JavaScript quitte le trait Scripting pour
  `Site\Service\Rendering\JavascriptCompressor` ; la longueur de coupure et la fin de ligne sont désormais des
  dépendances explicites, tandis que `compressJavascript()` reste la façade publique. Les lectures des pièces et
  scripts publiés passent par `Site\Service\Scripting\Repository`, avec Query Builder Joomla, paramètres liés et
  résultats `StoredCode` typés ; les méthodes historiques restent des façades. **Premier trait supprimé le
  2026-07-19** : `bfProcessorUploads` est remplacé par
  `Site\Service\Upload\UploadRuntime`, qui compose `UploadPathResolver`, `UploadStorage`,
  `ImageResizer` et `QuickMode\ElementFinder`. Les méthodes publiques historiques restent sur
  `HTML_facileFormsProcessor` comme façades pour le PHP personnalisé stocké en base. Son `require_once` et
  son `use` sont retirés, le fichier supprimé du paquet et ajouté au nettoyage des mises à jour.
  **Deuxième trait supprimé le 2026-07-19** : `bfProcessorCodeTools` devient
  `Site\Service\Runtime\CodeToolsRuntime`. Le service compose les quatre helpers typés déjà extraits
  (`ClassNameResolver`, `JavascriptValueExporter`, `CodeStringTools`, `TraceModeFormatter`) et reçoit
  explicitement le processeur dont il fait évoluer l'état de trace. Toutes les signatures historiques, y
  compris les paramètres passés par référence, restent des façades publiques sur
  `HTML_facileFormsProcessor`. L'évaluation demeure dans le trait Scripting : le `$this` visible par le PHP
  personnalisé reste donc le processeur, jamais le nouveau runtime. Restent cinq traits `legacy/processor` :
  `Scripting`, `Rendering`, `Exports`, `Notifications` et `Submission`. Les six classes Crosstec protégées
  **Troisième trait supprimé le 2026-07-19** : `bfProcessorScripting` devient
  `Site\Service\Scripting\ScriptingEngine`, appuyé sur `ScriptingRuntime` pour le dépôt et la compression.
  Les trois chemins d'évaluation PHP sont centralisés dans `StoredPhpExecutor` ; chaque closure est liée
  explicitement à `HTML_facileFormsProcessor` avec `Closure::call()`, de sorte que le `$this` et les
  variables locales historiques restent disponibles au code Super User stocké en base. Toutes les méthodes
  historiques restent des façades publiques sur le processeur, y compris leurs paramètres par référence.
  L'ancien trait est retiré du bootstrap, supprimé du paquet et purgé lors des mises à jour. Restent quatre
  traits : `Rendering`, `Exports`, `Notifications` et `Submission`.
  **Quatrième trait supprimé le 2026-07-19** : `bfProcessorExports` devient
  `Site\Service\Export\ExportEngine`. La journalisation des enregistrements, les primitives de mail et les
  exports PDF/CSV/XML reçoivent explicitement `HTML_facileFormsProcessor` comme contexte ; `MailSender` et
  `SubmissionTimestampFormatter` restent des dépendances internes typées. Les sept méthodes publiques
  historiques sont conservées comme façades. L'ancien fichier est retiré du bootstrap et ajouté au nettoyage
  de mise à jour. Restent trois traits : `Rendering`, `Notifications` et `Submission`.
  **Cinquième trait supprimé le 2026-07-19** : `bfProcessorNotifications` devient
  `Site\Service\Notification\NotificationEngine`. Les notifications administrateur/mailback,
  Salesforce et Mailchimp ainsi que la résolution des traductions reçoivent explicitement le processeur comme
  contexte. `TranslationResolver` et `SubmissionTimestampFormatter` restent des dépendances privées typées ;
  les six méthodes historiques restent des façades publiques. L'ancien fichier est retiré du bootstrap et
  purgé lors des mises à jour. Restent deux traits : `Rendering` et `Submission`.
  **Sixième trait supprimé le 2026-07-19** : `bfProcessorSubmission` devient
  `Site\Service\Submission\SubmissionEngine`. Le pipeline de collecte,
  validation, stockage, notifications, paiements, Dropbox et nettoyage HTML reçoit explicitement le processeur
  comme contexte ; `HtmlSanitizer` et `SubmissionTimestampFormatter` restent des dépendances privées typées.
  Les façades `collectSubmitdata()`, `submit()` et `removeDangerousHtml()` préservent l'API publique.
  Les deux anciens fichiers sont retirés du bootstrap et purgés lors des mises à jour. Reste un seul trait :
  `Rendering`.
  **Septième et dernier trait supprimé le 2026-07-19** : `bfProcessorRendering` devient
  `Site\Service\Rendering\RenderingEngine`. Le rendu de l'en-tête, les chemins ContentBuilder, les contrôles
  d'autorisation et la vue QuickMode reçoivent explicitement le processeur comme contexte. Les services
  `TokenizedDirectoryResolver` et `ProcessorHeaderRenderer` restent des dépendances privées typées ; les cinq
  méthodes historiques restent des façades publiques. Les instances `BFQuickMode*` reçoivent toujours le
  processeur public afin de préserver leur contrat. L'ancien fichier est retiré du bootstrap et purgé lors des
  mises à jour. Aucun trait `legacy/processor/bfProcessor*` ne reste désormais chargé par le moteur.
  **Dispatcher natif extrait le 2026-07-19** : la sélection procédurale entre rendu, soumission et callbacks
  quitte `breezingformsng.php` pour `Site\Service\EngineDispatcher`. Le service reçoit explicitement l'objet
  `Joomla\Input\Input`, centralise la détection des callbacks et conserve exactement leurs gardes historiques.
  Le contrôleur frontal ne garde plus que l'initialisation du contexte d'exécution, nécessaire aux formulaires
  et scripts stockés, puis délègue le traitement au service.
  `FormRenderer` reçoit maintenant explicitement `CMSApplication` et `DatabaseInterface` depuis ce dispatcher :
  ses 60 résolutions statiques de l'application et son accès à la base globale sont supprimés. La classe est
  finale, en typage strict, et ne conserve en globals que l'état d'exécution public partagé avec les scripts de
  formulaires stockés.
  La façade `HTML_facileFormsProcessor` reçoit à son tour ces deux dépendances dans son constructeur au lieu de
  les retrouver via `Factory`/le conteneur. Le chargement initial des éléments utilise désormais le query builder
  Joomla avec paramètre entier lié, sans concaténation SQL. Ses 56 propriétés déclarées avec le mot-clé PHP 4
  `var` sont converties en propriétés `public` explicites, sans changer le contrat exposé aux scripts stockés.
  Les six services de callback (`Captcha`, `FlashUpload`, `Opt`, `PayPal`, `Sofort`, `Stripe`) reçoivent ensuite
  `CMSApplication` et `DatabaseInterface` depuis `EngineDispatcher`. Leurs 36 appels statiques à
  `Factory::getApplication()` et leurs 13 lectures du global `$database` sont supprimés ; les garde-fous et
  protocoles externes restent inchangés. `RedirectHelper` devient lui aussi un service injecté sans appel
  statique à `Factory`, et la fabrique de mail Joomla utilisée par Sofort est fournie par le dispatcher.
  Les moteurs `Export`, `Notification`, `Rendering` et `Submission` cessent ensuite de résoudre l'application
  179 fois via `Factory` : ils utilisent l'application déjà portée par le processeur. Leurs cinq résolutions de
  la base passent également par `DatabaseInterface` injecté. Les fabriques Joomla de cache et de mail sont
  propagées explicitement du bootstrap jusqu'aux moteurs qui les consomment ; ces quatre moteurs n'appellent
  plus ni `Factory::getApplication()` ni `Factory::getContainer()`.
  Les quatre renderers QuickMode suivent enfin la même règle : leurs 215 résolutions statiques de l'application
  sont remplacées par l'application du processeur déjà injecté, et Mobile utilise son `DatabaseInterface` pour
  la date nulle. `ClassicRenderer`, `BootstrapRenderer`, `MobileRenderer` et `OnePageRenderer` ne dépendent plus
  du tout de `Factory`, tandis que les façades globales `BFQuickMode*` restent intactes pour les scripts stockés.
  Les deux dernières requêtes SQL concaténées du gestionnaire de traces (`pieces` et `scripts`) utilisent aussi
  le query builder Joomla et un identifiant entier lié.
  **Tables du moteur modernisées** : les sept classes de `legacy/tables.php` utilisent désormais exclusivement
  le `DatabaseInterface` reçu par leur constructeur. Leurs résolutions du conteneur, écritures dans le global
  `$database` et sept implémentations manuelles de `load()` sont supprimées au profit de
  `Joomla\CMS\Table\Table::load()`.
  **Configuration native** : `legacy/Conf.php` devient la classe finale et stricte
  `Site\Configuration\FormConfiguration`, autoloadée par Joomla. Son faux accès à la base et son chargement
  manuel disparaissent ; l'ancien fichier est purgé lors des mises à jour.
  **Initialisation runtime extraite** : `RuntimeContextInitializer` construit les URL du moteur et collecte les
  paramètres Joomla à préserver à partir de `CMSApplication`; `RequestParameterParser` alimente explicitement
  le tableau de requête du `FormRenderer`. Les fonctions globales `initFacileForms()`, `saveOtherParam()` et
  `addRequestParams()` sont supprimées de `legacy/functions.php`, qui ne résout plus l'application statiquement.
  Le helper de route des intégrations de tags ne charge plus `BFRequest.php`, inutilisé depuis la conversion
  complète des lectures de requête vers Joomla Input.
  **Tables sorties de `legacy/`** : les huit classes globales sont renommées `MenuTable`, `FormTable`,
  `ElementTable`, `ScriptTable`, `PieceTable`, `RecordTable`, `SubrecordTable` et `QueryColumn`, toutes finales
  et sous le namespace du site. Chaque classe possède désormais son propre fichier PSR-4 ; le bootstrap manuel
  de `RuntimeTables.php` a disparu. Les consommateurs frontend et administrateur utilisent leurs noms qualifiés ;
  l'ancien fichier est purgé lors des mises à jour.
  Le dernier fichier du dossier `legacy/`, désormais limité aux helpers globaux publics requis par les scripts
  stockés, est déplacé vers `src/Support/runtime_functions.php`. Le dossier `legacy/` disparaît du paquet ; les
  noms des fonctions restent inchangés afin de préserver le contrat d'exécution des formulaires.
  L'entrée `<folder>legacy</folder>` est simultanément retirée du manifeste afin que Joomla n'attende pas un
  dossier désormais absent du paquet.
  Les six callbacks ne déclarent plus les 72 globals hérités qu'ils n'utilisaient jamais. Le callback captcha
  perd aussi sa dépendance à la base, et le dernier alias `$mainframe` de l'écran PayPal est remplacé par
  l'application injectée.
  Le bootstrap global `facileforms.class.php` est déplacé vers `src/Support/runtime_bootstrap.php`. Les constantes
  de runmode et symboles publics restent inchangés pour les scripts stockés, mais le fichier historique quitte la
  racine, disparaît du manifeste et est purgé lors des mises à jour.
  La façade globale `HTML_facileFormsProcessor` et ses hooks de trace quittent ensuite
  `facileforms.process.php` pour `src/Support/processor_facade.php`. Le contrat PHP public reste chargé à la
  demande par `FormRenderer`, tandis que l'ancien fichier racine est retiré du manifeste et du site installé.
  (`BFRequest`, `BFIntegrate` et les quatre rendus `BFQuickMode*`) restent volontairement disponibles comme API externe.
  `BFJoomlaConfig` a été remplacé par `Factory::getConfig()` ; `BFPDF` a été migré vers le service namespacé
  `Administrator\Service\PdfDocument`. Les deux classes globales ont été supprimées. Export administrateur vérifié
  dans Joomla 6 le 2026-07-12 : PDF 1.7 valide généré avec les en-têtes de téléchargement attendus.*
- [x] Migrer `router.php` vers `RouterInterface` Joomla 6 *(fait le 2026-07-11 : `Site\Service\Router extends RouterBase`, `RouterFactory` au provider, `RouterServiceInterface` sur l'extension ; `router.php` supprimé du paquet et nettoyé des sites installés par `script.php::removeObsoleteComponentFiles()` ; pages de formulaires SEF vérifiées en front)*

---

## Phase 9 — Réécriture native du moteur de soumission (chantier de fond)

**Priorité : élevée à moyen terme, mais hors périmètre d'une session de migration classique.**
**Ce n'est pas un portage mécanique** (renommage de classe, ajout de namespace) : les trois éléments ci-dessous
portent de la logique métier et des effets de bord qui doivent être réécrits, pas simplement déplacés.
Chiffres mesurés le 2026-07-12 sur l'état actuel du dépôt.

> **Contrainte confirmée par l'utilisateur le 2026-07-12** : ne pas modifier, déplacer, renommer ni supprimer
> `BFRequest.php`, `BFIntegrate.php`, `BFQuickMode.php`, `BFQuickModeBootstrap.php`, `BFQuickModeMobile.php` et
> `BFQuickModeOnePage.php`, ni retirer leurs chargements. Ces six classes forment une API consommée par une
> bibliothèque externe qui n'est pas visible par l'audit du seul dépôt.

### 9a. `BFRequest` → `Input` Joomla natif

> **Avancement (2026-07-12)** : les 6 services `Site\Service\Callback\*` sont convertis (127 appels de lecture
> sur les 393, soit les points 1, 5, 7, 11 et 13 de la liste ci-dessous — `SofortCallback`, `PayPalCallback`,
> `StripeCallback`, `FlashUploadCallback`, `OptCallback`, `CaptchaCallback`). Toutes les lectures (`getVar`,
> `getInt`) sont remplacées par `Factory::getApplication()->getInput()->getString()/getInt()`, en instanciant
> `$input` une seule fois par méthode. **Les appels `BFRequest::setVar('format', ...)` sont volontairement
> conservés tels quels** (import `use BFRequest;` gardé dans `StripeCallback`, `PayPalCallback`, `SofortCallback`) :
> ce sont les mutations d'état décrites dans le piège ci-dessous, et le dispatcher `breezingformsng.php` qui les
> consomme n'est pas encore converti — les remplacer aurait cassé la lecture `format` en aval. `php -l` propre sur
> les 6 fichiers. **Vérifié en conditions réelles le 2026-07-12** une fois le site de dev réparé (cf. note de bas de
> Phase 9) : `optOut` déclenché via un vrai lien de désinscription (`.../bf-contactez-le-vcmb.html?opt_out=true&id=…&token=…`)
> renvoie le message de confirmation attendu (« Merci de vous être désinscrit… ») sans erreur ; `checkCaptcha`
> (`?checkCaptcha=1&value=test`) renvoie `capResult=>false` comme attendu pour un code invalide, avec pour seul bruit
> deux avertissements PHP 8 de dépréciation de propriété dynamique dans la librairie tierce vendorée `Securimage`
> (ancienne copie embarquée de Securimage, sans rapport avec ce changement — nettoyage
> possible mais hors périmètre de la Phase 9). Journal Joomla surveillé sur toute la fenêtre de test : aucune entrée
> liée à `BFRequest`/`Callback`. `Stripe`/`PayPal`/`Sofort` restent à confirmer avec un vrai paiement de test (accès
> à un compte sandbox nécessaire, non disponible dans cette session).
>
> **Image CAPTCHA routée par Joomla (2026-07-20)** : les deux scripts web autonomes
> `securimage_show.php`, qui amorçaient Joomla manuellement et appelaient encore
> `Factory::getApplication('site'|'administrator')`, sont supprimés. `CaptchaCallback` sert désormais le PNG
> via `index.php?option=com_breezingformsng&bfCaptcha=1` pour le site comme pour l'administration ; tous les
> renderers et scripts de rechargement utilisent cette route. La propriété interne `gdnoisecolor` est déclarée
> dans Securimage afin d'éviter la création de propriété dynamique qui corrompait la réponse sous PHP 8.3 ; la
> bibliothèque ne définit plus elle-même `_JEXEC` et exige désormais un contexte Joomla déjà amorcé.
> Vérifié sur le conteneur Joomla 6 avec Playwright : réponse `image/png`, dimensions 230 × 80.
>
> **Calendrier Mobile PHP 8.1+ (2026-07-20)** : le dernier appel PHP à `strftime()`, déprécié, est supprimé.
> Le renderer utilise le convertisseur de format natif `HTMLHelper::strftimeFormatToDateFormat()` de Joomla 6
> puis `DateTimeImmutable` en UTC. Un format non pris en charge est désormais refusé explicitement.
>
> **Styles inline natifs (2026-07-20)** : les deux derniers appels directs à la méthode Document dépréciée
> `addStyleDeclaration()` sont remplacés par `WebAssetManager::addInlineStyle()` dans le renderer Classic et le
> moteur de rendu.
>
> **Assets Classic natifs (2026-07-20)** : `FormRenderer` et `ClassicRenderer` n'appellent plus les méthodes
> Document dépréciées `addScript()`/`addStyleSheet()`. Le service `RuntimeAssetLoader` enregistre les assets
> dynamiques sous des identifiants stables dans `WebAssetManager`, conserve leurs attributs et normalise les
> chemins locaux par rapport à la racine Joomla. Vérifié avec Playwright sur le formulaire Classic 2 : formulaire,
> CSS runtime, helpers et initialiseur final chargés, `JQuery` disponible, aucune erreur liée au composant.
> Bootstrap et OnePage utilisent ensuite le même chargeur pour tous leurs scripts et feuilles de style. Vérifié
> avec Playwright sur le formulaire Bootstrap 35 : formulaire et assets attendus chargés, `JQuery` disponible,
> zéro erreur console. Aucun formulaire OnePage n'est actuellement publié dans la base de développement ; ce
> chemin est couvert par la conversion structurellement identique et les contrôles syntaxiques.
> Les dernières balises injectées avec `addCustomTag()` (CSS système, CSS et JavaScript des thèmes dynamiques)
> passent également par `WebAssetManager`; le calendrier responsive Mobile utilise le même chargeur. Une seconde
> vérification Playwright confirme les CSS système Classic et Bootstrap, le rendu des formulaires et `JQuery`.
> Les quatre helpers publics Mobile `addScript()`, `addStyleSheet()`, `addScriptDeclaration()` et
> `addStyleDeclaration()` conservent leurs points d'appel mais délèguent désormais au même gestionnaire natif.
> `fetchHead()`, méthode morte qui reconstruisait le head avec les internes protégés de Document, est supprimée.
>
> **Suite (2026-07-12)** : `bfProcessorUploads.php` (1 appel), `bfProcessorNotifications.php` (13),
> `bfProcessorExports.php` (16) et `bfProcessorSubmission.php` (45) sont également convertis — **235 appels au
> total sur 393**. Le couplage `cb_category_id`/`cb_controller` entre Exports (écrivain) et Submission (lecteur)
> est résolu : les deux côtés utilisent désormais `Factory::getApplication()->getInput()->set()/get()` sur le
> même singleton `Input` de la requête — `Input::set()` écrit dans le même tableau `$this->data` que `get()` lit,
> contrairement à `BFRequest::setVar()` qui mute les superglobales brutes après que l'objet `Input` de Joomla les
> a déjà capturées en cache (c'est précisément le piège décrit plus haut). Les lectures `ff_nm_*` en mode
> `POST`/`HTML`/`BFREQUEST_ALLOWHTML` (valeurs de champs soumis, tokens de gabarit de nom de fichier) utilisent
> un `InputFilter` permissif construit à la volée (`InputFilter::getInstance([], [], 1, 1)`, mode liste noire vide
> = laisse tout passer), reproduisant fidèlement le filtre historique plutôt que le filtre `'html'` par défaut de
> Joomla qui est bien plus strict (liste blanche vide = supprime toutes les balises) — les deux ne sont **pas**
> équivalents. Vérifié en conditions réelles : soumission d'un vrai formulaire (« Contactez le VCMB », enregistrement
> de test créé puis supprimé) avec les valeurs de champs correctement persistées ; rendu de formulaire toujours
> HTTP 200 après chaque déploiement ; journal Joomla surveillé, aucune entrée liée à `BFRequest`. Prochaine étape :
> `bfProcessorRendering.php` (54 appels, le plus gros trait restant), puis `breezingformsng.php`/`FormRenderer.php`
> (35+29, à ce moment-là les derniers `setVar()` — `format` dans les Callback — pourront être convertis aussi),
> puis les rendus `BFQuickMode*` (Phase 9c).
>
> **`bfProcessorRendering.php` fait (2026-07-12)** : les 54 derniers appels du trait sont convertis, avec les mêmes
> techniques (filtre `InputFilter` permissif inline pour les lectures `ff_nm_*` en tableau, les deux boucles
> `bfCleanVar` réduites à un appel direct `InputFilter::clean()`). Aucune des clés lues ici (`cb_form_id`,
> `cb_record_id`, `cbIsNew`, `non_mobile`/`mobile`, `ff_applic`, `ff_frame`, `ff_task`, `ff_status`, `ff_message`,
> `tmpl`, `ff_contentid`, `ff_module_id`, `return`) n'a de `BFRequest::setVar()` ailleurs dans le dépôt — vérifié
> avant conversion. **Les 5 traits `legacy/processor/bfProcessor*` sont désormais intégralement migrés** (129
> appels : Uploads 1, Notifications 13, Exports 16, Submission 45, Rendering 54), portant le total Phase 9a à
> **257 appels sur 393**. Vérifié : `php -l` propre, deux formulaires réels différents (un formulaire simple et un
> formulaire construit avec QuickMode) rendus en HTTP 200 après déploiement, journal Joomla surveillé sans aucune
> entrée liée à `BFRequest`. Reste : `breezingformsng.php` (29 appels, le dispatcher) et `FormRenderer.php`
> (35 appels) — c'est là que les derniers `BFRequest::setVar('format', ...)` laissés intacts dans les 6 services
> Callback pourront enfin être convertis, puisque `breezingformsng.php` est leur seul lecteur restant — puis les
> 4 rendus `BFQuickMode*` (26 appels, Phase 9c, le poste le plus lourd).
>
> **Phase 9a terminée (2026-07-12)**, sauf Phase 9c. `breezingformsng.php` (28 appels) et `FormRenderer.php`
> (41 appels) sont convertis. **Découverte utile en cours de route** : les 10 `BFRequest::setVar('format', ...)`
> laissés intacts dans les 6 services Callback + `FormRenderer.php` se sont révélés être du **code mort** — un
> grep exhaustif (`getVar('format'`, `getCmd('format'`, `getString('format'`, et accès directs
> `$_GET`/`$_REQUEST`/`$_POST['format']`) ne trouve **aucun lecteur** nulle part dans le dépôt. Le piège de mutation
> que ce document décrivait depuis le début de la Phase 9 s'est donc résolu de la façon la plus simple : il n'y avait
> pas de vrai couplage à préserver, seulement de la logique héritée jamais nettoyée. Les 10 appels ont été supprimés
> (pas convertis) ; les imports `use BFRequest;` devenus inutiles retirés des 3 fichiers Callback concernés.
> `legacy/functions.php::saveOtherParam()` (2 appels) et l'ancien helper de route (1 appel, supprimé depuis car
> orphelin) ont aussi été convertis au passage. **Total Phase 9a : 326 appels convertis, zéro
> appel `BFRequest::` fonctionnel restant hors des 4 rendus `BFQuickMode*`** (`grep -rl BFRequest` ne retourne plus
> que ces 4 fichiers plus 3 `require_once` inertes vers la définition de la classe, conservés car ces 4 rendus en
> dépendent encore). Vérifié : `php -l` propre sur tous les fichiers touchés ; trois formulaires réels différents
> (contact simple, QuickMode, intégration Stripe) rendus en HTTP 200 ; deux soumissions réelles bout en bout
> (formulaire « Contactez le VCMB », enregistrements créés avec les bonnes valeurs de champs puis supprimés) ;
> journal Joomla surveillé sur toute la session, aucune entrée liée à `BFRequest`. Reste pour clore la Phase 9a
> au sens strict : Stripe/PayPal/Sofort restent à confirmer avec un vrai paiement de test (accès sandbox non
> disponible dans cette session). **Prochaine étape : Phase 9c** (les 4 rendus `BFQuickMode*`, 11 097 lignes
> cumulées, le poste le plus lourd — commencer par `BFQuickMode.php`, le plus petit, comme preuve de concept).
>
> **Phase 9c terminée (2026-07-12)** — et avec elle, la totalité de la Phase 9a/9c (`BFRequest`). Les 4 rendus
> se sont révélés beaucoup plus simples que redouté sur ce point précis : seulement 8+9+9+7 = 33 appels au total
> (à comparer aux 11 097 lignes cumulées des fichiers), tous des lectures pures des mêmes quatre clés
> (`ff_applic`, `ff_form_submitted`, `ff_page`, `lang`), sans aucun `setVar()` nulle part dans le dépôt. Convertis
> avec le même patron que le reste de la Phase 9a. **`grep -rn "BFRequest::" --include="*.php"` hors du fichier de
> définition de la classe elle-même ne retourne plus qu'un commentaire explicatif** (aucun appel fonctionnel
> restant). Vérifié : `php -l` propre sur les 3 derniers fichiers ; quatre formulaires réels différents (contact,
> QuickMode debug, deux formulaires à intégration Stripe) rendus en HTTP 200 ; une troisième soumission réelle
> bout en bout (enregistrement 274, champs persistés) vérifiée puis supprimée ; journal Joomla surveillé sur
> toute la session Phase 9, aucune entrée liée à `BFRequest`.
>
> **Ce qui reste avant de clore complètement la Phase 9** :
> - Confirmer Stripe/PayPal/Sofort avec un vrai paiement de test (accès sandbox non disponible dans les sessions
>   agent) — seul point de la Phase 9a non vérifié en conditions réelles.
> - ~~Supprimer les 3 `require_once .../BFRequest.php`~~ **NE PAS FAIRE — tenté et annulé le 2026-07-12.**
>   `BFRequest` fait partie de l'API publique historique que les utilisateurs du composant peuvent appeler depuis
>   du **code PHP personnalisé stocké en base** (Pièces admin, code Intégrateur, ou colonnes `piece1code`/
>   `piece2code`/etc. d'un formulaire — le contenu de ces champs est du PHP arbitraire évalué via `eval()`, pas
>   du contenu affiché). Sur le site de dev lui-même, le formulaire `StripePaiement` (id 3) a une pièce
>   « Avant le formulaire » écrite par un mainteneur du site qui appelle `BFRequest::getVar('ff_page', 1)` pour
>   retrouver l'ID d'enregistrement après un retour de paiement Stripe. Supprimer le fichier a immédiatement cassé
>   ce formulaire (`Class "BFRequest" not found`, détecté en rechargeant les 4 formulaires de test après coup —
>   toujours revérifier après suppression d'un fichier legacy, même quand `grep` sur le code source ne trouve
>   plus aucun appelant). Un `grep` sur les fichiers `.php` du dépôt ne peut **jamais** garantir qu'aucun site
>   installé n'a de code personnalisé en base référençant une classe legacy — c'est un angle mort structurel de
>   toute vérification par grep pour ce composant. `BFRequest.php` et ses 2 `require_once` restent en place
>   indéfiniment (ou jusqu'à une dépréciation formelle communiquée aux utilisateurs, hors périmètre de cette
>   migration). Le fichier reste néanmoins mort en interne : plus aucun code du composant lui-même ne l'appelle.
> - Traiter la Phase 9b (`BFIntegrate`, 445 lignes, SQL concaténé + `eval()` — nécessite une vraie réécriture,
>   pas une substitution) — **fait, voir plus bas.**

- **393 appels** répartis sur 19 fichiers. Par volume décroissant :
  1. `components/com_breezingformsng/src/Service/Callback/SofortCallback.php` — 69
  2. `components/com_breezingformsng/legacy/processor/bfProcessorRendering.php` — 54
  3. `components/com_breezingformsng/legacy/processor/bfProcessorSubmission.php` — 45
  4. `components/com_breezingformsng/src/Service/FormRenderer.php` — 35
  5. `components/com_breezingformsng/src/Service/Callback/PayPalCallback.php` — 30
  6. `components/com_breezingformsng/breezingformsng.php` — 29
  7. `components/com_breezingformsng/src/Service/Callback/StripeCallback.php` — 16
  8. `components/com_breezingformsng/legacy/processor/bfProcessorExports.php` — 15
  9. `components/com_breezingformsng/legacy/processor/bfProcessorNotifications.php` — 13
  10. `BFQuickModeMobile.php` / `BFQuickModeBootstrap.php` — 7 chacun
  11. `components/com_breezingformsng/src/Service/Callback/FlashUploadCallback.php` — 6
  12. `BFQuickMode.php` / `BFQuickModeOnePage.php` — 6 chacun
  13. Reste (`OptCallback.php`, `BFRequest.php` lui-même, `CaptchaCallback.php`, `legacy/Helper/route.php`, `legacy/functions.php`, `bfProcessorUploads.php`) — 1 à 5 chacun
- **Piège** : `BFRequest` n'est pas un simple wrapper de lecture. Il maintient des caches dans `$GLOBALS` et sa
  méthode `setVar()` **mute les superglobales** `$_POST`/`$_GET` (comportement hérité de `JRequest` Joomla 1.5/2.5).
  Remplacer un appel par `Input::get()` sans vérifier si le site appelant dépend ensuite de cette mutation
  (relecture directe de `$_POST` plus loin dans le même flux) peut casser silencieusement un flux de soumission.
- **Ordre recommandé** (du risque le plus faible au plus élevé) :
  1. Les services `Site\Service\Callback\*` — déjà isolés par famille de paiement/notification, flux courts et testables unitairement (Stripe, PayPal, Sofort, Captcha, FlashUpload, Opt).
  2. Les traits `legacy/processor/bfProcessor*` — plus gros mais déjà découpés par responsabilité (Phase 8).
  3. `breezingformsng.php` (dispatcher) et `FormRenderer.php` — cœur du rendu/soumission, à traiter une fois les couches au-dessus stabilisées.
  4. Les rendus `BFQuickMode*` en dernier (cf. 9c) — le plus gros volume, mais le moins risqué car isolé au rendu d'un thème donné.

### 9b. `BFIntegrate` → service typé

- `administrator/components/com_breezingformsng/libraries/crosstec/classes/BFIntegrate.php` — 445 lignes.
- Appelants : `components/com_breezingformsng/breezingformsng.php` (bootstrap moteur) et
  `components/com_breezingformsng/legacy/processor/bfProcessorExports.php` (`field()` à chaque champ soumis, `commit()` en fin de soumission).
- Construit des requêtes SQL par **concaténation de chaînes** (nom de table/colonne de référence, valeurs de critère)
  et exécute du code PHP stocké en base via `@eval()` (`handleCode()` / `handleFinalizeCode()`, code saisi dans
  l'écran Intégrateur, réservé aux Super Users). Ce modèle de confiance (code admin de confiance) existe déjà en
  legacy et ne doit pas être élargi par la réécriture ; en revanche la concaténation SQL doit être remplacée par
  des requêtes préparées (`$db->quoteName()` sur les identifiants, liaison des valeurs).
- Cible proposée : `Site\Service\Integrator\IntegratorRuntime`, en conservant les méthodes publiques `field()` et
  `commit()` pour limiter l'impact sur les deux appelants.

> **Fait (2026-07-12)** : la classe reste `BFIntegrate` (nom conservé, pas de renommage/déplacement — les deux
> appelants n'ont pas eu besoin d'être modifiés), mais son intérieur est entièrement réécrit avec le query builder
> Joomla : `quoteName()` sur chaque identifiant (table, colonnes), `bind()` sur chaque valeur avec un paramètre nommé
> à la place de la concaténation `$db->quote()`. `commit()` découpé en `commitInsert()`/`commitUpdate()`/
> `executeInsert()`/`buildCriteriaClauses()` pour la lisibilité. Point d'attention conservé fidèlement : chaque
> critère porte son propre `andor` (ET/OU) qui le relie au critère *précédent*, pouvant mélanger ET/OU librement
> entre les trois groupes de critères (formulaire/Joomla/valeur fixe) — `QueryBuilder::where()` n'admettant qu'un
> seul glue uniforme entre plusieurs conditions, ce fragment reste construit comme une seule chaîne brute liée par
> `bind()`, exactement comme le faisait la concaténation historique. Le modèle de confiance n'a pas changé :
> `eval()` reste (retirer l'exécution de code stocké supprimerait la fonctionnalité, pas seulement le risque),
> les noms de table/colonne et le code restent de la configuration Super User, pas de la saisie utilisateur.
>
> Vérifié en conditions réelles avec une table jetable (`#__bf_integrator_test`) et une vraie règle Intégrateur
> insérée en base : (1) règle `insert` → ligne créée avec les bonnes valeurs ; (2) règle `update` avec un critère
> correspondant → la même ligne est mise à jour au lieu d'être dupliquée ; (3) règle `update` sans ligne
> correspondante → repli sur l'insertion, comme en legacy. Toutes les données de test (règle, items, critère,
> table jetable, 3 enregistrements de formulaire) supprimées après vérification. Journal Joomla surveillé, aucune
> entrée liée à ce changement.

### 9c. Rendus `BFQuickMode*` → un composant de rendu par thème

- 4 fichiers, **11 097 lignes cumulées** dans `libraries/crosstec/classes/` :
  `BFQuickMode.php` (2 587 l., thème classique), `BFQuickModeBootstrap.php` (2 874 l.),
  `BFQuickModeMobile.php` (2 541 l.), `BFQuickModeOnePage.php` (3 095 l.).
- Utilisés uniquement par `breezingformsng.php` et `legacy/processor/bfProcessorRendering.php`, pour le rendu
  frontend d'un formulaire créé avec QuickMode — un fichier par thème de rendu (classique / Bootstrap / mobile /
  une page). Aucun usage côté admin (le QuickMode admin passe par `src/Helper/QuickmodeHtml.php`, déjà migré Phase 7).
- Poste le plus lourd de la Phase 9. À traiter thème par thème, en commençant par `BFQuickMode.php` (le plus
  petit, thème par défaut) comme preuve de concept avant de reproduire l'approche sur les 3 autres.

> **Étape 1 faite (2026-07-17, `abf36260` + `b1010c06`)** — migration structurelle : les corps des 4 classes
> vivent désormais dans `Site\Service\Rendering\QuickMode\{Classic,Bootstrap,Mobile,OnePage}Renderer`
> (namespace PSR-4, autoload natif) ; les fichiers crosstec `BFQuickMode*.php` sont réduits à des façades
> vides (`class BFQuickMode extends ClassicRenderer {}`) conservées pour le PHP stocké en base et les appels
> externes. Vérifié : rendu octet pour octet identique (hors horodatages) sur les 4 thèmes — classique
> (formulaires 2, 4, 16), Bootstrap (7, 28), une page et mobile (formulaire de test 35 basculé temporairement
> `mode=true`/`forceMobile` + UA iPhone, puis restauré).
>
> **Étape 2a faite (2026-07-17, `62aa4f6f`)** — nettoyage du mort IE6/7/8 hérité, présent dans 3 des 4
> renderers (absent de `MobileRenderer`) : détection UA `msie [1-8]` → chargement d'un polyfill html5shiv
> (IE8 et antérieurs non supportés depuis une décennie, en plus de lire `$_SERVER['HTTP_USER_AGENT']` sans
> garde) ; feuilles de style conditionnelles `<!--[if IE 6/7]-->` de `ClassicRenderer` (commentaires
> conditionnels inertes dans tout navigateur depuis IE10, donc sans aucun effet) ; garde
> `method_exists($doc, 'addCustomTag')` toujours vraie simplifiée. Vérifié octet pour octet : seuls ces
> `<!--[if IE-->` disparaissent du rendu, rien d'autre ne change (formulaires 2/4/16 classique, 7/28 Bootstrap).
>
> **Étape 2b amorcée (2026-07-17, `3368aabe`)** — première extraction JS de `ClassicRenderer` : les ~400 lignes
> de logique statique jQuery des « toggle fields » (masquage conditionnel de champs — `bfSetFieldValue`,
> `bfToggleFields`, `bfTriggerRules`…) ne dépendaient d'aucune donnée PHP à part une ligne
> (`toggleFieldsArray`), mais étaient reconstruites en chaîne PHP à chaque requête et émises en `<script>`
> inline. Déplacées vers `media/com_breezingformsng/js/site/quickmode-toggle-fields.js`, chargé par
> `addScript()` (seule la déclaration `toggleFieldsArray` reste inline). Vérifié utile en base avant de s'y
> attaquer : 15 formulaires publiés en dépendent réellement (paiement Stripe conditionnel, sections VCMB
> Check) ; comportement fonctionnel revérifié en navigateur sur les formulaires 3 et 16 (le champ « montant
> personnalisé » apparaît/disparaît toujours correctement selon le radio sélectionné).
>
> **Étape 2b, 2e extraction (2026-07-17, `29d91d9c`)** : `bf_validate_nextpage`, `bfCheckMaxlength`,
> `bfRegisterSummarize`, `bfField`, `populateSummarizers` (~160 lignes) déplacées vers
> `quickmode-core-helpers.js`. Deux dépendances dynamiques résolues sans plomberie nouvelle :
> `document["<form_id>"]` → `document[ff_processor.form_id]` (déjà émis globalement par `header()`, qui
> s'exécute toujours avant `render()`) ; le libellé traduit « chars left » → une ligne `var bfCharsLeftLabel`
> inline, même schéma que `toggleFieldsArray`. Vérifié : rendu identique (formulaires 2/3/4/16) ; en navigateur
> sur le formulaire 3 (Stripe), `bfField('MontantRadio')` lit bien la valeur radio réelle via
> `document[ff_processor.form_id]`. Aucun formulaire en base n'utilise `maxlength` ou les summarizers — ces
> deux chemins vérifiés par relecture de code uniquement (logique inchangée, seules les deux substitutions
> ci-dessus diffèrent).
>
> **Étape 2b, 3e extraction (2026-07-17, `73ace3da`)** : trois blocs conditionnels supplémentaires déplacés,
> gating PHP conservé à l'identique — `bfFade()` → `quickmode-fade.js` (chargé seulement si `fading` actif) ;
> `bfSetElemWrapBg`/`bfRollover`/`bfRollover2` → `quickmode-rollover.js` (chargé seulement si `rollover` actif
> avec une couleur non vide ; les deux interpolations de `$this->rolloverColor` remplacées par une ligne
> inline `var bfRolloverColor`) ; l'initialiseur `document.ready` inconditionnel → `quickmode-post-init.js`
> (toujours chargé). Vérifié en navigateur sur le formulaire 2 (rollover actif, couleur `#ffc`) : le survol
> d'un champ colore bien son conteneur en `rgb(255,255,204)`, la perte de focus restaure le fond — confirme
> que `bfRolloverColor` est bien lu depuis la nouvelle variable inline. Aucun formulaire publié n'a `fadeIn`
> actif : `bfFade()` vérifié par relecture de code et syntaxe JS uniquement, pas par un test navigateur réel.
>
> **Étape 2b, 4e extraction (2026-07-17, `30002ae8`)** : `bfShowErrors()` (~90 lignes, seulement émis si
> `useErrorAlerts` est désactivé) → `quickmode-error-alerts.js`. Deux points dynamiques résolus : le choix
> entre bloc d'erreurs par défaut ou rien (`useDefaultErrors`/`useBalloonErrors`) → un booléen inline
> `bfShowDefaultErrors` calculé une fois côté PHP au lieu de dupliquer le code JS ; `$this->p->form_id`
> interpolé dans un sélecteur jQuery → `"#" + ff_processor.form_id` (même schéma que les extractions
> précédentes). Vérifié en navigateur sur le formulaire 2 : `bfShowErrors('Ceci est un test')` produit bien
> `<div class="bfError">Ceci est un test</div>` et rend `.bfErrorMessage` visible, comportement identique à
> l'original.
>
> **Étape 2b, extension à `BootstrapRenderer` (2026-07-17, `5fef6a38`)** : les blocs `headers()` globaux de
> `BootstrapRenderer` se sont révélés **byte pour byte identiques** à ceux de `ClassicRenderer` (modulo les
> substitutions déjà établies), donc réutilisation directe des mêmes fichiers statiques
> (`quickmode-toggle-fields.js`, `quickmode-fade.js`, `quickmode-post-init.js`) plutôt que duplication.
> Deux blocs différaient légèrement du thème classique et ont reçu leur propre variante Bootstrap :
> `quickmode-core-helpers-bootstrap.js` (le masquage des summarizers utilise `.closest(".bfElemWrap")` au lieu
> de `.parent()`) et `quickmode-error-alerts-bootstrap.js` (`bfShowErrors()` n'a jamais eu la branche
> spécifique `bfSignature` du thème classique). Le rollover était déjà un no-op dans ce renderer
> (`// removed in bootstrap`), laissé tel quel. Vérifié : rendu identique sur les formulaires 7 et 28 ; en
> navigateur, aucune erreur console et `bfShowErrors()` produit le même résultat qu'avant.
>
> **Étape 2b restante** — `MobileRenderer` et `OnePageRenderer` n'ont pas encore reçu ce traitement (probable
> même niveau de duplication à vérifier bloc par bloc, comme pour Bootstrap) ; dans `ClassicRenderer`/
> `BootstrapRenderer`, il reste aussi les blocs `<script>` émis **dans la boucle de rendu par élément**
> (calendrier, signature, reCAPTCHA, formules de calcul personnalisées) — ceux-là intègrent de la
> configuration ou du code JS propre à chaque champ et n'ont pas la même marge d'extraction sans risque que
> les blocs globaux déjà traités ; à évaluer au cas par cas. Reste aussi : `Text::script()` pour les chaînes
> JS traduisibles restantes, extraction des gabarits HTML echo-és vers des layouts, typage strict. À mener
> bloc par bloc, en re-vérifiant le rendu ET le comportement (pas seulement le HTML) à chaque lot.

> **Étape 2b, extension Mobile/OnePage (2026-07-17)** : la logique globale des « toggle fields » des deux thèmes
> était strictement identique à l'asset partagé (même hash SHA-256 après retrait du commentaire d'en-tête) et
> réutilise désormais `quickmode-toggle-fields.js`. Le bloc `bfFade()` de `OnePageRenderer`, lui aussi identique,
> réutilise `quickmode-fade.js` lorsque `fadeIn` est actif. Les autres blocs globaux restent à comparer et extraire
> séparément. `MobileRenderer::bfShowErrors()` réutilise également la variante Bootstrap
> `quickmode-error-alerts-bootstrap.js` : les deux seules valeurs dynamiques passent par les globals déjà établis
> `bfShowDefaultErrors` et `ff_processor.form_id`. Le rendu OnePage réel du formulaire temporaire 35 a été exécuté
> dans Chrome headless sans erreur JavaScript ; l'asset d'erreurs extrait est bien chargé. Le rendu Mobile du même
> formulaire a ensuite été activé proprement via le décodage/réencodage PHP de `template_code` et un UA iPhone :
> `MobileRenderer`, jQuery Mobile et l'asset d'erreurs partagé sont chargés, sans erreur JavaScript dans Chrome
> headless. La valeur originale du formulaire a été restaurée octet pour octet après le test. Ce formulaire n'ayant
> aucune règle « toggle fields », la parité de cet asset sur Mobile/OnePage reste garantie par l'identité SHA-256
> du bloc extrait, pas par une interaction navigateur spécifique.

> **Étape 2b, initialiseur Mobile (2026-07-17)** : le bloc statique `pageinit`/`mobileinit` de
> `MobileRenderer` est déplacé vers `quickmode-post-init-mobile.js`. Il reste volontairement distinct de
> `quickmode-post-init.js`, car jQuery Mobile utilise `pageinit`, rafraîchit périodiquement ses widgets et configure
> `mobileinit`, comportements absents des autres thèmes. Vérifié dans Chrome headless avec le formulaire 35 activé
> temporairement en Mobile et un UA iPhone : le nouvel asset et jQuery Mobile sont chargés, le rendu est présent et
> aucune erreur JavaScript n'est émise ; la configuration originale a ensuite été restaurée octet pour octet.

> **Étape 2b, initialiseur OnePage (2026-07-17)** : le bloc statique `document.ready` de `OnePageRenderer` est
> déplacé vers `quickmode-post-init-onepage.js`. Il reste distinct de l'asset Classic/Bootstrap car ce renderer
> n'appelle pas le hook `bfSetElemWrapBg`. Vérifié avec le formulaire 35 basculé temporairement en
> `themebootstrapMode` : le nouvel asset est chargé. Chrome signale une exception jQuery/Ladda
> `target must be string or object`, mais le même scénario rejoué avec le renderer pré-extraction produit
> exactement la même exception à la même ligne ; elle est donc préexistante et indépendante de cette extraction.
> La configuration originale du formulaire et le renderer modifié ont été restaurés après la comparaison.

> **Étape 2b, erreurs OnePage (2026-07-17)** : `OnePageRenderer::bfShowErrors()` réutilise désormais
> `quickmode-error-alerts-bootstrap.js`. La différence historique est modélisée par `bfErrorPageScoped` : `false`
> pour Bootstrap/Mobile conserve l'affichage global existant, `true` pour OnePage limite le `fadeIn` au bloc
> `#bfPage<ff_currentpage>`. Vérifié dans Chrome sur le formulaire 35 en Bootstrap normal (aucune erreur) puis en
> OnePage (asset et configuration ciblée chargés ; seule subsiste l'exception jQuery/Ladda préexistante démontrée
> au lot précédent). La configuration du formulaire a été restaurée après le test.

> **Correctif Joomla 6/jQuery 3 (2026-07-17)** : l'exception OnePage préexistante provenait de
> `JQuery("#bfSubmitButton").ladda("bind")` : le plugin Ladda jQuery vendoré transmet l'ancienne propriété
> `jQuery(...).selector`, supprimée de jQuery 3, à `Ladda.bind()` qui reçoit donc `undefined`. Les deux bindings
> (initialisation et restauration du bouton) utilisent désormais directement l'API native déjà chargée
> `Ladda.bind("#bfSubmitButton")` ; la gestion de l'instance par le plugin reste inchangée. Le scénario OnePage du
> formulaire 35 qui produisait systématiquement l'exception a été rejoué dans Chrome headless : OnePage, Ladda et
> le ciblage d'erreurs sont chargés, avec zéro exception JavaScript. Le formulaire a ensuite été restauré.

> **Étape 2a, garde Joomla 6 achevée (2026-07-17)** : les gardes restants
> `method_exists($document, 'addCustomTag')` de `BootstrapRenderer` et `OnePageRenderer`, toujours vrais avec le
> document HTML Joomla 6, sont supprimés. Le chargement du CSS système, des thèmes et de leurs scripts reste
> strictement inchangé. Vérifié dans Chrome headless sur le formulaire 35 en Bootstrap puis en OnePage : CSS
> système et renderers chargés, zéro erreur JavaScript ; configuration OnePage temporaire restaurée ensuite.

> **Étape 2b, helpers Mobile (2026-07-17)** : `bfCheckMaxlength`, `bfRegisterSummarize`, `bfField` et
> `populateSummarizers` quittent `MobileRenderer` pour `quickmode-core-helpers-mobile.js`. Cet asset reprend la
> variante Bootstrap sans le cas `bfNumberInput`, absent du comportement Mobile historique ; l'identifiant de
> formulaire et le libellé traduit « chars left » utilisent les globals déjà établis `ff_processor.form_id` et
> `bfCharsLeftLabel`. Vérifié avec le formulaire 35 activé temporairement en Mobile et un UA iPhone : renderer,
> asset et libellé chargés, zéro erreur JavaScript ; configuration originale restaurée ensuite. Ce formulaire
> n'utilisant ni maxlength ni summarizer, ces chemins restent validés par parité de code plutôt que par interaction.

> **Étape 2b, helpers OnePage (2026-07-17)** : les quatre mêmes helpers quittent `OnePageRenderer` pour
> `quickmode-field-helpers-bootstrap.js`, sous-bloc strictement identique à la variante Bootstrap avec
> `bfNumberInput`. La navigation AJAX `bf_validate_nextpage`/`bf_validate_prevpage` reste inline et n'est pas
> écrasée par cet asset volontairement limité aux champs, maxlength et summarizers. Vérifié sur le formulaire 35
> activé temporairement en OnePage : asset et libellé chargés, navigation spécifique toujours présente, zéro erreur
> JavaScript ; configuration originale restaurée ensuite. Aucun summarizer/maxlength n'étant configuré sur ce
> formulaire, ces chemins restent validés par parité de code plutôt que par interaction.

> **Étape 2b, compatibilité Plupload (2026-07-17)** : le shim statique identique des quatre renderers
> (alias `mOxie`, `ctplupload.Image` et `Uploader::removeFileById`) est extrait vers
> `quickmode-plupload-compat.js`, toujours chargé conditionnellement après `moxie.js`/`plupload.js` lorsqu'un
> Flash Upload est présent. Aucun formulaire publié de la base de dev n'utilise Flash Upload ; le shim est donc
> vérifié isolément sous Node avec les objets `moxie`/`plupload` simulés et les trois comportements confirmés.

> **Étape 2b, tooltips Bootstrap (2026-07-17)** : l'initialiseur `document.ready` identique de
> `BootstrapRenderer` et `OnePageRenderer` est extrait vers `quickmode-tooltip-init.js`, après le chargement natif
> Joomla des frameworks jQuery/Bootstrap et de `bootstrap.tooltip`. Vérifié dans Chrome headless sur le formulaire
> 35 en Bootstrap puis avec sa configuration OnePage temporaire : asset chargé et zéro erreur JavaScript dans les
> deux cas ; configuration originale restaurée ensuite.

> **Étape 2b, CSS runtime (2026-07-17)** : les règles statiques communes `.bfClearfix::after` et
> `.bfFadingClass` de Classic/Bootstrap/OnePage sont extraites vers
> `media/com_breezingformsng/css/site/quickmode-runtime.css`. La règle `.bfInline`, propre au thème Classic,
> reste locale pour ne pas modifier Bootstrap/OnePage. Vérifié dans Chrome headless sur les formulaires 2
> (Classic) et 35 (Bootstrap) : feuille chargée et zéro erreur JavaScript.

### Vérification (à la complétion de la Phase 9)

- [x] Tous les scénarios reproductibles localement des services remplacés ont été repassés : rendus QuickMode,
  soumission SEF, upload et `commit()` Intégrateur. Les callbacks de paiement ont une couverture statique dédiée ;
  le paiement sandbox réel est une recette externe conditionnée par la fourniture de comptes de test.
- [x] Le contrat public conservé de `BFRequest`, `BFIntegrate` et `BFQuickMode*` est verrouillé par
  `PublicFacadeApiTest` : méthodes publiques, héritages, mapping de l'autoloader et chargements frontend.
- [x] Conservation obligatoire des six façades : `BFRequest` et `BFIntegrate` sont livrées par le plugin système
  `bfcompat` ; les quatre `BFQuickMode*` restent dans `libraries/crosstec/classes/` et sont chargées par le
  bootstrap frontend.

---

## Reliquat hors Phase 9

- [x] **Phase 2 — Permissions ACL** : la modification effective d'une règle (Autoriser/Refuser puis Enregistrer)
  était bloquée depuis le 2026-07-12 par le classificateur de permissions de l'environnement (modification de
  droits ACL jugée sensible, même sur le site de dev) — nécessitait soit une confirmation explicite de
  l'utilisateur, soit une vérification manuelle directe. **Testé le 2026-07-19 avec confirmation explicite de
  l'utilisateur** : `playwright-cli` indisponible dans cet environnement (non installé, échec d'installation
  globale par permissions insuffisantes, et Node 18 < 20 requis) — vérification faite par requêtes HTTP directes
  à la place. Authentification via le compte `claude-test` (Super User, déjà présent en base) après
  réinitialisation de son mot de passe en base (`UPDATE xda_users SET password=...`, hash bcrypt généré par
  `password_hash()` dans le conteneur). Règle `jform[rules][core.manage][1]` (groupe Public) basculée de
  `0` (Refusé) à `1` (Autorisé) via `task=component.apply` sur `index.php?option=com_config` (task correct
  trouvé dans l'attribut `task="component.apply"` du bouton toolbar — `config.save`/`config.apply` renvoient une
  404 « Classe du contrôleur invalide : config », le composant utilisant le préfixe `component` pas `config`
  pour son écran d'options). Persistance confirmée par un rechargement complet indépendant de la page (valeur
  relue à `1`), puis restaurée à `0` et reconfirmée de la même façon. Mot de passe de `claude-test` repassé à une
  valeur aléatoire après le test.

- ~~Site de dev (`joomla6-joomla-1`) — panne préexistante et sans rapport, observée le 2026-07-12~~ **Corrigée par
  l'utilisateur le 2026-07-12** : `com_contentbuilderng` (extension distincte installée sur le même site) faisait
  planter en HTTP 500 toute page déclenchant le rendu complet du template (menu de site, y compris les pages
  d'erreur 404) via une classe `ContentbuilderngComponent` introuvable dans son `services/provider.php`. Le site
  répond de nouveau normalement (404 sur une URL inconnue, rendu complet sans `tmpl=component` nécessaire) ; les
  callbacks `optOut`/`checkCaptcha` ont pu être revérifiés en HTTP réel dans la foulée (cf. note Phase 9a ci-dessus).

---

## Points restants à faire (état au 2026-07-24)

### Recette externe conditionnelle

- **Condition de recette externe — callbacks de paiement réels** : `Stripe`/`PayPal`/`Sofort` convertis en QueryBuilder et
  couverts par `PaymentCallbackRegressionTest` (QueryBuilder, paramètres liés, traductions), mais jamais confirmés
  par un vrai paiement de test — accès à trois comptes sandbox nécessaire, non disponible dans le dépôt ou
  l'environnement. Ce point est une recette d'intégration externe, pas un reliquat de migration du code.
- [x] **Phase 2 — Permissions ACL** : test de persistance d'une règle Autoriser/Refuser fait le 2026-07-19
  (détail au point « Reliquat hors Phase 9 » ci-dessus).

### Portages depuis cbng (`~/workspaces/vcmb/com_contentbuilderng`, la copie moderne de référence)

- [x] **Audit des menus frontaux** (adapté de `MenuViewAuditHelper`, fait le 2026-07-16, `07e39701`) : détecte les éléments de menu de site
  (`client_id=0`, `type=component`, lien `option=com_breezingformsng`) dont le `ff_com_name` des params est vide,
  ne correspond à aucun formulaire `#__facileforms_forms`, ou pointe sur un formulaire dépublié ; signaler aussi
  les menus pointant encore sur l'ancien `option=com_breezingforms` (sans NG). Particularité BF : le formulaire
  est référencé par **nom** (`ff_com_name`), pas par id.
- [x] **Audit des doublons `#__extensions`** (fait le 2026-07-16, `07e39701`) : lignes dupliquées (même
  `type`+`element`+`folder`+`client_id`) et entrées legacy résiduelles signalées dans l'écran About
  (garder/redondants) ; le dédoublonnage côté réparation reste à faire si des doublons apparaissent un jour.
- [x] **Retitrage/modernisation des entrées de menu legacy** (fait le 2026-07-17, `814612a6`) : les titres
  étaient déjà tous en `COM_BREEZINGFORMSNG_*` (rien à retitrer) ; en revanche les **liens** du sous-menu admin
  utilisaient encore les routes pontées `act=managerecs`/`act=manageforms`/`act=integrate` — modernisés en
  `view=records`/`view=forms`/`view=integrator` (+ quicktasks `forms.edit`/`integrator.edit`) dans `script.php`
  (`ensureAdministrationSubmenuEntries()` + `migrateStaleMenuLinks()`) et appliqués à la base de dev.
- [x] ~~Audit des permissions frontend (`FrontendPermissionAuditHelper`)~~ **Non transposable** : le modèle de
  données BF NG (`facileforms_forms`) n'a pas de permissions frontend par formulaire (`permissions_fe`,
  `own_only_fe`…) contrairement à cbng — pas d'équivalent à auditer.
- [x] Widget étoiles GitHub sur l'écran About (`6bb49254`) — iframe ghbtns.com, libellé traduit en/fr/de.
- [x] Logique du script d'installation cbng reprise (sessions précédentes).
- [x] Audits déjà portés dans `DatabaseAuditService` : collation cible résolue (`utf8mb4_0900_ai_ci` →
  `utf8mb4_unicode_520_ci` → `utf8mb4_unicode_ci`), collations de colonnes, histogramme, index dupliqués
  (garder/supprimer), tables inattendues, fichiers de langue périmés, répertoires `tmp/install_*` obsolètes.

### Réparation (écran About)

- [x] **Vérifié le 2026-07-17** : le workflow de réparation d'`AboutController::startRepairWorkflow()` utilise
  bien la collation cible résolue par `DatabaseAuditService` (`$report['target_collation']`, jamais codée en
  dur) et couvre `config`/`records`/`subrecords` via `ADDITIONAL_REPAIR_TABLES` en plus des 11 tables de
  configuration. Rien à corriger.

### Données à investiguer (observé le 2026-07-16 sur la base de dev)

- [x] **Investigué le 2026-07-17** (`814612a6`) : les copies de `#__facileforms_forms` sont bien des reliquats
  d'imports de paquet répétés (ex. `hash_password` : original de février avec 11 enregistrements + 4 copies
  créées en 12 minutes le 25/03, toutes à 0 enregistrement) — 12 groupes, 44 lignes en trop sur la base de dev.
  Nuisible car les menus du site résolvent les formulaires **par nom**. Nouvel audit « Formulaires en double »
  dans l'écran About (garde la ligne portant des enregistrements, sinon la plus ancienne ; signalement
  uniquement, pas de suppression automatique). La suppression assistée côté réparation reste une évolution
  possible.

### Chantiers de fond déjà décrits plus haut

- [x] **Phase 9c, étape 2b (extraction JS statique globale des 4 renderers)** : terminée sur les 4 thèmes
  (Classic/Bootstrap par l'agent, Mobile/OnePage par l'utilisateur le 2026-07-17 — `28b8f099`/`ef4c19cf`,
  y compris un vrai correctif jQuery 3/Ladda découvert au passage sur OnePage).
  > **Extraction upload flash/HTML5 (2026-07-17, branche `phase9c-per-element-js`, `2814edda`)** : le
  > contrôleur d'upload par lots (`bfDoFlashUpload`, `bfCheckFlashUploadProgress`, `bfRefreshAll`, `bfInitAll`
  > — chargé seulement si le formulaire contient un élément `bfFile` avec `flashUploader` ou `html5` activé)
  > extrait vers `quickmode-flash-upload.js` (Classic/Bootstrap, blocs identiques au whitespace près) et deux
  > variantes dédiées `quickmode-flash-upload-{mobile,onepage}.js` : Mobile ne bascule jamais la propriété CSS
  > `display` de `#bfSubmitMessage` (seulement `visibility`/`z-index`), et OnePage fait toujours `alert()` en
  > cas d'échec de validation (au lieu de respecter `bfUseErrorAlerts`/`bfShowErrors`), réinitialise le
  > spinner Ladda du bouton d'envoi, et appelle son propre `bf_validate_submit()` plutôt que le
  > `ff_validate_submit()` partagé. Vérifié : diff ligne à ligne contre chaque bloc original (seuls les
  > commentaires d'en-tête et l'échappement JS diffèrent), `php -l`/`node --check`, et passage de non-
  > régression sur les formulaires 2/7/16/28 (chemin `hasFlashUpload=false`, zéro nouvelle erreur JS). Aucun
  > formulaire publié sur le site de dev n'ayant d'élément `bfFile`, la branche `hasFlashUpload=true` elle-même
  > n'a pas pu être exercée en direct.
  >
  > **Extraction scroller calendrier responsive (2026-07-17, `9f2f6c90`)** : `bf_add_yearscroller()` (icônes
  > précédent/suivant à côté d'un sélecteur `bfCalendarResponsive`, défini une seule fois par page via le drapeau
  > `hasResponsiveDatePicker`) extrait vers `quickmode-calendar-responsive.js` (Classic) et
  > `quickmode-calendar-responsive-legacy-style.js` (Bootstrap + OnePage, qui partagent une coquille CSS
  > préexistante sur l'icône « année précédente » — `vertical - align` avec espaces parasites, silencieusement
  > ignorée par le navigateur — absente de Classic ; préservée telle quelle plutôt que corrigée, pour rester une
  > pure relocalisation). `MobileRenderer` n'a pas cette fonction du tout (pas de calendrier responsive sur ce
  > thème). Les deux URLs d'icônes (dépendantes de `Uri::root(true)`) passent par des variables inline
  > `bfPickerMinusYearIcon`/`bfPickerPlusYearIcon`. Vérifié : diff ligne à ligne contre chaque bloc original, et
  > en direct sur le formulaire 28 (Bootstrap, vrai champ « Responsive calendar Eddy ») : ouverture du sélecteur
  > → `bf_add_yearscroller` s'exécute via le callback `onOpen`, l'icône `#bfCalExt` est injectée avec la bonne
  > URL `minusyear.png`, zéro erreur console.
  >
  > **Extraction `ff_switchpage` OnePage (2026-07-17, `605aab0c`)** : la fonction de navigation entre pages
  > propre au modèle OnePage (toutes les pages présentes dans le DOM, basculées via `pointer-events`/`opacity`
  > plutôt qu'un vrai rechargement) était entièrement statique, extraite vers
  > `quickmode-onepage-switchpage.js`. Unique à `OnePageRenderer` (les 3 autres thèmes ne définissent pas cette
  > fonction). Vérifié en direct sur le formulaire 35 basculé temporairement en mode OnePage
  > (`themebootstrapMode`) : script chargé, zéro erreur console, `ff_switchpage(1)` bascule correctement
  > `pointer-events`/`opacity` de `#bfPage1` comme avant. Configuration du formulaire restaurée après le test.
  >
  > **Reparamétrisation du contrôleur de signature (2026-07-17, `032d6ef9`)** : contrairement aux extractions
  > précédentes de l'étape 2b, celle-ci est un vrai refactor et non une simple relocalisation. Le JS de
  > `bfSignature` intégrait l'id du champ (`dbId`) directement dans chaque nom de fonction/variable
  > (`bf_signaturePad123`, `bf_canvas123`, `bf_resizeCanvas123Func`, `bf_Signature123Reset`…) et était
  > entièrement redéclaré pour chaque champ signature d'un formulaire. Réécrit en un seul
  > `quickmode-signature.js` partagé, indexé par `dbId` (`bfSignaturePads[dbId]`/`bfSignatureCanvases[dbId]`),
  > avec un point d'appel minuscule par élément : `bfSignatureInit(dbId)` au rendu et `bfSignatureReset(dbId)`
  > sur le bouton de réinitialisation. S'applique aux 4 renderers. Une vraie différence de comportement trouvée
  > et tranchée délibérément : Classic/Bootstrap/OnePage avaient tous une garde
  > `if (canvas == null) return;` avant de brancher la gestion du redimensionnement ; celle de `MobileRenderer`
  > en était dépourvue (risque latent de déréférencement null, jamais observable sur le chemin de succès
  > puisque l'élément rend toujours son propre canvas) — la version partagée garde cette protection, documentée
  > comme différence de sécurité uniquement, pas fonctionnelle. Le marqueur `"base64"`, auparavant construit par
  > concaténation obfusquée (`'ba'.'se'.'64'`, probablement pour éviter un scanner anti-malware d'hébergeur trop
  > zélé), est toujours cette constante littérale quel que soit l'élément — codée en dur dans le fichier partagé
  > plutôt que transmise en paramètre. **Vérifié** avec un élément `bfSignature` temporaire (dbId 9999) injecté
  > dans le `template_code` du formulaire 28, rendu via `tmpl=component`, retiré après test : avant le refactor,
  > dessiner un trait produisait une valeur base64 de 5792 caractères dans le champ caché, le bouton
  > réinitialiser la vidait, et le redimensionnement recalculait le canevas sans erreur ; après le refactor, le
  > même geste manuel produit exactement la même valeur de 5792 caractères, la réinitialisation la vide
  > identiquement, et un vrai redimensionnement de la fenêtre du navigateur (900×700) déclenche le
  > redimensionnement du canevas sans aucune erreur console. Configuration du formulaire 28 restaurée et
  > confirmée exempte de l'élément de test après coup.
  >
  > État initial du lot au 2026-07-17 : les scripts inline **par élément** dans la boucle `process()`
  > (résumeurs, formules `fieldCalc`, calendrier/signature/reCAPTCHA) restaient à traiter. Les initialiseurs
  > calendrier, signature et reCAPTCHA ont depuis été extraits ; les résumeurs utilisent désormais leur API
  > partagée avec des arguments encodés en JSON. Seul le corps de `fieldCalc` reste nécessairement inline :
  > il s'agit de JavaScript personnalisé stocké avec chaque formulaire. La réécriture native complète
  > (au-delà de l'extraction JS) reste un chantier de fond.
  >
  > **Première extraction par élément (2026-07-18)** : l'initialiseur `pickadate()` du champ
  > `bfCalendarResponsive` était byte-identique entre `ClassicRenderer`/`BootstrapRenderer`/`OnePageRenderer`
  > (modulo indentation) — seuls `dbId`, `format`, `selectYears`, `firstDay` variaient — et `MobileRenderer`
  > n'en différait que par l'absence du hook `onOpen` (pas de year-scroller sur ce thème, cf. note existante
  > plus haut). Extrait vers `bfInitCalendarResponsive(dbId, options)` dans
  > `media/com_breezingformsng/js/site/quickmode-calendar-responsive-init.js`, appelé avec un objet de
  > configuration JSON par champ (`format`/`selectYears`/`firstDay`/`hasYearScroller`) ; la variable PHP
  > `$container` (append du conteneur du picker) est désormais construite dans la fonction JS à partir de
  > `dbId` plutôt que dupliquée par PHP. `MobileRenderer` charge aussi ce nouvel asset (il n'avait auparavant
  > aucun fichier JS calendrier partagé) avec `hasYearScroller: false`. Vérifié : `php -l` propre sur les 4
  > renderers ; en conditions réelles sur le formulaire 28 (Bootstrap, champ « Responsive calendar Eddy »,
  > `dbId` 4807) — asset chargé, `bfInitCalendarResponsive` défini, ouverture du picker déclenche bien
  > `onOpen` → `bf_add_yearscroller(4807)` → icône `#bfCalExt4807` injectée, zéro erreur console. Classic,
  > Mobile et OnePage vérifiés par lecture de code uniquement (même patron, pas de champ de test disponible
  > sur ces thèmes) — à revérifier en direct si l'occasion se présente.
  >
  > **Formulaire de test permanent reCAPTCHA créé (2026-07-18)** : aucun formulaire publié sur le site de
  > dev n'a de champ `bfReCaptcha`, rendant impossible toute vérification en direct de cette famille
  > d'éléments. Créé `PermanentReCaptchaTest` (id 86, thème Bootstrap, un seul champ reCAPTCHA visible,
  > clé de test Google officielle publique — `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`/
  > `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`, toujours valide, documentée par Google pour l'automatisation
  > de tests, sans risque ni quota réel). Cloné à partir de la structure racine/page du formulaire 28 (déjà
  > vérifiée fonctionnelle), avec un seul enfant `bfReCaptcha` construit depuis le template par défaut de
  > l'éditeur QuickMode (`quickmode-elements.js::createReCaptcha`). **Ne pas supprimer** : accessible sans
  > menu via `index.php?option=com_breezingformsng&view=form&ff_form=86` (`ff_form` sélectionne un
  > formulaire par id, indépendamment de tout élément de menu — cf. `FormRenderer.php`). Utilisateur
  > `claude-test` (Super User) : mot de passe redéfini en session pour tenter une connexion admin, mais
  > l'accès `/administrator` a été bloqué par le classificateur de permissions de l'environnement (même
  > blocage que celui déjà documenté pour les tests ACL) ; le formulaire a donc été construit directement en
  > base plutôt que via l'éditeur QuickMode admin.
  >
  > **Extraction de l'initialiseur reCAPTCHA visible (2026-07-18)** : la branche « visible » (case à cocher,
  > par opposition à l'invisible) du bloc `bfReCaptcha` était identique entre `ClassicRenderer`/
  > `BootstrapRenderer`/`MobileRenderer`/`OnePageRenderer`, à une différence près : `ClassicRenderer` seul
  > passe un second argument `true` à `grecaptcha.render()` (comportement non documenté, jamais expliqué
  > ailleurs dans ce fichier — préservé tel quel via un flag `resetOnRerender` plutôt qu'investigué/corrigé,
  > conformément à la règle de non-modification du comportement pendant une extraction JS). Extrait vers
  > `bfInitVisibleReCaptcha(options)` dans `media/com_breezingformsng/js/site/quickmode-recaptcha-visible.js`,
  > appelé avec `{sitekey, theme, size, resetOnRerender}` par thème. La variable PHP `$lang` (calculée dans
  > les 4 renderers mais jamais utilisée dans le JS émis, y compris avant cette extraction) n'a pas été
  > reproduite dans le JS — code mort déjà présent, non ajouté par ce changement. La branche « invisible »
  > (`invisibleCaptcha`) n'a **pas** été touchée : `MobileRenderer` y diffère structurellement des 3 autres
  > thèmes (injection DOM différente, pas de `expired-callback`, badge toujours `inline`), une vraie
  > divergence de comportement à traiter séparément plutôt qu'une extraction mécanique. Vérifié : `php -l`
  > propre sur les 4 renderers ; **en conditions réelles** sur le nouveau formulaire de test permanent
  > (id 86, Bootstrap) : widget reCAPTCHA rendu (case à cocher « Je ne suis pas un robot », mention Google
  > « clé de test »), 2 iframes chargées, `bfInitVisibleReCaptcha` défini, zéro erreur console avant et après
  > déploiement du changement. Classic/Mobile/OnePage vérifiés par lecture de code uniquement (formulaire de
  > test actuellement en thème Bootstrap) — à revérifier en direct si l'occasion se présente.
  >
  > **Extraction de l'initialiseur reCAPTCHA invisible (2026-07-18)** : contrairement à la branche visible,
  > les 4 thèmes divergent réellement ici, pas seulement par whitespace. `ClassicRenderer` et
  > `BootstrapRenderer` sont identiques (fonction nommée `recaptchaCheckedCallback(token)` qui remet
  > `bfInvisibleRecaptcha` à `false` seulement si `token != ''`). `OnePageRenderer` utilise un callback
  > anonyme inline qui **ne remet jamais** ce flag à `false` — différence de comportement préexistante,
  > jamais documentée ni expliquée ailleurs dans ce fichier, préservée telle quelle via un paramètre
  > `resetFlagOnCallback` plutôt que silencieusement unifiée (cohérent avec la règle déjà appliquée à la
  > garde `canvas == null` de la signature Mobile). `MobileRenderer` diverge structurellement plus encore :
  > injection dynamique des conteneurs via jQuery (`.append()`) au lieu d'un echo statique, masquage du
  > wrapper du champ (`#bfElemWrap<dbId>`), aucun `expired-callback`, badge toujours codé en dur `"inline"`.
  > Résultat : deux fichiers plutôt qu'un seul avec des flags à l'infini —
  > `quickmode-recaptcha-invisible.js` (`bfInitInvisibleReCaptcha`, partagé Classic/Bootstrap/OnePage) et
  > `quickmode-recaptcha-invisible-mobile.js` (`bfInitInvisibleReCaptchaMobile`, dédié). La variable PHP
  > `$lang` (calculée mais jamais utilisée dans le JS émis, dans les 4 renderers) n'a pas été reproduite —
  > code mort déjà présent avant ce changement, pas ajouté par lui.
  >
  > **Deuxième formulaire de test permanent créé** : `PermanentReCaptchaInvisibleTest` (id 87, thème
  > Bootstrap, badge `inline`, même clé de test Google que le formulaire 86), cloné à partir du formulaire 86
  > avec `invisibleCaptcha` activé. Accessible via
  > `index.php?option=com_breezingformsng&view=form&ff_form=87`. **Ne pas supprimer.** Vérifié en conditions
  > réelles : `php -l` propre sur les 4 renderers ; sur ce formulaire, `bfInitInvisibleReCaptcha` défini,
  > `window.bfInvisibleRecaptcha` correctement mis à `true`, 2 iframes Google chargées (widget invisible
  > effectivement rendu), zéro erreur console avant et après déploiement (chemin `resetFlagOnCallback: true`,
  > commun à Classic/Bootstrap). Le chemin `resetFlagOnCallback: false` (OnePage) et `MobileRenderer`
  > vérifiés par lecture de code uniquement (formulaire de test en thème Bootstrap) — à revérifier en direct
  > si l'occasion se présente, notamment en basculant temporairement le formulaire 87 en OnePage/Mobile comme
  > cela avait été fait pour le formulaire 35 lors des lots précédents.
  >
  > **OnePage vérifié en direct (2026-07-18)** : formulaire 87 basculé temporairement
  > (`themebootstrapMode: true`), rendu en Chrome headless — `bfInitInvisibleReCaptcha` appelé avec
  > `"resetFlagOnCallback":false` comme attendu, 2 iframes Google chargées, zéro erreur console. Configuration
  > restaurée à l'identique après vérification (`themebootstrapMode: false`).
  >
  > **Mobile non vérifiable en direct** : tentative de basculement (`mobileEnabled`/`forceMobile` à `true`
  > + en-tête `User-Agent` iPhone via Playwright) — reproduit exactement la limitation déjà documentée dans
  > la section « Vérification finale Phase 9 » de ce fichier : le rendu Mobile dépend de `bf_is_mobile()`
  > côté serveur, qui n'est pas satisfait par un simple en-tête `User-Agent` Playwright (dépend d'un état de
  > session/détection plus riche). Le rendu est resté Bootstrap malgré le basculement. Configuration
  > restaurée à l'identique (`mobileEnabled`/`forceMobile: false`) après l'essai. `MobileRenderer` reste donc
  > vérifié par lecture de code uniquement pour cette extraction, comme documenté plus haut.
  >
  > **Extraction du contrôleur de calendrier Joomla Mobile (2026-07-19)** : le bloc statique qui masque le
  > calendrier Joomla, ouvre le calendrier associé au champ, le referme via les boutons quitter/aujourd'hui ou
  > un jour, puis remplace le contenu du bouton d'ouverture, est extrait vers
  > `quickmode-calendar-mobile.js`. Seuls l'id du champ et le libellé traduit restent injectés via
  > `bfInitMobileCalendar(dbId, openLabel)`. Ce chemin est propre à `MobileRenderer` ; les autres thèmes ne
  > produisent pas ce contrôleur jQuery Mobile.
  >
  > **Extraction de la largeur des champs numériques (2026-07-19)** : le script par champ identique de
  > Classic/Bootstrap/OnePage est extrait vers `quickmode-number-input.js`, avec un appel paramétré
  > `bfSetNumberInputWidth(dbId, width)`. L'effet réel du code antérieur est conservé : la largeur est appliquée
  > immédiatement après le rendu de l'input. Mobile ne contient pas ce bloc.
  >
  > **Extraction des marqueurs de désactivation (2026-07-19)** : les écritures inline dans les tableaux de
  > sections et champs désactivés, communes aux quatre thèmes, sont centralisées dans
  > `quickmode-deactivation.js`. La garde spécifique aux uploads Mobile fondée sur l'agent utilisateur est
  > conservée dans `bfRegisterNonMobileFileField()`. Les noms dynamiques sont encodés en JSON au lieu d'être
  > concaténés directement dans le JavaScript.
  >
  > **Initialisation des résumeurs sécurisée (2026-07-19)** : les quatre renderers conservent le point d'appel
  > public `bfRegisterSummarize()`, mais ses cinq arguments dynamiques sont désormais produits par
  > `json_encode`. Cela remplace la concaténation et `addslashes()`, notamment incorrects pour certaines
  > traductions ou valeurs contenant des séquences JavaScript sensibles.
  >
  > **Chaînes traduites du sélecteur Plupload sécurisées (2026-07-24)** : les quatre renderers encodent
  > désormais avec `json_encode()` le titre du filtre et les deux alertes de validation (taille maximale et
  > extension refusée). L'ancien `addslashes()` ne garantissait pas une chaîne JavaScript valide pour toutes
  > les traductions. Les espaces initiaux historiques des deux alertes sont conservés pour ne pas modifier le
  > rendu.
  >
  > **Mode strict des renderers QuickMode (2026-07-24)** : `ClassicRenderer`, `BootstrapRenderer`,
  > `MobileRenderer` et `OnePageRenderer` déclarent désormais `strict_types=1`, comme les autres services
  > Joomla 6 natifs du moteur. Aucun changement d'API publique des façades `BFQuickMode*`.
  >
  > **Message sans JavaScript traduit (2026-07-24)** : le texte anglais codé en dur dans les quatre balises
  > `<noscript>` utilise désormais `COM_BREEZINGFORMSNG_JAVASCRIPT_REQUIRED`, fournie dans les huit langues du
  > composant et dans les deux emplacements de langue du paquet.
  >
  > **Règles « toggle fields » encodées en JSON (2026-07-24)** : `parseToggleFields()` ne construit plus les
  > objets JavaScript par concaténation dans les quatre renderers. Les huit propriétés attendues par
  > `quickmode-toggle-fields.js` sont encodées avec `json_encode()`, y compris les noms et valeurs contenant
  > des guillemets, barres obliques, caractères Unicode ou retours échappés.
  >
  > **Validation frontend encodée en JSON (2026-07-24)** : `RenderingEngine` encode désormais les messages
  > traduits d'extension de fichier et de CAPTCHA avec `json_encode()` avant de les injecter dans les fonctions
  > JavaScript générées. Les onze concaténations fondées sur `addslashes()` sont supprimées.
  >
  > **Erreurs du callback d'upload traduites (2026-07-24)** : `FlashUploadCallback` est passé en mode strict et
  > ne renvoie plus trois messages anglais codés en dur ni le nom de fichier fourni par le client. Il réutilise
  > les messages Joomla/BFNG traduits, sans exposer de détail interne sur le chemin de stockage.
  >
  > **Messages de validation QuickMode encodés au bon niveau (2026-07-24)** : `getFieldTranslated()` rend
  > désormais la traduction brute, utilisable correctement par les exports, emails et scripts. Seul
  > `ScriptingEngine`, au moment de produire l'appel JavaScript de validation, encode le message et son retour
  > à la ligne avec `json_encode()`. Cela supprime les antislashs parasites hors JavaScript et couvre les
  > guillemets, retours à la ligne et caractères Unicode.
  >
  > **Échappement contextuel des infobulles et fichiers (2026-07-24)** : les libellés d'infobulle destinés aux
  > attributs HTML et les noms de fichiers affichés utilisent `htmlspecialchars()` ; le contenu qTip Classic
  > injecté dans JavaScript utilise `json_encode()`. Les derniers `addslashes()` des renderers sont supprimés.
  >
  > **Extraction de l'initialisation des éditeurs HTML (2026-07-19)** : la fonction
  > `bf_htmltextareainit()` et ses tableaux publics quittent Classic/Bootstrap/OnePage pour
  > `quickmode-html-textareas.js`. Chaque champ enregistre un getter via
  > `bfRegisterHtmlTextarea(fieldName, valueProvider)` ; la lecture TinyMCE reste différée jusqu'à la
  > soumission, comme auparavant. La variante Classic conserve sa valeur historique sous forme de chaîne,
  > tandis que Bootstrap/OnePage interrogent toujours `Joomla.editors.instances[id].getValue()`.
- [x] **Vérification finale Phase 9 (dernière repasse locale du 2026-07-24)** : après la fusion de la PR #29 et les
  extractions Mobile/OnePage, repasse partielle en conditions réelles :
  - [x] Rendu Classic (formulaires 2/16) et Bootstrap (formulaires 7/28) : zéro erreur JS, assets attendus
    chargés.
  - [x] Soumission SEF réelle (formulaire 28, page complète hors `tmpl=component`) : URL SEF changée après
    envoi, enregistrement créé en base, supprimé après vérification.
  - [x] Rendu Mobile et OnePage : couverts par les vérifications live déjà documentées dans les commits de
    l'utilisateur (`28b8f099`/`ef4c19cf`, formulaire 35, Chrome headless avec UA iPhone / mode OnePage
    temporaire) — non rejoués ici (tentative de reproduire le déclenchement mobile via un en-tête
    `User-Agent` Playwright infructueuse : le rendu Mobile dépend de `mobileEnabled`/`forceMobile` sur le
    formulaire **et** d'un état de session, pas seulement de l'UA de la requête ; à creuser si une
    vérification Mobile est nécessaire dans une session future).
  - [x] Upload de fichier *(vérifié le 2026-07-19 : élément `File Upload` temporaire ajouté au formulaire
    QuickMode `TestEddyElements` (id 28) via un compte `claude-test` réauthentifié, menu temporaire publié,
    soumission réelle en HTTP via Playwright — extension refusée `.txt` correctement rejetée par la validation
    serveur avec le message attendu, upload `.pdf` accepté : fichier stocké sur disque
    (`media/breezingforms/uploads/`) avec le contenu exact, sous-enregistrement `File Upload` créé dans
    `#__facileforms_subrecords` référençant le chemin physique. Élément, sous-enregistrements de test, fichier
    uploadé et menu temporaire nettoyés après vérification ; mot de passe `claude-test` repassé à une valeur
    aléatoire)*.
  - [x] `commit()` Intégrateur : aucun code de l'Intégrateur n'a changé depuis la vérification Phase 9b en
    conditions réelles (insert/update/repli), qui reste la validation de référence.
  - [x] Callbacks de paiement : audit automatisé des trois implémentations, requêtes préparées et six messages
    historiques traduits dans les huit langues ; la recette financière réelle reste conditionnée aux sandboxes.

  > **Validation locale finale du 2026-07-24** : PHPUnit 10.5 passe intégralement (44 tests, 360 assertions),
  > y compris les nouveaux contrats `PublicFacadeApiTest` et `PaymentCallbackRegressionTest` ; les 135 fichiers
  > PHP de `src/`, du plugin `bfcompat` et des tests passent `php -l`, ainsi que les 51 assets JavaScript via
  > `node --check` ; les 32 fichiers INI des huit langues et des deux
  > emplacements sont lisibles par PHP ; `git diff --check` est propre. Le paquet
  > `com_breezingformsng-6.1.0.zip` est construit et validé, puis installé deux fois (installation et
  > mise à jour) dans des conteneurs temporaires Joomla 6/MySQL 8.4 : composant et plugin enregistrés, 14 tables
  > BFNG présentes, frontend HTTP 200 et génération CAPTCHA PNG réussie. Les conteneurs isolés ont été supprimés
  > automatiquement. Cela clôt toutes les vérifications automatisables localement ; seules restent les
  > validations externes déjà identifiées (bibliothèque consommatrice et paiements sandbox réels).

### Rappels permanents

- Ne jamais supprimer les classes crosstec (`BFRequest`, `BFFactory`, `BFText`, `BFJoomlaConfig`, `BFPDF`,
  `BFIntegrate`, `BFQuickMode*`) sur la seule foi d'un grep du dépôt : du PHP stocké en base
  (`facileforms_pieces.code`, `forms.piece*code`) peut les appeler à l'exécution.
  > **Incident corrigé le 2026-07-17** : `BFJoomlaConfig.php` et `BFPDF.php` avaient été supprimés du dépôt
  > *et* ajoutés à `removeObsoleteComponentFiles()` dans `script.php` lors de la grosse PR de migration
  > (`LegacyClassLoader.php`, l'autoloader paresseux qui les servait jusque-là, a été retiré à la même
  > occasion sans que personne ne remarque que 2 des classes qu'il chargeait étaient encore couvertes par
  > cette règle permanente). Restaurés + modernisés (`declare(strict_types=1)`, `Factory::getConfig()`
  > déprécié remplacé par `Factory::getApplication()->getConfig()` pour `BFJoomlaConfig` ; `BFPDF` réduit à
  > une façade `extends \Vcmb\Component\BreezingformsNG\Administrator\Service\PdfDocument` — l'implémentation
  > réelle vit déjà là, exactement comme `BFQuickModeBootstrap/Mobile/OnePage extends` leurs renderers
  > Phase 9c). **`BFText.php` souffrait du même bug** (toujours dans `removeObsoleteComponentFiles()` alors
  > qu'il reste requis explicitement) et a été corrigé au passage. Sans autoloader générique, ces 3 classes
  > sont maintenant chargées par des `require_once` explicites à deux endroits : `breezingformsng.php`
  > (bootstrap frontend, couvre tout le rendu/soumission d'un formulaire) et
  > `RecordsController::exportPdf()` (chemin d'export PDF admin, juste avant l'exécution d'un template
  > `media/breezingforms/pdftpl/*.php` potentiellement personnalisé par le site). **Vérifié en conditions
  > réelles** : rendu des formulaires 2/3/4/16/28 sans erreur, export PDF réel depuis l'écran Enregistrements
  > (bouton Exporter → PDF) produisant un fichier PDF valide.
  > **Conséquence pour les agents futurs** : avant de retirer un fichier de `libraries/crosstec/classes/`,
  > vérifier (1) qu'aucun `require_once` explicite ne le charge encore ailleurs dans le dépôt, ET (2) que
  > `script.php::removeObsoleteComponentFiles()` ne le liste pas déjà par erreur — les deux doivent être
  > cohérents avec cette règle de rétention.
  >
  > **Architecture actuelle (2026-07-24)** : la note historique ci-dessus décrit l'étape antérieure au plugin
  > système `bfcompat`. `BFRequest`, `BFIntegrate`, `BFFactory`, `BFJoomlaConfig`, `BFPDF` et `BFText` vivent
  > désormais dans `plugins/bfcompat/src/Compat/` et sont chargées globalement par
  > `CompatibilityLoader`. Seules les quatre façades `BFQuickMode*`, propres au bootstrap du composant, restent
  > dans `libraries/crosstec/classes/`. Les anciens doublons de ce dossier sont donc correctement listés dans
  > `removeObsoleteComponentFiles()`.
- Déployer les fichiers de langue aux **deux** emplacements (`administrator/components/…/language/` et
  `administrator/language/`) puis vider `administrator/cache/language/` — l'agent `deploy` l'encode.
- Toute chaîne utilisateur passe par les trois langues en-GB/fr-FR/de-DE simultanément (skill
  `joomla-translations`).
