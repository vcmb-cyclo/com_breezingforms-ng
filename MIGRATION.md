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
- [ ] Routeur admin central (`admin.breezingforms.php` — 766 lignes, `$act`-routing)
- [x] Gestion des enregistrements (`admin/recordmanagement.class.php` — migré Phase 1)
- [ ] Configuration (`admin/config.class.php` + `config.html.php` — 1 078 lignes)
- [ ] Intégrateur (`admin/integrator.class.php` + `.html.php`)
- [ ] Gestion des menus (`admin/menu.class.php` + `menu.html.php`)
- [ ] Gestionnaire de formulaires (`admin/form.class.php` + `form.html.php` — ~180K lignes)
- [ ] Éditeur d'éléments (`admin/element.class.php` + `element.html.php` — ~171K lignes)
- [ ] QuickMode (`admin/quickmode.class.php` + `.html.php` + `.js` — ~560K lignes)
- [ ] EasyMode (`admin/easymode.*`)
- [ ] Import/export paquets (`admin/import.class.php`)
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
- [ ] Liste des enregistrements s'affiche avec filtres et pagination
- [ ] Export PDF, CSV, XML fonctionnels
- [ ] Import CSV fonctionnel
- [ ] Édition et sauvegarde d'un enregistrement
- [ ] Flags (viewed / exported / archived) fonctionnels en masse et unitaire
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

> **Note Phase 6** : le runtime legacy (`$ff_config->csvdelimiter` etc.) lit toujours depuis `#__facileforms_config`.
> La migration des lectures runtime vers `ComponentHelper::getParams()` se fera en Phase 6.
> `src/Model/ConfigModel.php` contiendra la logique de migration.

### Vérification
- [ ] Composants → BreezingForms NG → Options ouvre l'écran natif Joomla
- [ ] Sauvegarde des paramètres → persistance en base (`#__extensions` params)
- [ ] Permissions ACL visibles et fonctionnelles

---

## Phase 3 — Intégrateur `act=integrate`

**Priorité : moyenne.**

### Fichiers à créer
- [ ] `src/Controller/IntegratorController.php`  
  Tasks : `display`, `save`, `delete`, `test`
- [ ] `src/Model/IntegratorModel.php`
- [ ] `src/View/Integrator/HtmlView.php`
- [ ] `tmpl/integrator/default.php`

### Fichiers à modifier
- [ ] `src/Controller/DisplayController.php` — ajouter `view=integrator`

### Fichiers à supprimer
- [ ] `administrator/components/com_breezingformsng/admin/integrator.class.php`
- [ ] `administrator/components/com_breezingformsng/admin/integrator.html.php`
- [ ] `administrator/components/com_breezingformsng/admin/integrator.php`

### Vérification
- [ ] Liste des intégrations s'affiche
- [ ] Création / sauvegarde / suppression d'une intégration
- [ ] Test d'intégration fonctionnel

---

## Phase 4 — Gestion des menus `act=managemenus`

**Priorité : faible. Fonctionnalité réduite.**

### Fichiers à créer
- [ ] `src/Controller/MenusController.php`
- [ ] `src/Model/MenuModel.php`
- [ ] `src/View/Menus/HtmlView.php`
- [ ] `tmpl/menus/default.php`

### Fichiers à supprimer
- [ ] `administrator/components/com_breezingformsng/admin/menu.class.php`
- [ ] `administrator/components/com_breezingformsng/admin/menu.html.php`
- [ ] `administrator/components/com_breezingformsng/admin/menu.php`

### Vérification
- [ ] Liste / création / suppression d'éléments de menu

---

## Phase 5 — Gestionnaire de formulaires `act=manageforms` + éditeur `act=editpage`

**Priorité : critique. Effort très élevé (cœur du produit).**

### Fichiers à créer
- [ ] `src/Controller/FormsController.php`  
  Tasks : `list`, `add`, `edit`, `save`, `copy`, `delete`, `publish`, `unpublish`, `quickmode`, `quickmode_editor`
- [ ] `src/Controller/ElementsController.php`  
  Tasks : `edit`, `save`, `delete`, `add`, `copy`
- [ ] `src/Model/FormModel.php` — CRUD formulaire unique
- [ ] `src/Model/FormsModel.php` — liste filtrée
- [ ] `src/Model/ElementModel.php`
- [ ] `src/View/Forms/HtmlView.php` + templates
- [ ] `src/View/Elements/HtmlView.php` + templates

### Fichiers à modifier
- [ ] `src/Controller/DisplayController.php` — retirer les `case manageforms / editpage / quickmode / run`
- [ ] QuickMode JS (`admin/quickmode-app.js`) — adapter les endpoints AJAX vers les nouvelles routes controller

### Fichiers à supprimer
- [ ] `administrator/components/com_breezingformsng/admin/form.*`
- [ ] `administrator/components/com_breezingformsng/admin/element.*`
- [ ] `administrator/components/com_breezingformsng/admin/quickmode.*`
- [ ] `administrator/components/com_breezingformsng/admin/easymode.*`
- [ ] `administrator/components/com_breezingformsng/admin/run.php`
- [ ] `administrator/components/com_breezingformsng/admin/import.class.php` (si intégré au FormModel)

### Vérification
- [ ] Lister les formulaires
- [ ] Créer / éditer / sauvegarder un formulaire
- [ ] Ajouter / modifier / supprimer des éléments
- [ ] Ouvrir et utiliser QuickMode
- [ ] Prévisualiser un formulaire (`act=run`)
- [ ] Importer / exporter un paquet

---

## Phase 6 — Suppression du bridge legacy

**À faire uniquement quand toutes les phases 1–5 sont complètes.**

- [ ] Vider `DisplayController::display()` de son `include admin.breezingforms.php`
- [ ] Supprimer `administrator/components/com_breezingformsng/admin.breezingforms.php`
- [ ] Supprimer `toolbar.facileforms.php` et `toolbar.facileforms.html.php`
- [ ] Supprimer `src/Helper/LegacyClassLoader.php`
- [ ] Retirer l'enregistrement de `LegacyClassLoader` dans `services/provider.php`
- [ ] Supprimer le répertoire `admin/` s'il est vide

### Vérification finale
- [ ] Naviguer dans **tous** les écrans admin sans erreur
- [ ] Aucun `include` ou `require` vers `admin/` ne subsiste dans le call stack
- [ ] `php -l` sur tous les fichiers `src/` : aucune erreur de syntaxe

---

## Phase 7 — Frontend (hors périmètre immédiat)

> Projet de refonte dédié. Ne pas commencer avant que les phases 1–6 soient validées.

- [ ] Décomposer `facileforms.process.php` (448 KB) en services :  
  `FormRenderer`, `FormProcessor`, `SubmissionHandler`, intégrations (Stripe, Mailchimp, Dropbox…)
- [ ] Créer `src/Controller/FormController` côté site (remplacer `breezingformsng.php`)
- [ ] Créer `src/View/Form/HtmlView.php` (remplacer le rendering legacy)
- [ ] Migrer `router.php` vers `RouterInterface` Joomla 6
