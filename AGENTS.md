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

## Buttons

- Normal/standard buttons use
  `class="btn theme-primary btn-solid btn-styled btn-sm"`.
- Bootstrap's `btn` stays in the list as the base layer: it provides geometry,
  focus ring, disabled states and sizing today, while `theme-primary`,
  `btn-solid` and `btn-styled` are the project's theme hooks for colour and
  treatment. The theme stylesheet must load after `bootstrap.min.css` so its
  rules win.
- Secondary/outline actions use `btn btn-outline-secondary btn-sm`.
- `btn-sm` is the standard size; do not use `btn-lg`.

## Repository layout

- The Git repository root is the fork of phpwcms (`santscho/phpwcmsNG`,
  upstream `slackero/phpwcms`).
- `public/` is the local playground / web root served at
  <http://phpwcmsng.test>. It is gitignored and not part of the repository:
  finished changes are promoted to the repository root before committing.
