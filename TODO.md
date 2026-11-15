# TODO

Open work that is planned but not started. Newest state first; delete an item
when it lands.

## Backend localization

English is the source language (`include/inc_lang/backend/en/`), every other
language falls back to it at runtime, so a missing string is never blank — it
is English. Tooling: `bin/i18n.php` (`check`, `coverage`, `sync`, `prune`),
wrapped by `make i18n-check`, `make i18n-coverage`, `make i18n-sync LANG=xx`
and `make release-check`; `/bin/i18n.php` is un-ignored in `.gitignore` for
that reason.

State as of 2026-11-15 — **12,860 strings untranslated across 25 languages**:

| language | missing | | language | missing |
|---|---|---|---|---|
| bs | 268 | | et / hu | 546 / 546 |
| sl | 269 | | ca | 548 |
| tr | 328 | | pt / sk | 571 / 571 |
| nl | 368 | | ru | 588 |
| fr | 376 | | bg | 591 |
| ro | 453 | | es / it | 655 / 655 |
| gr | 457 | | da / lt / se | 659 each |
| cs / cz | 473 each | | ar | 662 |
| no | 476 | | | |
| vn / fi | 504 / 505 | | | |

`en`, `de` and `pl` are complete. `cz` and `cs` are both Czech.

- [ ] Translate the remaining strings, smallest language first:
      bs, sl → tr → nl → fr → ro, gr → cs/cz, no → vn, fi → the rest.
      Check progress with `make i18n-coverage`, verify with
      `make i18n-check` before every commit.
- [ ] Get the translations reviewed by native speakers before they ship in a
      release — everything added so far was written by the maintainer of this
      backlog, not by translators.
- [ ] Decide how the rest is produced: continue by hand in small batches, or
      bulk machine translation (DeepL / Crowdin / Weblate) with review. 12,860
      strings is beyond what a manual pass covers in a session.
- [ ] Keep each file's existing encoding when translating: HTML entities in
      `ca`, `es`, `fi`, `se`, `sk`, numeric entities in `vn`, plain ASCII in
      `ro` and `bs`, UTF-8 everywhere else. Quoting differs too (`sl` uses
      double quotes, `bs` and `tr` align the `=` column).

### Related gaps that block or complicate the above

- [ ] The file help popup `include/inc_help/filehelp.htm` is static HTML with
      no `$BL` at all, so it stays English in every language. It would have to
      become a PHP template that reads language strings.
- [ ] `bin/i18n.php check` parses definitions with a regex and does not load
      them. The Lithuanian file passed `php -l` and every regex check while
      actually failing at runtime (`'*'` inside a single quoted string parsed
      as multiplication). Add a load test of every language file to `check`.
- [ ] `include/inc_lang/backend/*/lang.ext.inc.php` is a second, separate
      vocabulary (27 keys for `filebrowser.php` / `fileinfo.php`) with its own
      upper-case key scheme. Decide whether to fold it into `lang.inc.php` or
      rename its keys to the `be_*` scheme; today it is easy to forget when
      translating.
- [ ] 26 of the 28 language files define at least one key twice (the later
      definition wins silently). `php bin/i18n.php check` lists them;
      cleaning them up is per-language work.
- [ ] 157 English strings are not referenced by any backend code
      (`php bin/i18n.php prune --lang=en`). Decide whether to delete them, or
      keep the ones that frontend templates or third-party modules may look up.
