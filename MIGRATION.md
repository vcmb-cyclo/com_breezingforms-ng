# Migration com_breezingformsng → Joomla 6 pur

> Document de suivi destiné aux agents. Cocher chaque tâche à la complétion.  
> Branche de travail recommandée : `migration-j6` (déjà active).

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
- [x] PDF : TCPDF maintenu en `6.11.3` tant que PHP 8.1 reste supporté ; `tc-lib-pdf 8.x` exige PHP 8.2 minimum
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
- [ ] Permissions ACL visibles et fonctionnelles *(interface native et groupes vérifiés ; modification effective d'une règle restant à tester)*

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
- [x] Import de paquets — `ImportModel` réécrit (SimpleXML, requêtes paramétrées, transaction) ; consommé par `script.php::importStandardLibrary()`. Limité aux bibliothèques de scripts/pièces (seul cas réel : `packages/stdlib.english.xml`) ; les paquets avec formulaires/menus sont refusés avec un message clair. Vérifié de bout en bout le 2026-07-10 : installation du paquet 6.1.0-RC3 dans le conteneur `joomla6-joomla-1`, import stdlib OK (71 scripts inchangés ignorés, 3 pièces mises à jour, métadonnées paquet actualisées).

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
- [x] Empêcher l'accès déprécié à la base pendant la construction de `LegacyPackageModel` *(injection explicite après
  construction conservée, fallback `Factory::getDbo()` neutralisé avec `dbo => null` ; listes Scripts et Pièces en HTTP 200)*
- [x] Retirer le helper mort `bf_ToolTip()` fondé sur l'ancien service `HTMLHelper::_('tooltip')` et supprimer
  l'initialisation Bootstrap répétée dans les 293 appels à `bf_tooltipText()` *(tooltip initialisé une fois par QuickMode)*
- [ ] Réécriture native du moteur (remplacer les traits legacy par de vrais services typés) — chantier de fond restant.
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
  résultats `StoredCode` typés ; les méthodes historiques restent des façades. Restent les
  responsabilités encore portées par les sept traits
  `legacy/processor`. Les six classes Crosstec protégées
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
> (`media/com_breezingformsng/images/site/captcha/securimage.php`, sans rapport avec ce changement — nettoyage
> possible mais hors périmètre de la Phase 9). Journal Joomla surveillé sur toute la fenêtre de test : aucune entrée
> liée à `BFRequest`/`Callback`. `Stripe`/`PayPal`/`Sofort` restent à confirmer avec un vrai paiement de test (accès
> à un compte sandbox nécessaire, non disponible dans cette session).
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
> `legacy/functions.php::saveOtherParam()` (2 appels) et `src/Helper/legacy/route.php::getFormRoute()` (1 appel)
> convertis aussi au passage (aucun `setVar()` pour leurs clés). **Total Phase 9a : 326 appels convertis, zéro
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

### Vérification (à la complétion de la Phase 9)

- [ ] Chaque service qui remplace un fichier crosstec repasse les scénarios déjà validés dans ce document :
  rendu de formulaire (tous thèmes QuickMode), soumission SEF, callbacks de paiement (Stripe/PayPal/Sofort),
  upload, `commit()` Intégrateur — sans régression.
- [ ] Les appels internes et la bibliothèque externe continuent de fonctionner avec l'API publique conservée de
  `BFRequest`, `BFIntegrate` et `BFQuickMode*`.
- [x] Conservation obligatoire des 6 fichiers `libraries/crosstec/classes/BF{Request,Integrate,QuickMode,QuickModeBootstrap,QuickModeMobile,QuickModeOnePage}.php`
  et de leurs chargements : API utilisée par une bibliothèque externe.

---

## Reliquat hors Phase 9

- [ ] **Phase 2 — Permissions ACL** : la modification effective d'une règle (Autoriser/Refuser puis Enregistrer)
  n'a pas pu être testée par un agent — tentative bloquée le 2026-07-12 par le classificateur de permissions de
  l'environnement (modification de droits ACL jugée sensible, même sur le site de dev). Nécessite soit une
  confirmation explicite de l'utilisateur avant qu'un agent retente, soit une vérification manuelle directe :
  Composants → BreezingForms NG → Droits → choisir un groupe → Autoriser/Refuser une action → Enregistrer →
  rouvrir l'écran et confirmer que la valeur a persisté.

- ~~Site de dev (`joomla6-joomla-1`) — panne préexistante et sans rapport, observée le 2026-07-12~~ **Corrigée par
  l'utilisateur le 2026-07-12** : `com_contentbuilderng` (extension distincte installée sur le même site) faisait
  planter en HTTP 500 toute page déclenchant le rendu complet du template (menu de site, y compris les pages
  d'erreur 404) via une classe `ContentbuilderngComponent` introuvable dans son `services/provider.php`. Le site
  répond de nouveau normalement (404 sur une URL inconnue, rendu complet sans `tmpl=component` nécessaire) ; les
  callbacks `optOut`/`checkCaptcha` ont pu être revérifiés en HTTP réel dans la foulée (cf. note Phase 9a ci-dessus).

---

## Points restants à faire (état au 2026-07-16)

### Vérifications bloquées ou manuelles

- [ ] **Callbacks de paiement en conditions réelles** : `Stripe`/`PayPal`/`Sofort` convertis en QueryBuilder et
  validés par `php -l` + revue, mais jamais confirmés par un vrai paiement de test — accès à un compte sandbox
  nécessaire, non disponible en session agent (cf. notes Phase 9a).
- [ ] **Phase 2 — Permissions ACL** : test de persistance d'une règle Autoriser/Refuser à faire manuellement
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

- [ ] **Phase 9c** : la réécriture native des 4 rendus frontend `BFQuickMode*` (~11 100 lignes cumulées) est le
  dernier gros chantier « Joomla 6 pur ». À traiter thème par thème en commençant par `BFQuickMode.php`
  (le plus petit, thème par défaut). Les Phases 1–8 et 9a/9b sont terminées.
- [ ] **Vérification finale Phase 9** : repasse complète des scénarios (rendu tous thèmes, soumission SEF,
  callbacks paiement, upload, `commit()` Intégrateur).

### Rappels permanents

- Ne jamais supprimer les classes crosstec (`BFRequest`, `BFFactory`, `BFText`, `BFJoomlaConfig`, `BFPDF`,
  `BFIntegrate`, `BFQuickMode*`) sur la seule foi d'un grep du dépôt : du PHP stocké en base
  (`facileforms_pieces.code`, `forms.piece*code`) peut les appeler à l'exécution.
- Déployer les fichiers de langue aux **deux** emplacements (`administrator/components/…/language/` et
  `administrator/language/`) puis vider `administrator/cache/language/` — l'agent `deploy` l'encode.
- Toute chaîne utilisateur passe par les trois langues en-GB/fr-FR/de-DE simultanément (skill
  `joomla-translations`).
