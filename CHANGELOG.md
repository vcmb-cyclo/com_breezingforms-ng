# Changelog

## Unreleased

- Switched BreezingForms integration from legacy `com_contentbuilder` to `com_contentbuilderng`.
- Updated BF site/admin flows to use ContentBuilder NG services for permissions, form resolution, record sync, article creation, and redirects.
- Changed BF direct access behavior so linked CBNG views are validated against the new CBNG ACL flow.
- Added the optional BFCompat system plugin for historical `BFFactory`, `BFIntegrate`, `BFJoomlaConfig`,
  `BFPDF`, `BFRequest`, and `BFText` APIs used by third-party plugins and PHP stored in the database.
- Kept the required BFQuickMode public classes in the component while switching the component runtime to
  its Joomla 6 namespaced renderers and integration service.
- Removed the obsolete sysbreezingforms plugin; its disabled licence check and Joomla 3 menu-markup cleanup
  are no longer used by the Joomla 6 component manifest.
- Switched administrator controllers to the application supplied by Joomla's MVC base controller and moved
  database-backed administrator models to `BaseDatabaseModel`/`getDatabase()`.
- Switched database models to Joomla's injected current-user and event-dispatcher services.
- Removed the unused legacy Dropbox configuration containing obsolete embedded application credentials.
- Migrated reCAPTCHA, Dropbox, Mailchimp, and Salesforce HTTP clients from the Joomla CMS compatibility
  wrapper to the native `Joomla\\Http` package required by Joomla 6.
- Removed direct mutation of Joomla document internals, replaced legacy Bootstrap/calendar file loading with
  Joomla 6 web assets, and migrated remaining local runtime script tags to WebAssetManager.
- Removed all embedded jQuery copies and migrated frontend and administrator rendering to Joomla 6's native
  jQuery web asset.
- Migrated script and piece source submissions from direct request-body and superglobal access to Joomla 6
  Input with explicit raw filtering, and standardized their state-changing actions on POST CSRF validation.
- Removed the unused legacy form-route helper and renamed the shared script/piece list model to its Joomla 6
  package-model role.
- Migrated record exports from direct PHP response headers and global database lookup to the Joomla application
  response API and the database connection supplied by the MVC model.
- Migrated QuickMode chunk-save status and completion responses from direct PHP response handling to Joomla's
  application response API.
- Added the sortable form ID as the first data column in the Joomla administrator forms list.
- Unified the records-list modified-date heading with the forms-list wording.
- Migrated CAPTCHA status, Stripe checkout redirects, and paid-file download headers to Joomla 6's application
  response API while retaining streamed file delivery.
- Normalized integer and nullable relation fields when copying forms so strict Joomla 6 database configurations
  no longer receive empty strings for integer columns.
- Aligned every sortable Forms, Records, Scripts, and Pieces heading with ContentBuilder NG by using Joomla 6
  SearchTools icons and accessible list-view sorting behavior; non-sortable columns remain icon-free.
- Removed the Pieces “show internal functions” control, session state, and underscore-prefix query filtering;
  all pieces are now listed consistently.
- Added contextual Integrator help and Joomla 6 SearchTools sorting for every sortable rule-list column.
- Restored the compact, panelled presentation of the advanced form settings while retaining the Joomla 6
  tabs and form controls.
- Removed the obsolete standalone QuickMode mobile document reconstruction and routed its assets through the
  Joomla 6 document normally; migrated adjacent request and session state access to Joomla Input and Session.
- Migrated PayPal request data, Flash uploader files, and the remaining VirtueMart bridge state from PHP
  superglobals to Joomla Input and Session; made Flash upload-size validation an encapsulated service method.
- Replaced PayPal IPN's direct cURL/socket transport and disabled TLS verification with an injectable
  `Joomla\\Http` client using the platform's secure transport configuration; the PayPal waiting callback no
  longer emits a second standalone XHTML document inside Joomla's response.
- Routed regular form upload metadata through Joomla's files Input, removing the final runtime dependency on
  PHP's request, session, GET, POST, and FILES superglobals.
- Replaced the bundled Securimage CAPTCHA library with the maintained adythree/securimage 4.0.4 fork,
  moved its PHP runtime out of public media, and added Google reCAPTCHA to About.
