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
- Replaced the bundled Securimage CAPTCHA library with the maintained adythree/securimage 4.0.4 fork,
  moved its PHP runtime out of public media, and added Google reCAPTCHA to About.
