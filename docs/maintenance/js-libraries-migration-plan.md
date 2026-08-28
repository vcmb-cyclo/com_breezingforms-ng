# Plan de migration des librairies JavaScript vendorisées

> Document de suivi. Cocher chaque tâche à la complétion. Aucune de ces
> librairies n'est gérée par un gestionnaire de paquets front-end (pas de
> `package.json` de dépendances runtime) : tout est copié en dur sous
> `administrator/components/com_breezingformsng/libraries/` et
> `components/com_breezingformsng/libraries/`.

## Méthode

Pour chaque librairie : vérifier s'il existe une version amont plus récente
et compatible en drop-in (même API, même mode de chargement `<script>`
classique — le projet n'a pas de pipeline de build JS), avant de choisir
entre bump de version et remplacement complet.

## Inventaire et verdict par librairie

| Librairie | Version vendorisée | Fichiers appelants | Verdict après vérification amont | Effort |
|---|---|---|---|---|
| **Plupload** (Moxiecode/Ephox) | 3.1.2 (2018) | 4 (`*Renderer.php` QuickMode) | Code JS identique octet pour octet à la dernière release amont (v3.1.5, tag GitHub 2021) — **déjà à jour fonctionnellement**. Seuls les binaires Flash/Silverlight (`Moxie.swf`, `Moxie.xap`) sont morts (Flash Player n'existe plus dans aucun navigateur). | 🟢 Trivial — suppression de fichiers morts uniquement |
| **Remodal** | 1.1.1 | 1 (à identifier dans les templates QuickMode) | `latest` npm = 1.1.1, publié 2017 — **déjà à jour**, projet quasi à l'arrêt mais rien à bumper | ⚪ Aucune action possible |
| **pickadate.js** (amsul) | 3.6.4 (2019) | 4 | `latest` stable npm = 3.6.4 — **déjà à jour**. Les versions 5.0.0-alpha/prealpha existent mais sont non stables, projet non maintenu depuis 2019 | ⚪ Aucune action possible sans réécriture complète |
| **Ladda** (hakimel) | 1.0.6 (2018) | 1 (`OnePageRenderer.php` — spinner du bouton submit) | 🔴 **Bloqué.** Paquet 2.0.3 téléchargé et inspecté directement (pas juste les métadonnées) : contient uniquement `js/ladda.js` en **vrai module ES** (`import {Spinner} from 'spin.js'`, un import "bare specifier" que les navigateurs ne résolvent pas nativement), pas de build UMD/IIFE, pas de fichier minifié, pas de plugin jQuery. Migrer exigerait d'introduire un bundler JS (Rollup/esbuild) dans un projet qui n'en a délibérément aucun — disproportionné pour un simple spinner de bouton. 1.0.6 reste fonctionnel, ne présente aucun risque de sécurité (lib d'animation purement client-side, aucune API Joomla touchée) | ⛔ Bloqué — nécessite une décision produit (introduire un bundler) hors périmètre de ce chantier |
| **jQuery UI** (embarqué, `jq-ui.min.js`) | 1.6rc4 (~2008) | 3 (admin About, Quickmode) | Très en retard (actuel : 1.13.x) ; à vérifier quels widgets sont réellement utilisés (probablement juste `.dialog`/`.sortable`/`.tooltip`, remplaçables par Bootstrap natif Joomla 6) | 🔴 Élevé — remplacement, pas un bump |
| **jquery.validationEngine** | 1.6.3 (2009) | 5 | Plugin non maintenu depuis ~2015 ; pas de successeur direct compatible drop-in | 🔴 Élevé — remplacement (validation HTML5 native + JS maison, ou Joomla `formvalidator`) |
| **jTable** | 2.4.0 (2011-2014) | 2 (admin Pieces/Scripts renderers) | Projet à l'arrêt depuis ~2015, pas de version plus récente | 🔴 Élevé — remplacement (probablement par les tables Joomla natives `searchtools`) |
| **SweetAlert** (v1) | 1.x → **2 (11.26.25)** | 3 (QuickMode Bootstrap/Classic/OnePage) + 1 appel `swal(error)` dans le stdlib package importable (`packages/stdlib.english.xml`, pas du code first-party) | v1 abandonnée depuis 2017 au profit de **SweetAlert2**. Le bundle officiel `sweetalert2.all.min.js` expose nativement un alias `window.swal` rétrocompatible (pas un shim ajouté ici) | ✅ Fait — bundle vendorisé remplacé, 3 points d'appel PHP repointés, appel du stdlib modernisé en `Swal.fire()` |
| **jQuery Mobile** (`jq.mobile.min.js`) | 1.4.5 (2014) | 1 (MobileRenderer) | ⚠️ Projet **officiellement abandonné** par jQuery Foundation en 2015. Pas de version plus récente, jamais. | 🔴 Très élevé — réécriture complète du rendu mobile (hors scope JS pur) |
| **overLIB** | 4.21 (2004) | 1 (RenderingEngine, tooltips) | Techno pré-jQuery, auteur inactif depuis longtemps | 🟡 Moyen — remplacement par tooltip Bootstrap natif (déjà utilisé ailleurs dans le composant) |
| **wz_dragdrop** | non versionné (~2000s) | 0 — chargée mais jamais appelée | Audit du dépôt entier : aucun code (PHP inline ou JS) n'appelle l'API exposée (`dd.setCe`, `dd.regImg`, etc.). Le `<script>` était chargé en mode preview sans jamais être utilisé — **code mort pur**, pas une lib legacy active | ✅ Fait — suppression pure, pas de remplacement nécessaire |
| **jQuery core** | — | — | Déjà migré : le composant utilise le jQuery natif de Joomla via `jquery-alias.js`/`jquery-restore.js`. Rien à faire. | ✅ Fait |

## Ordre de traitement recommandé

1. ✅ **Nettoyage mort (aujourd'hui)** : suppression des binaires Flash/Silverlight inertes de Plupload (`Moxie.swf`, `Moxie.xap`) — zéro risque, zéro changement de comportement observable (Flash Player n'existe plus nulle part).
2. ✅ **overLIB → tooltip Bootstrap natif (aujourd'hui)** — `RenderingEngine.php` (élément "Tooltip") migré vers `HTMLHelper::_('bootstrap.tooltip', '.hasTooltip')`, même pattern déjà utilisé par les renderers QuickMode sur le frontend. `overlib_mini.js` supprimé (fichier + entrée `joomla.asset.json` orpheline).
3. ✅ **wz_dragdrop supprimé (aujourd'hui)** — audit du dépôt : zéro appel à son API n'importe où dans le code. Le chargement conditionnel en mode preview (`RenderingEngine.php`), l'entrée `joomla.asset.json` orpheline et le dossier vendorisé (`wz_dragdrop.js`, `transparentpixel.gif`) ont été retirés. Pas de remplacement — il n'y avait rien à remplacer.
4. ✅ **SweetAlert v1 → SweetAlert2 (aujourd'hui)** — `sweetalert.min.js` (v1) remplacé par `sweetalert2.min.js` (v11.26.25, bundle officiel `sweetalert2.all.min.js`) ; les 3 `RuntimeAssetLoader::script()` (ClassicRenderer, OnePageRenderer, BootstrapRenderer) repointés. Le seul appel réel trouvé dans tout le dépôt (`swal(error)` dans le piece `gn_validate_page_fin` du stdlib package importable) modernisé en `Swal.fire(error)` ; reste compatible même sans cette modernisation grâce à l'alias `window.swal` que SweetAlert2 fournit lui-même.
5. ⛔ **Ladda 1.0.6 → 2.0.3 — bloqué (vérifié aujourd'hui)** : le paquet 2.x n'a plus de build utilisable en `<script>` classique (voir tableau ci-dessus). Reste sur 1.0.6 tant qu'aucun pipeline de build JS n'existe pour le composant ; à revisiter si ce chantier devient nécessaire pour une autre raison (le bundler ne se justifie pas pour ce seul cas).
6. **jTable → Joomla `searchtools`** — périmètre admin uniquement (Pieces/Scripts), formulaire net à circonscrire.
7. **jquery.validationEngine → validation native** — 5 points d'appel, à auditer un par un.
8. **jQuery UI → Bootstrap natif Joomla 6** — à mener après (3) et (6), une fois qu'on a une idée claire des widgets jQuery UI réellement utilisés.
9. **jQuery Mobile → réécriture du rendu mobile** — le plus gros chantier, à traiter en dernier et isolément (impacte tout `MobileRenderer.php`), potentiellement en dehors du périmètre "librairies JS" (c'est une réécriture de rendu).

## Notes de vérification

- Vérifications faites via le registre npm et les tags GitHub officiels (Remodal, pickadate, Plupload, Ladda) le 2026-07-24.
- Plupload : le fichier vendorisé `plupload.js` est byte-for-byte identique au source du tag GitHub `v3.1.5` malgré l'en-tête `v3.1.2` — aucune évolution fonctionnelle entre ces tags.
- Pour les librairies marquées "aucune action possible", revérifier périodiquement (une fois par an) au cas où le projet reprendrait vie ou qu'un fork actif apparaîtrait.
