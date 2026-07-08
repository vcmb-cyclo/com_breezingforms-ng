# BreezingForms NG Form Mode

This Joomla 6 / PHP 8.1+ codebase keeps only one form creation mode:
QuickMode.

## Historical Context

Older BreezingForms documentation describes three editors: QuickMode, EasyMode,
and ClassicMode. Those sources are useful only as historical context:

- Rheinwerk Openbook:
  https://web.archive.org/web/20210507062113/https://openbook.rheinwerk-verlag.de/joomla15/joomla_18_formulare_neu_001.htm
- Crosstec documentation map:
  https://web.archive.org/web/20251013050040/https://crosstec.org/en/support/online-documentation/breezingforms.html

See `docs/maintenance/breezingforms-upstream-docs.md` for extracted upstream
notes. Do not treat older EasyMode or ClassicMode documentation as current
implementation guidance.

## Supported Mode

| Mode | Status | Storage marker | Main admin entry points |
| --- | --- | --- | --- |
| QuickMode | Supported | `template_code_processed = 'QuickMode'` | `act=manageforms&task=quickmode`, `act=quickmode` |

Forms whose persisted marker is not `QuickMode` are not supported by this port.
Frontend rendering now stops with a translated warning for those legacy form
formats.

## Removed Modes

EasyMode and ClassicMode editor code has been removed from the admin component.

Removed EasyMode files:

- `administrator/components/com_breezingformsng/admin/easymode.php`
- `administrator/components/com_breezingformsng/admin/easymode.html.php`
- `administrator/components/com_breezingformsng/admin/easymode.class.php`
- `administrator/components/com_breezingformsng/admin/easymode-js.php`
- `administrator/components/com_breezingformsng/libraries/jquery/themes/easymode/easymode.all.css`

Removed ClassicMode editor files:

- `administrator/components/com_breezingformsng/admin/element.php`
- `administrator/components/com_breezingformsng/admin/element.class.php`
- `administrator/components/com_breezingformsng/admin/element.html.php`

Removed routes:

```text
/administrator/index.php?option=com_breezingformsng&act=easymode&form=FORM_ID
/administrator/index.php?option=com_breezingformsng&act=editpage&form=FORM_ID&page=1
/administrator/index.php?option=com_breezingformsng&act=manageforms&task=editpage1&ids[]=FORM_ID
```

## Current Admin Routing

Known QuickMode URLs:

```text
/administrator/index.php?option=com_breezingformsng&act=manageforms&task=quickmode&form=FORM_ID
/administrator/index.php?option=com_breezingformsng&act=quickmode&form=FORM_ID
```

QuickMode inline editor:

```text
/administrator/index.php?option=com_breezingformsng&tmpl=component&act=quickmode_editor
```

The forms list and post-save redirects should point to QuickMode, not to the
old `editpage` element editor.

## Important Files

- `administrator/components/com_breezingformsng/admin/quickmode.php`
- `administrator/components/com_breezingformsng/admin/quickmode.html.php`
- `administrator/components/com_breezingformsng/admin/quickmode.class.php`
- `administrator/components/com_breezingformsng/admin/quickmode-app.js`
- `administrator/components/com_breezingformsng/admin/quickmode-elements.js`
- `administrator/components/com_breezingformsng/admin/quickmode-editor.php`
- `administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickMode.php`
- `administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeBootstrap.php`
- `administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeOnePage.php`
- `administrator/components/com_breezingformsng/libraries/crosstec/classes/BFQuickModeMobile.php`
- `components/com_breezingformsng/facileforms.process.php`
- `components/com_breezingformsng/breezingformsng.php`

## Maintenance Rules

- New or edited forms must use QuickMode.
- Do not reintroduce `act=easymode` or `act=editpage`.
- Do not change `template_code_processed` without migrating the matching
  `template_code`, `template_areas`, elements, scripts, and generated IDs.
- Preserve the QuickMode-specific copy/import ID reset path.
- If legacy EasyMode or ClassicMode data must be migrated, create an explicit
  migration into QuickMode instead of restoring the old editors.
- Keep new maintenance work aligned with Joomla 6 APIs and PHP 8.1+ only.

## Useful Searches

```sh
rg -n "template_code_processed|QuickMode|QuickModeForms|task=quickmode" administrator components
```

```sh
rg -n "act=easymode|act=editpage|case 'easymode'|case 'editpage'|EasyMode|ClassicMode" administrator components
```
