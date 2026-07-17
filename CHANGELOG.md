# Changelog

## Unreleased

- Switched BreezingForms integration from legacy `com_contentbuilder` to `com_contentbuilderng`.
- Updated BF site/admin flows to use ContentBuilder NG services for permissions, form resolution, record sync, article creation, and redirects.
- Changed BF direct access behavior so linked CBNG views are validated against the new CBNG ACL flow.
- Restored `BFFactory` (removed in Phase 8 as an apparently-unused legacy wrapper): production Pieces
  (`ff_databaseToSelect`, `ff_query`, `ff_select`, `ff_selectValue`) and the `hash_password` form's "Before Form"
  code — all PHP stored in the database, not in this source tree — still call `BFFactory::getDbo()`/`getDBO()`.
  Removing it broke those on the live site (`Class "BFFactory" not found`) until restored.
  **Known blind spot, not yet fully audited**: `BFText`, `BFJoomlaConfig`, and `BFPDF` were removed in the same
  Phase 8 pass on the same "no caller found" basis. A check of this dev site's stored Pieces/form `piece*code`
  columns found no reference to those three, so no action was taken — but that check only covers this one
  site's data, not every installed site. If a `Class "BFXxx" not found"` error is ever reported for one of these
  three (or any other removed legacy class), assume it's the same root cause: custom PHP stored in
  `#__facileforms_pieces.code` or `#__facileforms_forms.piece*code`, evaluated at runtime, referencing a class
  the source-tree migration deleted. A `grep` across the PHP source can never rule this out by itself — the
  database must be checked too.
