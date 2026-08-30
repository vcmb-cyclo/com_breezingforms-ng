# Plan de migration de l'architecture PHP

> Suivi de la modernisation PHP Joomla 6 / PHP 8.3+. Les compatibilités
> Joomla et PHP antérieures ne font pas partie du périmètre.

## Principes

- Extraire une responsabilité à la fois depuis les façades historiques.
- Ajouter un test de caractérisation avant, ou dans le même commit que,
  l'extraction.
- Préserver les contrats publics nécessaires au code de formulaires stocké en
  base jusqu'à leur migration explicite.
- Préférer les services Joomla 6 natifs et les types PHP stricts pour tout
  nouveau code.

## État actuel

| Domaine | État | Référence |
|---|---|---|
| Uploads, callbacks, exports et permissions | Couvert et modernisé par lots | Tests de services Site/Admin |
| Renderers QuickMode | Filets de caractérisation en place pour Classic, Bootstrap, Mobile et OnePage | `tests/Site/Service/Rendering/QuickMode/` |
| `RenderingEngine::header()` | Extrait vers `ProcessorHeaderRenderer` et couvert | `RenderingEngineViewCharacterizationTest` |
| `RenderingEngine::view()` — entrée QuickMode | Gardes, métadonnées, mode mobile, session et choix mobile couverts | `RenderingEngineViewCharacterizationTest` |
| `RenderingEngine::view()` — shell formulaire | Initialisation, fermeture et wrappers legacy/modernes extraits et couverts | `RenderingEngine` |
| `RenderingEngine::view()` — pièces | Pièces Before/After, code personnalisé et bibliothèque, avec sorties `bury()` couvertes | `RenderingEngine` |
| `RenderingEngine::view()` — scripts | Bibliothèques, callbacks formulaire et `onload` initial/post-soumission extraits et couverts | `RenderingEngine` |
| `RenderingEngine::view()` — validation | Générateur des extensions et valeurs CAPTCHA par défaut extraits et couverts | `RenderingEngine` |
| `RenderingEngine::view()` — CAPTCHA | Sélection des scripts `Captcha` / `ReCaptcha` extraite et couverte, ordre historique préservé | `RenderingEngineViewCharacterizationTest` |
| `RenderingEngine::view()` — Query List | Préparation des colonnes, pagination et lignes JavaScript extraite et couverte | `RenderingEngineQueryListPreparationTest` |

## Prochaines étapes

1. Poursuivre le découpage du rendu classique avec les valeurs ContentBuilder,
   puis le HTML de nœud.
2. Après les filets de sécurité, mutualiser les comportements de champs
   QuickMode déjà rendus par les quatre renderers.
3. Traiter PHPCS et PHPStan par groupes de services, sans reformatage massif.

## Vérification minimale par lot

1. Test PHPUnit ciblé du service modifié.
2. `php -l` sur les fichiers PHP modifiés.
3. Suite PHPUnit complète lorsque l'arbre de travail ne contient pas de
   refactorisation concurrente non validée.
4. `git diff --check` avant commit.

## Hors périmètre immédiat

- Réécriture globale de `HTML_facileFormsProcessor`.
- Suppression des façades encore appelées par le PHP stocké en base.
- Changements de comportement du JavaScript historique sans caractérisation.
