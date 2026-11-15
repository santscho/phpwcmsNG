# phpwcmsNG — project rules

## Design: Bootstrap first

- All backend and setup UI is built on **Bootstrap 6**, vendored at
  `include/inc_ext/bootstrap/` (currently 6.0.0-alpha.1: `bootstrap.min.css`,
  `bootstrap.bundle.min.js`, both source maps, MIT licence, `VERSION.txt`).
- Use **stock Bootstrap classes and defaults**: no custom stylesheet, no CSS
  variables or component overrides, no custom colours, fonts, font sizes or
  gradients.
- Do not carry over the legacy table-based layout, sliced images, or the old
  stylesheets (`install.css`, `phpwcms.min.css`, `login.min.css`) when a screen
  is modernized. Replace them, don't layer on top.
- Reference layout for backend screens: `navbar` brand bar (with
  `data-bs-theme="dark"` on a `bg-primary` bar so the white phpwcms logo stays
  legible), then `container` → `row` / `col` → `card` / `card-body`, with the
  standard button classes below.
- The backend frame (`phpwcms.php`) is a primary `navbar sticky-top` carrying
  the main sections plus host and logout, then a fluid container row: sidebar column
  (`col-12 md:col-3 xl:col-2`) next to the content column
  (`col-12 md:col-9 xl:col-10`, keeping the `#be_main_content` id).
- The body is `d-flex flex-column min-vh-100`, the content container carries
  `flex-grow-1` and the footer `mt-auto pb-7`, so the footer rests on the bottom
  of the window while the page is short and follows the content when it is long.
- The version check block (`phpwcmsversionCheck()`) is a `card` with a
  `card-header` title, an `alert theme-success`/`theme-danger` status line and
  `badge theme-success`/`theme-danger` marks on the newer version number; no
  `versioncheck`, `valid` or `error` classes and no separate stylesheet rules.
- The sidebar is a single light panel `bg-1 border rounded overflow-hidden`.
  Inside it the section sub navigation is a `nav` holding a
  `list-group list-group-flush`; entries are `list-group-item
  list-group-item-action` emitted by `subnavtext()`, the current one also gets
  `active` and `aria-current="page"`. Below it a `p-5` block carries the
  backend search as an `input-group` of `form-control` and
  `btn theme-primary btn-solid btn-styled btn-sm`, `hr` separators, `h2.h6`
  section titles and the online users as `small` text. No table rows, image
  bullets or `nav-link` entries.
- Exceptions are possible, but only when deliberately agreed and kept minimal;
  note the reason in the commit message.

## Bootstrap 6 conventions

- **Responsive/state prefixes** are `bp:` prefixed, not infixed:
  `md:col-9`, `md:offset-3`, `md:d-inline`, `md:navbar-expand`,
  `md:p-12`. `xxl` is now `2xl`.
- **Colour variants** are compose-only: `theme-primary`, `theme-secondary`,
  `theme-success`, `theme-danger`, `theme-warning`, `theme-info`,
  `theme-inverse`. There is no `text-bg-*`, no `alert-danger`, no
  `btn-primary`/`btn-outline-primary`.
- **Surfaces/text**: `bg-1`…`bg-10`, `bg-body`, `fg-secondary`, `fg-1`…`fg-10`,
  `fg-contrast-*`. `bg-body-tertiary` and `text-body-secondary` no longer exist.
- **Spacing scale changed**: `p-2`=0.375rem, `p-3`=0.5rem, `p-5`=1rem,
  `p-7`=1.5rem, `p-12`=3rem. Check every padding/margin against the old v5 value
  instead of renaming blindly.
- **Forms**: `.form-control` also styles `<select>`; checkboxes/radios are
  `.check` / `.radio` on the input inside a `.form-field` grid wrapper;
  validation is `data-bs-validate` (no `was-validated`).
- **JavaScript is ESM-only**: load the bundle with
  `<script type="module" src=".../bootstrap.bundle.min.js">`; there is no
  `window.bootstrap` global.
- Component renames: modal → **dialog** (native `<dialog>`), offcanvas →
  **drawer**, dropdown → **menu**, collapse/accordion → native `<details>`.
- Bootstrap 6 wraps its CSS in `@layer`. Unlayered author CSS (e.g.
  `phpwcms.min.css`) always wins over it — keep that in mind before wondering
  why a v6 rule is not applied.

## Buttons

- Normal/standard buttons use
  `class="btn theme-primary btn-solid btn-styled btn-sm"`.
- These are native Bootstrap 6 APIs: `theme-*` sets the colour tokens,
  `btn-solid` fills, `btn-styled` adds the gradient/shadow treatment, `btn`
  supplies base geometry, `btn-sm` the size. No project stylesheet is involved.
- Secondary/outline actions use `class="btn btn-outline theme-secondary btn-sm"`.
- `btn-sm` is the standard size; do not use `btn-lg`.

## Screen mode

- The backend header carries a screen mode switcher on the right: three
  `btn btn-icon` buttons (Auto, Light, Dark) with inline SVG icons from
  `backend_theme_icon()`; the selected one is `btn-solid`, the others
  `btn-outline`, plus `aria-pressed`.
- Auto removes `data-bs-theme` from `<html>` so Bootstrap follows the operating
  system, Light and Dark set `data-bs-theme="light"`/`"dark"` there.
- The choice is stored per user in `phpwcms_user.usr_theme`
  (`varchar(10)`, default `auto`, revision 556) and loaded into
  `$_SESSION['wcs_user_theme']` at login; switching posts to
  `include/inc_act/ajax_connector.php` with action `backend_theme` and the
  `csrftoken`.
- Keep the navigation bar dark and theme independent: it stays
  `bg-primary` with `data-bs-theme="dark"` so the white logo works in both
  modes.

## Graphics and icons

- No raster (pixel) graphics for interface chrome. Status marks, arrows,
  separators, buttons and badges are built from Bootstrap components (`badge`,
  `alert`, `btn`) or inline SVG icons; the phpwcms logo is vector SVG and stays.
- Status is never communicated by an image: use
  `<span class="badge theme-success">OK</span>` / `theme-danger` with a text
  label, and `alert theme-*` for messages.
- When a screen is modernized, its pixel assets are deleted from the repository
  as soon as nothing references them. Search the whole repo for the file name
  first — some paths are assembled from variables (`'img/famfamfam/lang/'.$lang`).
- Do not add an icon font or icon package without agreement; Bootstrap
  components are the first choice, inline SVG the second.

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

The project now runs the same major version these skills target
(6.0.0-alpha.1). Reference docs: <https://getbootstrap.com/docs/6.0/> and
<https://getbootstrap.com/llms.txt>.

## Repository layout

- The Git repository root is the fork of phpwcms (`santscho/phpwcmsNG`,
  upstream `slackero/phpwcms`).
- `public/` is the local playground / web root served at
  <http://phpwcmsng.test>. It is gitignored and holds local runtime state
  (config, install values, uploads).
- **Nothing is ever copied from `public/` into the repository.** The core tree is
  the source of truth: make every edit there, and copy core → `public/` only when
  the playground needs the current code to render. `public/` is a throwaway
  mirror plus local state, never an input to a commit.
- Before every commit check `git status --short` and `git diff --cached` for
  credential-looking lines (`db_pass`, `admin_pass`, `SMTP_CLIENT_SECRET`, …) and
  for paths under `public/`.
