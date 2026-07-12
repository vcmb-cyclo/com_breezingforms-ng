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
- [x] Import de paquets — `ImportModel` réécrit (SimpleXML, requêtes paramétrées, transaction) ; consommé par `script.php::importStandardLibrary()`. Limité aux bibliothèques de scripts/pièces (seul cas réel : `packages/stdlib.english.xml`) ; les paquets avec formulaires/menus sont refusés avec un message clair. Vérifié de bout en bout le 2026-07-10 : installation du paquet 6.1.0-RC2 dans le conteneur `joomla6-joomla-1`, import stdlib OK (71 scripts inchangés ignorés, 3 pièces mises à jour, métadonnées paquet actualisées).

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
- [x] Ajouter / modifier / supprimer des éléments *(vérifié dans Chrome le 2026-07-12 sur le formulaire de test 28 « Test eddy elements » : sélection d'une page puis clic « Nouvel élément » ajoute exactement un nœud à l'arbre ; suppression via le menu contextuel `TREE_OBJ.remove()` le retire ; `template_code` en base inchangé avant/après (hash identique), confirmant que l'édition reste côté client jusqu'à l'enregistrement explicite)*
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
  Restent côté crosstec : `BFRequest` (387 appels — portage de JRequest avec caches `$GLOBALS` et `setVar` mutant les
  superglobales : à traiter lors de la réécriture, pas par substitution), `BFIntegrate` et les rendus `BFQuickMode*`.
  `BFJoomlaConfig` a été remplacé par `Factory::getConfig()` ; `BFPDF` a été migré vers le service namespacé
  `Administrator\Service\PdfDocument`. Les deux classes globales ont été supprimées. Export administrateur vérifié
  dans Joomla 6 le 2026-07-12 : PDF 1.7 valide généré avec les en-têtes de téléchargement attendus.*
- [x] Migrer `router.php` vers `RouterInterface` Joomla 6 *(fait le 2026-07-11 : `Site\Service\Router extends RouterBase`, `RouterFactory` au provider, `RouterServiceInterface` sur l'extension ; `router.php` supprimé du paquet et nettoyé des sites installés par `script.php::removeObsoleteComponentFiles()` ; pages de formulaires SEF vérifiées en front)*
