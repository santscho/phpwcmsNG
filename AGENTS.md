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
  focus ring, disabled states and sizing, while `theme-primary`, `btn-solid`
  and `btn-styled` are the project's theme hooks for colour and treatment.
- Those hooks live in `include/inc_css/theme.css`, which must be loaded after
  `bootstrap.min.css` (and before any legacy stylesheet that should override
  it). It maps the tokens `--theme-color`, `--theme-color-hover`,
  `--theme-color-active`, `--theme-color-rgb` and `--theme-on-color` onto
  Bootstrap's button variables; colours default to Bootstrap's primary palette,
  so re-colouring means overriding those tokens, not editing button rules.
- Secondary/outline actions use `btn btn-outline-secondary btn-sm`.
- `btn-sm` is the standard size; do not use `btn-lg`.

## Bootstrap skills

All official Bootstrap agent skills are installed in `skills/` (source
`twbs/bootstrap`, installed with `npx skills add twbs/bootstrap`, versions
pinned in `skills-lock.json`). Read the relevant `skills/<name>/SKILL.md`
before starting a task it covers:

- `bootstrap-v6-install` — adding Bootstrap to a project (CDN or package manager)
- `bootstrap-npm`, `bootstrap-vite`, `bootstrap-webpack`, `bootstrap-parcel` — build setups
- `bootstrap-component`, `bootstrap-component-js` — authoring component CSS/markup and JS
- `bootstrap-color-system` — colour tokens, colour modes
- `bootstrap-utility-api` — extending or trimming utilities
- `bootstrap-v5-v6-migration`, `bootstrap-v4-v6-migration` — upgrades

These skills target Bootstrap 6 (6.0.0-alpha.1 at time of writing), while this
project vendors Bootstrap 5.3.9. Use them for upgrade work and forward-looking
patterns; for 5.3 specifics use <https://getbootstrap.com/docs/5.3/> or the
machine-readable docs at <https://getbootstrap.com/llms.txt>.

## Repository layout

- The Git repository root is the fork of phpwcms (`santscho/phpwcmsNG`,
  upstream `slackero/phpwcms`).
- `public/` is the local playground / web root served at
  <http://phpwcmsng.test>. It is gitignored and not part of the repository:
  finished changes are promoted to the repository root before committing.
