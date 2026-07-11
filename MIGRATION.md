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
- [ ] Frontend moteur formulaires (`facileforms.process.php` — **448 KB**, hors périmètre immédiat)

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
- [ ] Import CSV fonctionnel
- [ ] Édition et sauvegarde d'un enregistrement
- [x] Flags (viewed / exported / archived) fonctionnels en unitaire *(setFlag vérifié ; bascule en masse à confirmer à la main)*
- [ ] Bouton Help ouvre la modale

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
- [ ] Sauvegarde des paramètres → persistance en base (`#__extensions` params)
- [ ] Permissions ACL visibles et fonctionnelles

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
- [ ] Création d'une nouvelle règle (insert/update)
- [ ] Ajout/suppression d'items et critères
- [ ] Éditeur de code (CodeMirror) fonctionnel
- [ ] Publish/unpublish règle et item

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
- [x] Import de paquets — `ImportModel` réécrit (SimpleXML, requêtes paramétrées, transaction) ; consommé par `script.php::importStandardLibrary()`. Limité aux bibliothèques de scripts/pièces (seul cas réel : `packages/stdlib.english.xml`) ; les paquets avec formulaires/menus sont refusés avec un message clair. Vérifié de bout en bout le 2026-07-10 : installation du paquet 6.1.0-RC2 dans le conteneur `joomla6-joomla-1`, import stdlib OK (71 scripts inchangés ignorés, 3 pièces mises à jour, métadonnées paquet actualisées).

### Migré depuis cette phase
- QuickMode — déplacé en Phase 7 vers `QuickmodeController`, `QuickmodeModel`, `QuickmodeHtml` et routes `task=quickmode.*`
- QuickMode JS AJAX endpoint — migré vers `task=quickmode.doAjaxSave`

### Vérification
- [x] Lister les formulaires (filtres package, état, recherche ; tri ; pagination) *(liste vérifiée HTTP)*
- [ ] Créer / éditer / sauvegarder un formulaire (3 onglets de propriétés)
- [x] Publier / dépublier un formulaire *(vérifié aller-retour en base ; duplication/suppression à confirmer à la main)*
- [x] Ouvrir QuickMode depuis la liste *(quickmode.display rendu complet, 1,6 Mo)*

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
- [ ] Supprimer `src/Helper/LegacyClassLoader.php` (QuickModeHtml + BF* encore nécessaires ; classes mortes `BFTabs`, `BFJNewTabs`, `BFBehaviorTabs`, `BFPagination`, `BFPaginationChrome`, alias `BFTableElements` déjà supprimées)
- [ ] Retirer l'enregistrement de `LegacyClassLoader` dans `services/provider.php`
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
- [x] `src/Helper/QuickmodeHtml.php` — renderer legacy déplacé, enregistré dans `LegacyClassLoader`
- [x] `src/Controller/QuickmodeController.php` — tasks : `display`, `doAjaxSave`, `editor`
- [x] `src/View/Quickmode/HtmlView.php` — vue Joomla 6 native, configure toolbar
- [x] `tmpl/quickmode/default.php` — appelle `QuickModeHtml::showApplication()`
- [x] `tmpl/quickmode/editor.php` — éditeur inline (git mv depuis `quickmode-editor.php`), task mise à jour vers `quickmode.editor`
- [x] `DisplayController` — alias legacy QuickMode (`act=quickmode*`, `act=manageforms&task=quickmode*`) retirés après migration des liens internes
- [x] `admin.breezingforms.php` — bridge entièrement vidé (switch ne contient plus que `default: break`)
- [x] `admin/quickmode.php`, `admin/quickmode.class.php`, `admin/quickmode.html.php`, `admin/quickmode-editor.php` — supprimés
- [x] Liens internes migrés vers routes MVC `task=quickmode.display` / `task=quickmode.editor`
- [x] Alias `act=quickmode*` supprimés de `DisplayController`
- [x] Save AJAX QuickMode : `quickmode-app.js` poste sur `task=quickmode.doAjaxSave`, puis redirige vers `quickmode.display`
- [x] `QuickmodeController::doAjaxSave()` crée `media/breezingforms/ajax_cache` si absent, valide les bornes de chunks et refuse les payloads JSON invalides

### Vérification
- [x] Ouvrir QuickMode depuis la liste des formulaires *(quickmode.display rendu complet, 1,6 Mo)*
- [x] Sauvegarder un formulaire (AJAX chunked save → `quickmode.doAjaxSave`) *(vérifié HTTP authentifié le 2026-07-11 : formulaire 8, réponse `8`, hash `template_code` inchangé)*
- [ ] Ajouter / modifier / supprimer des éléments
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

- [ ] Décomposer `facileforms.process.php` (448 KB) en services :  
  `FormRenderer`, `FormProcessor`, `SubmissionHandler`, intégrations (Stripe, Mailchimp, Dropbox…)
- [ ] Créer `src/Controller/FormController` côté site (remplacer `breezingformsng.php`)
- [ ] Créer `src/View/Form/HtmlView.php` (remplacer le rendering legacy)
- [x] Migrer `router.php` vers `RouterInterface` Joomla 6 *(fait le 2026-07-11 : `Site\Service\Router extends RouterBase`, `RouterFactory` au provider, `RouterServiceInterface` sur l'extension ; `router.php` supprimé du paquet et nettoyé des sites installés par `script.php::removeObsoleteComponentFiles()` ; pages de formulaires SEF vérifiées en front)*
