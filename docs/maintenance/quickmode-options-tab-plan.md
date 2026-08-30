# Plan — Rapatrier « Plus d'options » comme onglet « Options » dans QuickMode

> Demande du 2026-08-30 : le lien « Plus d'options » de l'écran QuickMode
> (`task=quickmode.display&form=...`) doit devenir un onglet normal appelé
> « Options » dans ce même écran, au lieu de naviguer vers l'écran classique.
> Approche retenue : **réutiliser le contenu existant** (recommandée,
> confirmée par l'utilisateur) — pas de réécriture des réglages en propriétés
> QuickMode natives, pas d'iframe.

## État actuel

### Deux écrans, deux modèles de persistance distincts

- **QuickMode** (`task=quickmode.display&form=ID`) — `QuickmodeHtml::showApplication()`
  affiche un éditeur d'arbre avec deux onglets Bootstrap dans `#menutab` :
  - `fragment-1` « Propriétés » (`COM_BREEZINGFORMSNG_PROPERTIES`)
  - `fragment-2` « Avancé » (`COM_BREEZINGFORMSNG_ADVANCED`), qui contient
    `advanced_form.php` — celui-ci affiche le lien « Plus d'options »
    (`COM_BREEZINGFORMSNG_MORE_OPTIONS`) pointant vers
    `task=forms.edit&id=ID&advanced=1`.
  - Les deux onglets sont **imbriqués dans `<form name="bfForm" onsubmit="return false">`**.
    Leurs boutons « Enregistrer » (`bfPropertySaveButton*`,
    `bfAdvancedSaveButton*`) sont des `<input type="submit">` mais la
    soumission native est bloquée ; un handler jQuery
    (`appScope.saveButton`, `media/com_breezingformsng/js/admin/quickmode-app.js:527-539`)
    sérialise l'arbre en mémoire. La persistance réelle se fait ailleurs
    (action globale « Enregistrer » du toolbar), en un seul blob JSON
    (`template_code`) couvrant tout le formulaire.
- **Écran classique** (`task=forms.edit&id=ID&advanced=1`, routé par
  `FormsController::edit()` uniquement quand `advanced=1`) —
  `tmpl/forms/edit.php` (489 lignes) affiche un **second** système d'onglets
  Bootstrap indépendant (`#bfFormTabs`) : Général, Email, Scripts, Pièces
  formulaire/soumission, MailChimp, Salesforce, Dropbox. Le tout est dans
  **son propre** `<form id="adminForm" name="adminForm" method="post"
  action="index.php?option=com_breezingformsng">`, soumis nativement vers
  `task=forms.save` (`FormsController::save()`), qui écrit directement des
  dizaines de colonnes SQL sur `#__facileforms_forms` (aucun rapport avec le
  JSON `template_code`).
- Les données de `forms/edit.php` viennent de
  `FormModel::getForm(int $id): ?\stdClass` (`administrator/.../src/Model/FormModel.php:44`),
  chargé dans `View\Forms\HtmlView::display()`.
- Aucun recouvrement de contenu entre les deux écrans : les réglages de
  l'écran classique (email, scripts globaux, pièces, intégrations
  MailChimp/Salesforce/Dropbox) ne sont **pas** dupliqués dans les onglets
  Propriétés/Avancé de QuickMode.

### Contrainte HTML bloquante

`fragment-3` (le futur onglet « Options ») devra contenir le même balisage
que `forms/edit.php`, y compris **son propre `<form>`**. Comme `fragment-1`
et `fragment-2` sont aujourd'hui imbriqués dans `<form name="bfForm">`, on ne
peut pas ajouter `fragment-3` au même endroit sans imbriquer deux `<form>`
(interdit en HTML, comportement de soumission indéterminé selon les
navigateurs). Il faut resserrer `<form name="bfForm">` autour de
`fragment-1`/`fragment-2` uniquement, et laisser `fragment-3` en dehors —
tout en restant un enfant direct de `.tab-content` (Bootstrap bascule les
`.tab-pane` par classe, pas par profondeur DOM, donc ce déplacement est safe
pour le comportement des onglets).

## Étapes proposées, dans l'ordre

1. **Extraire le contenu de `forms/edit.php` en layout réutilisable**, sans
   changement de comportement sur l'écran classique lui-même : sortir le
   bloc `<ul id="bfFormTabs">...` + `<div class="tab-content">...</div>`
   (tout sauf le `<form>` englobant et le bouton Enregistrer final) dans
   `administrator/components/com_breezingformsng/layouts/forms/advanced_options.php`,
   appelé via `LayoutHelper::render()` avec les mêmes variables (`$f`, `$pkg`,
   `$editor`, `$tabEntryCounts`, les fonctions locales `bfSel`/`$countConfigured`
   à convertir en méthodes statiques ou closures passées en paramètre).
   `forms/edit.php` l'inclut à la place du bloc inline.
   - Vérification : capture HTML avant/après (`ob_start`/`ob_get_clean`) sur
     un rendu contrôlé, diff strict, aucun octet différent.
2. **Resserrer `<form name="bfForm">`** dans `QuickmodeHtml::showApplication()`
   pour qu'il n'entoure que `fragment-1` et `fragment-2` (pas tout
   `#menutab`). Vérifier en direct que les onglets Propriétés/Avancé
   fonctionnent toujours (sélection d'un nœud, edition d'une propriété,
   sauvegarde) — c'est le point le plus sensible du plan, à isoler dans son
   propre commit avec vérification navigateur avant d'ajouter quoi que ce
   soit d'autre.
3. **Charger la ligne complète du formulaire dans le contrôleur/la vue
   QuickMode.** `QuickmodeController`/`QuickmodeModel` n'exposent
   aujourd'hui que `getFormOptions()`/`getTemplateCode()` (métadonnées
   QuickMode). Ajouter un appel à `FormModel::getForm($formId)` (même modèle
   que l'écran classique) et transmettre `$f` à la vue, uniquement quand
   `$formId !== 0` (formulaire déjà créé — un nouveau formulaire non
   enregistré n'a pas encore de ligne `#__facileforms_forms` à côté du JSON).
4. **Ajouter l'onglet `fragment-3` « Options »** dans `#menutab` :
   - Bouton `nav-link` avec le libellé `COM_BREEZINGFORMSNG_OPTIONS` (clé
     déjà traduite dans les 8 langues, aucune nouvelle chaîne nécessaire
     pour le libellé lui-même).
   - Pane `<div id="fragment-3">` contenant **son propre**
     `<form id="bfOptionsForm" method="post"
     action="index.php?option=com_breezingformsng">` avec le layout extrait
     à l'étape 1, un champ caché `task=forms.save`, le jeton CSRF Joomla, et
     `id`/`pkg` déjà connus du contexte QuickMode.
   - Comportement de sauvegarde : soumission native de `bfOptionsForm`
     (rechargement de page, cohérent avec le comportement actuel de l'écran
     classique) plutôt qu'une tentative de fusion AJAX avec le flux
     QuickMode — évite de dupliquer la logique de `FormsController::save()`
     et respecte le choix « réutiliser le contenu existant ».
     `FormsController::save()` redirige déjà vers
     `task=quickmode.display&form=ID` après écriture ; à ajuster pour
     revenir spécifiquement sur l'onglet Options (ancre `#fragment-3` ou
     paramètre de requête lu par `quickmode-app.js` au chargement).
5. **Retirer le lien « Plus d'options »** de `advanced_form.php` (et son CSS
   dédié `.btn-more-options` dans `custom.js`), maintenant que son contenu
   est atteignable sans quitter l'écran. Garder la route
   `task=forms.edit&advanced=1` elle-même active (pas de suppression) : elle
   reste le point d'entrée pour la création d'un formulaire (`id<=0`) via
   `FormsController::edit()`, cas hors périmètre de cette demande.
6. **Traductions** : mettre à jour les 8 fichiers `.ini`
   (en-GB/fr-FR/de-DE/it-IT/es-ES/hu-HU/nl-NL/tr-TR) uniquement si de
   nouvelles chaînes apparaissent (aide contextuelle sur l'onglet, message de
   confirmation) — à date, `COM_BREEZINGFORMSNG_OPTIONS` suffit pour le
   libellé de l'onglet lui-même.
7. **Vérification complète** avant commit, comme pour chaque lot de cette
   migration : `php -l` sur les fichiers touchés, suite PHPUnit complète,
   PHPStan niveau courant, vérification navigateur en direct (ouvrir
   QuickMode sur un formulaire existant, onglet Options, modifier un champ
   type Email, enregistrer, recharger, confirmer la persistance), build +
   validation du package.

## Risques identifiés

- **Imbrication de deux systèmes d'onglets Bootstrap** (le `#menutab` de
  QuickMode et le `#bfFormTabs` de l'écran classique, une fois copié dans
  `fragment-3`) : les identifiants sont déjà uniques entre les deux écrans
  (`tab-general`/`pane-general` côté classique, `fragment-1`/`fragment-2`
  côté QuickMode), donc pas de collision attendue, mais à confirmer en
  direct après l'étape 4.
- **Deux formulaires HTML sur une même page** (`bfForm` restreint aux
  onglets Propriétés/Avancé, `bfOptionsForm` pour Options) : `document.adminForm`
  est utilisé ailleurs dans `quickmode-app.js` (ligne 931, pour lire
  `active_language_code`) — vérifier qu'aucun script ne suppose qu'un seul
  `<form>` existe sur la page avant de renommer/dupliquer des noms de champs.
- **CodeMirror** : les instances de l'écran classique utilisent le préfixe
  `jf_` (`jf_script1code`, `jf_script2code`, `jf_<piece>code`), distinct des
  identifiants QuickMode (`bf*`) — pas de collision identifiée, à confirmer
  une fois les deux écrans rendus simultanément.
- **Redirection post-sauvegarde** de `FormsController::save()` : actuellement
  pensée pour un écran plein-page ; à ajuster pour rouvrir QuickMode sur
  l'onglet Options plutôt que sur l'onglet Propriétés par défaut.
- **Formulaire non encore créé** (`$formId === 0`) : l'onglet Options n'a pas
  de ligne SQL à afficher/enregistrer tant que le formulaire n'existe pas.
  Décision à confirmer : griser l'onglet, ou le masquer, jusqu'à la première
  sauvegarde QuickMode qui crée la ligne.

## Critère de sortie

`task=quickmode.display&form=ID` expose un troisième onglet « Options »
donnant accès à l'intégralité des réglages actuellement uniquement
disponibles via `task=forms.edit&advanced=1`, sans quitter l'écran QuickMode
et sans dupliquer la logique de sauvegarde de `FormsController::save()`. Le
lien « Plus d'options » a disparu de l'onglet Avancé.
