# Renderer characterization snapshots

Each `.html` file here is the exact output `ClassicRenderer::process()` (and,
as more get added, the other QuickMode renderers) produces for one field
type + fixture, captured by the tests in the parent directory.

These are **characterization tests**, not a spec: they exist to catch
*accidental* changes while the large QuickMode renderer classes get split up
(see `docs/maintenance/js-libraries-migration-plan.md`), not to declare what
the "correct" HTML is. A snapshot diff during a refactor almost always means
the refactor broke something - but if a snapshot needs to change on purpose
(a deliberate behavior change, a real bug fix), update it explicitly:

```
BF_UPDATE_SNAPSHOTS=1 vendor/bin/phpunit tests/Site/Service/Rendering/QuickMode/
```

then **read the diff** before committing the new snapshot, the same way
you'd review a diff of generated code - never regenerate blindly to make a
red test pass.
