# phpwcmsNG — project rules

## Design: Bootstrap first

- All backend and setup UI is built on **Bootstrap 5**, vendored at
  `include/inc_ext/bootstrap/` (currently 5.3.9: `bootstrap.min.css`,
  `bootstrap.bundle.min.js`, source map, licence).
- Use **stock Bootstrap classes and defaults**: no custom stylesheet, no CSS
  variables or component overrides, no custom colours, fonts, font sizes or
  gradients.
- Do not carry over the legacy table-based layout, sliced images, or the old
  stylesheets (`install.css`, `phpwcms.min.css`, `login.min.css`) when a screen
  is modernized. Replace them, don't layer on top.
- Reference layout for backend screens: `navbar` brand bar (with
  `data-bs-theme="dark"` on a `bg-primary` bar so the white phpwcms logo stays
  legible), then `container` → `row` / `col` → `card` / `card-body`, with
  `btn btn-primary` for actions.
- Exceptions are possible, but only when deliberately agreed and kept minimal;
  note the reason in the commit message.

## Repository layout

- The Git repository root is the fork of phpwcms (`santscho/phpwcmsNG`,
  upstream `slackero/phpwcms`).
- `public/` is the local playground / web root served at
  <http://phpwcmsng.test>. It is gitignored and not part of the repository:
  finished changes are promoted to the repository root before committing.
