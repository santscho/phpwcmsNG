# phpwcmsNG — project rules

## General revamp rules

These apply to every backend screen that is modernized:

1. **Keep the functions.** PHP actions, query strings, form fields, hidden
   inputs, CSRF tokens and POST/GET semantics stay exactly as they are. A revamp
   changes markup and styling, never behaviour.
2. **Clear the old UI formatting.** Remove the legacy table layout, sliced
   images, spacer gifs, inline `width`/`height`/`align`/`bgcolor`/`style`
   attributes and the old stylesheet rules the screen relied on instead of
   layering Bootstrap on top of them.
3. **Rebuild the display with default Bootstrap components** — `card`,
   `list-group`, `navbar`, `nav`, `table`, `alert`, `badge`, `input-group`, the
   stock utilities and the button classes below. No custom CSS, colours, fonts
   or font sizes.
4. **Replace pixel graphics with Bootstrap Icons** inlined by `backend_icon()` —
   action buttons, arrows, handles, status marks. No raster chrome is added.
5. **Register every new icon.** When an icon is introduced, add its function and
   the Bootstrap Icon name to the *Backend icon set* list below in the same
   change, so the set stays documented and no two screens pick different glyphs
   for the same action.
6. **Backend date and time fields use the picker.** Never a native
   `datetime-local`, never the legacy `dynCalendar` gif, never a free text field:
   the field is an `input[data-datepicker]` driven by
   `include/inc_js/backend.datepicker.js`, it shows the date and time format the
   logged in user picked in the profile and its calendar starts on that user's
   week start day. Dates a screen only renders use the same format — see
   *Date and time format*.

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
- The backend frame (`phpwcms.php`) is a primary `navbar sticky-top`; the main
  sections are a base Bootstrap `nav` (`nav-link` anchors, `active` plus
  `aria-current="page"` on the current one, no `nav-pills`) and the theme switcher, host and
  logout sit on the right. The unread message count is a `badge theme-warning`
  inside the messages pill. Below it a fluid container row: sidebar column
  (`col-12 md:col-3 xl:col-2`) next to the content column
  (`col-12 md:col-9 xl:col-10`, keeping the `#be_main_content` id).
- The body is `d-flex flex-column min-vh-100`, the content container carries
  `flex-grow-1` and the footer `mt-auto pb-7`, so the footer rests on the bottom
  of the window while the page is short and follows the content when it is long.
- The version check block (`phpwcmsversionCheck()`) is a `card` with a
  `card-header` title, an `alert theme-success`/`theme-danger` status line and
  `badge theme-success`/`theme-danger` marks on the newer version number; no
  `versioncheck`, `valid` or `error` classes and no separate stylesheet rules.
- The sidebar is a full height column: the content area is a
  `container-fluid d-flex flex-column flex-grow-1 p-0` holding a
  `row g-0 flex-grow-1`, so the `aside` (`col-12 md:col-3 xl:col-2`) runs from
  the header down to the footer. The column carries the light `bg-2` surface and
  `md:border-end` as its only divider from the content column, which keeps its
  `p-5` padding and `id="be_main_content"`.
- Everything in the sidebar is a card, stacked with `mb-5` and no `hr`
  separators between the blocks: the section sub navigation (a `nav` with
  `list-group list-group-flush`, `list-group-item list-group-item-action` rows
  from `subnavtext()`, current one `active` plus `aria-current="page"`, rendered
  inside `card overflow-hidden` and omitted entirely on HOME), the search card
  (`card-header` title, `card-body` with the `input-group` of `form-control` and
  `btn theme-primary btn-solid btn-styled btn-sm`) and the users online card
  (`card-header` title, `list-group list-group-flush` of `list-group-item`
  rows). No table rows, image bullets, `nav-link` entries or `small` text
  scratch lists.
- The sidebar search field is an `input type="search"`, and the term is kept in
  `$_SESSION['phpwcms_backend_search']` because the HOME listing filters by it.
  Emptying the field with its clear button (or by deleting the term) posts the
  `backend_search_clear` action to `include/inc_act/ajax_connector.php`, which
  resets the session value, so a cleared term never reappears after navigating to
  another section. Enter on an emptied field does the same instead of submitting
  an empty search.
- The clear action calls `session_write_close()` before it answers, and the HOME
  card is loaded again afterwards with `location.replace()` on the current URL:
  the session has to be written before that request runs, and reloading the
  search result page would repeat its POST instead of reading the new state.
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
  why a v6 rule is not applied. The old px based `.mb-2`…`.mb-5` and
  `.mt-2`…`.mt-5` helpers were deleted for that reason: they shadowed the v6
  margin utilities (`mb-5` meant 5px instead of 1rem).

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
  components are the first choice, inline SVG the second. Backend row actions
  are rendered with `backend_action()` (outline `btn-sm` icon button, plus the
  `active` and `disabled` states) and `backend_icon()` from
  `include/inc_lib/backend.functions.inc.php` instead of pixel gifs.

## Tooltips

- Every backend tooltip is a Bootstrap tooltip: `data-bs-toggle="tooltip"` next to
  the `title` that carries the text. Do not leave a bare `title` on a link,
  button, toggle, badge or other chrome - it renders the browser's own tooltip
  instead of a Bootstrap one.
- Bootstrap tooltips are opt-in, so `include/inc_js/backend.bootstrap.js` (an ES
  module the backend frame loads next to the Bootstrap bundle) initialises every
  `[data-bs-toggle="tooltip"]` element. A screen only needs the attributes.
- That module also shims `window.Event`: MooTools 1.1 replaces the constructor
  with its event wrapper and Bootstrap dispatches custom events with the native
  `new Event('name', {...})` signature, so without the shim every Bootstrap
  component that fires an event throws. Keep the shim when touching the module.
- `backend_action()` and `backend_sort_handle()` emit both attributes. A disabled
  icon button ignores the pointer, so `backend_action()` puts the tooltip on the
  `d-inline-flex` wrapper it renders around the disabled button.
- Legacy screens that still use the old table layout and gif buttons keep their
  plain `title` attributes until they are revamped.

## Backend icon set

Every backend icon is a Bootstrap Icon (<https://icons.getbootstrap.com>, v1.13.2,
MIT) inlined at 16x16 from `backend_icon()`. The names are fixed:

- **type markers**: structure level `grid-1x2-fill`, article
  `journal-bookmark-fill`, content part `postcard-fill` — shown before the row
  title. Structure levels are the site structure categories in the article tree
  and on `ADMIN > site structure` (`struct_levellist()`), including the root row
  of both trees in `article.structlist.tmpl.php` and
  `admin.structlist.tmpl.php`, so they never use the `folder-fill` of the file
  center directories
- **row actions**: edit `pen`, cut `scissors` (solid `theme-warning` while the file
  sits in the clipboard), copy `copy`, paste `clipboard-plus`, delete `trash`,
  restore from trash `restore` (Bootstrap icon `arrow-counterclockwise`)
- **visibility switch**: `eye` in `theme-success` while the entry is visible,
  `eye-slash` in `theme-danger` while it is hidden
- **tree and add**: `chevron-right`, `chevron-down`, `plus`
- **file manager actions**: upload `upload`, download `download`, new directory
  `folder-plus`, help `question-circle`, expand all `chevron-bar-expand`,
  collapse all `chevron-bar-contract`, thumbnails off `image` / on `image-fill`
- **directory types**: directory `folder-fill`, gallery root directory `images`,
  gallery sub directory `image`
- **state switches** (files and directories alike): active `toggle-on` as a
  solid `theme-success` button, inactive `toggle-off` as an outline
  `theme-secondary` one; public `globe` and private `lock` follow the same rule
- **public files**: file owner `person-fill`, shown before the owner name of
  each group in the public file listing
- **file types** (`backend_file_icon()` maps an extension to one of these):
  image `file-earmark-image`, pdf `file-earmark-pdf`, word `file-earmark-word`,
  excel `file-earmark-excel`, archive `file-earmark-zip`, text `file-earmark-text`,
  audio `file-earmark-music`, video `file-earmark-play`, anything else
  `file-earmark`

Do not hand-draw icons or pick replacement glyphs; add the Bootstrap Icon path
to `backend_icon()` if a new one is needed.

A new icon is only half the job: extend this list in the same change with the
function it serves and the icon name (see revamp rule 5). Check whether an
existing entry already covers the action before adding a second glyph for it.

## Sorting lists

- Reorderable backend lists use plain HTML5 drag and drop from
  `include/inc_js/backend.sortable.js` — no jQuery and no third-party sorter.
- The wrapper needs `data-backend-sortable`, `data-backend-sort-token` and
  `data-backend-sort-error`; the rows need `backend_sort_handle()` as the only
  drag source plus `data-sort-group`, `data-sort-id` and `data-sort-parent`
  (content parts also carry `data-sort-block`, they sort inside their block).
- The drop is saved by the `backend_sort` action in
  `include/inc_act/ajax_connector.php`, which checks the CSRF token, the group
  and that the submitted ids are exactly the sibling set, then rewrites the
  sort values in steps of 10.
- Sortable trees are `list-group list-group-flush` lists of `list-group-item`
  rows, never tables; indentation comes from `backend_indent()` (mapped `ms-*`
  classes) and row actions sit in a `d-flex gap-2` group of `btn-icon`
  buttons, matching the screen mode buttons in the header.

## File center

- `FILE` keeps its folder tabs as a Bootstrap `nav nav-pills` at the top of the
  content column in `include/inc_tmpl/files.reiter.tmpl.php`, without a card
  around it: `nav-link` anchors, the current folder `active` plus
  `aria-current="page"`, and the folder actions as `btn-icon` buttons on the
  right of the same row.
- The private listing is a `card` with a `list-group list-group-flush`; directory
  and file rows are `list-group-item` rows of the same shape as the article tree
  (open/close chevron, type icon, name, actions). Opening a directory nests a
  further `list-group list-group-flush ms-5` inside the current list instead of
  indenting with spacers, so any depth stays a plain nested list.
- Every action is a `backend_action()` icon button: upload, new directory or
  paste, edit, the two state switches and delete; a non-empty directory gets the
  disabled trash button with the "not empty" title. File rows also carry
  download, cut and the file information editor.
- The public listing (`files.public.list.tmpl.php` with the helpers in
  `files.public-functions.inc.php`) is the same pattern, grouped per owner: a
  `person-fill` row per owner with its `pklapp=u<id>` chevron, and the owner's
  directories and files in a nested `list-group list-group-flush ms-5`. Directory
  rows carry `folder-fill` and the `pklapp=<id>` chevron, file rows the
  `backend_file_icon()` mark, name, `EXT &middot; size` meta, the thumbnail and a
  download button that keeps `pl=1`. The name opens `fileinfo.php?public&fid=<id>`
  in the `filedetail` popup, as before. `all=open` fills `pklapp` with every owner
  and public directory, `all=close` clears it.
- Thumbnails stay available through the header toggle and render inside the row,
  but only for image extensions.
- `files.abschluss.tmpl.php` and `files.private-filelist.inc.php` are gone: the
  closing bar was decoration and the root files are listed by
  `list_private_files()` now.
- The file help popup (`include/inc_help/filehelp.htm`, opened from the header
  question button) is a static page: Bootstrap CSS from `include/inc_ext`, one
  `card` with a `list-group` of legend rows. Each row repeats the icon the file
  list uses, inlined as a `<symbol>` sprite at the top of the body and rendered
  through `<use>` inside an inert `btn btn-icon` span, so outline buttons, solid
  active marks and the disabled trash match the real toolbar. A small script
  copies the opener's `data-bs-theme` so the popup follows the screen mode.
- The multiple upload screen (`files.ftptakeover.tmpl.php`) is cards too: a drop
  zone with a native multiple `form-control` file input, the files waiting in the
  FTP directory as a list group with the total size in a badge, then the file
  information split by a language `nav nav-pills` header with one `card-body`
  pane per language. `include/inc_js/backend.upload.js` sends one request per file
  to `include/inc_act/act_upload.php` under the field name `qqfile` and reloads
  the listing afterwards. The qq FileUploader widget is not used here anymore,
  but `filebrowser.php` still loads it, so its files stay.

## File search and trash

- `FILE > file center > search` is two cards: the search form (title header, the
  hint as `small fg-secondary`, the `form-control` search field with `autofocus`,
  the AND/OR and scope selects side by side, `start search` as the primary button
  in the `card-footer`) and, once a search ran, a results card whose header shows
  the hit count in a `badge theme-secondary` and whose body is a
  `list-group list-group-flush` of the same file rows as the listings. A missing
  search string is an `alert theme-danger`, an empty result an
  `alert theme-warning`.
- Search result rows link the name to the file editor (`&editfile=`) when the user
  owns the file or is admin, and to the `fileinfo.php?public&fid=` popup otherwise;
  the edit button only appears in the first case, the download button always.
- `FILE > file center > trash can` (`files.private.trash.tmpl.php`) is a card with
  the count in its header, the trashed files from
  `files.private-delfilelist.inc.php` as rows with `restore` and `trash` buttons
  (both keep their confirm questions, the final delete is outline
  `theme-danger`), and `delete all files in trash` as a solid `theme-danger`
  button in the footer. An empty trash is a `card-body` with the
  `show private files` button.

## Private file upload

- `FILE > file center > private files > upload file`
  (`include/inc_tmpl/files.private.upload.tmpl.php`) is a single card: the header
  carries the screen title and a `close` button back to the file center, the body
  the form, the footer the `close` and primary submit buttons.
- The body is a `row g-4` of `col-12 md:col-6` fields (directory select and file
  input first), followed by `border-top mt-4 pt-4` groups for IPTC data, the
  language tabs, the keywords and the status marks. Upload errors are an
  `alert theme-danger`, a missing required keyword category is a
  `badge theme-danger` next to its label.
- The language switch is a `nav nav-pills` inside `file-lang-tabs`. Every tab keeps
  its `rel`, every field block is `finfo finfo<lang>` and the inactive ones carry
  `d-none`, which the jQuery handler toggles. Keep the bare `finfo` class — the tab
  handler selects `.file-lang-tabs .nav-link`, not a `nav` element.
- IPTC and the four status marks are `.form-field` with a `.check` input. The tag
  autosuggest, the `autosize` textareas and the `file_aktion`/`MAX_FILE_SIZE`
  hidden fields stay as they are.

## Private new directory

- `FILE > file center > private files > create new dir`
  (`include/inc_tmpl/files.private.newdir.tmpl.php`) follows the upload screen: a
  single card with the title and a `close` button in the header, the form in the
  body and `close` plus the primary `create new dir` button in the footer.
- The parent directory is shown as a `form-label` over its name (the root
  directory when nothing was selected), then a `row g-4` of `col-12 md:col-6`
  fields: name and gallery select, the full width long info textarea, sorting and
  the two status marks as `.form-field`/`.check` pairs under a `status` label.
- A missing name is reported as an `alert theme-danger` above the fields; the
  `dir_pid` and `dir_aktion` hidden fields live in the footer with the buttons.

## File actions

- `FILE > file actions` (`include/inc_tmpl/files.actions.tmpl.php`) is three
  stacked cards: the folder choice (`folderform`, still posting on change), the
  file list and the action panel. The screen title is the header of the first
  card, the step labels are the headers of the other two.
- The files of the selected folder are a `list-group list-group-flush` of
  `list-group-item` rows: `.check` checkbox, the file type icon from
  `backend_file_icon()`, the name as the row label, then icon-only `badge` state
  marks — `toggle-on` `theme-success`/`toggle-off` `theme-secondary` for active and
  `globe` `theme-success`/`lock` `theme-secondary` for public. The select-all
  checkbox sits in the `card-footer`.
- The action panel is a `form-control` select on `file_action`; the folder, status
  and owner fields plus the submit button are cells that `showAction()` reveals by
  toggling `d-none` — vanilla JS, no MooTools. The result message is an
  `alert theme-success` (or `theme-danger` when nothing was marked).

## Article new and edit

- `ARTICLE > new article` (`phpwcms.php?do=articles&p=1&struct=0`) and the article
  edit screen are the same template,
  `include/inc_tmpl/article.editsummary.tmpl.php`, loaded by
  `include/inc_lib/article.editcontent.inc.php`. It is one form (`name="article"
  id="article"`) of five stacked cards in a `row g-4`, every field in a
  `col-12 md:col-6` cell (a few `md:col-3` for the short numeric ones).
- Card headers are `h2.h6 mb-0`; the two image cards add the purpose as a
  `badge theme-secondary` (`article detail`, `article listing`) on the right of a
  `d-flex flex-column md:flex-row` header instead of a second heading. `template`,
  `cache` and `search index` are `h3.h6 mb-4` groups separated by
  `border-top mt-4 pt-4`, not nested cards.
- The image cards are `form-label` + `form-control` + `d-flex flex-wrap gap-2`: the
  read-only file name field (`onfocus="this.blur()"` stays), the image browser as
  a text button — `btn btn-outline theme-secondary btn-sm` labelled
  `be_cnt_openimagebrowser`, there is no local Bootstrap Icons source for a
  `card-image` glyph — and the delete action as the existing `trash` icon from
  `backend_action(..., 'danger')`. Max width/height sit in a nested `row g-3`,
  enlarge/gallery are `.form-field`/`.check` pairs.
- The language switch stays a set of `.radio` inputs: `input.lang-default` plus
  one `input.lang-opt` per frontend language from `code.lang.inc.php`. Keep those
  classes,   `#lang-id-select` unchanged. The alias row above the fields is three `link
  small` anchors: `article alias` calls `set_article_alias()`, and
  `#parent_alias`/`#struct_alias` carry the alias in `data-alias` plus a
  Bootstrap tooltip, which the inline jQuery handler turns into
  `#article_alias`.
- The article owner `<select name="article_uid">` marks admin users by appending
  `($BL['be_article_adminuser'])` to the option label, sorted admins first; the
  old inline `style="background-color:#FFC299"` is gone.
- The begin and end fields are flatpickr date and time pickers
  (`article_begin`/`article_end`, `input[data-datepicker]`), not the
  `dynCalendar` widget: nothing on this screen uses `img/dynCal/` any more. The
  field keeps the canonical `Y-m-d H:i:s` value phpwcms stores
  (`$article_begin_value`/`$article_end_value`, guarded with `strtotime()` so an
  unset value stays empty) and only the visible twin shows the date and time
  format from the user's profile, and the calendar starts on that user's week
  start day, see *Date and time format*. The submitted value never depends on the
  display, so `article.editcontent.inc.php` is untouched. The `last edit` note in
  the card header uses the same format.
- `article_date_changed()` holds the two rules the callbacks used to carry:
  picking a date marks its own boundary active (`set_begin`/`set_end`), and a
  begin after the end clears that end, compared by date only as before. The
  `set_begin`/`set_end` checkboxes still fill the field with the stored value in
  ISO form when ticked.
- `initJsCalendar()` still loads `dynCalendar.js` for the article frame
  (`phpwcms.php`) because news, ads and the calendar module keep the widget. The
  content part editor does not: it asks for `initJsDatePicker()` itself, see
  *Content part editor dates*.
- The footer is one `card-footer d-flex flex-wrap gap-2 justify-content-end` with
  the submit inputs: `updatesubmit` (solid primary, `Update` when
  `$article["article_id"]` is set, `Create` otherwise) and `Submit`
  (`Save & close`); both keep their names because
  `article.editcontent.inc.php` tests `updatesubmit` to decide the action. The
  cancel link closes the screen as before.
- Keep `document.article.*` working: the summary editor, the keyword autosuggest
  (`#article_keyword_autosuggest`, `#as-values-keyword-autosuggest`) and the
  inline `onchange`/`onclick` handlers reference form elements by name.

## Date and time format

- Every backend date and time field is a flatpickr picker that displays the
  logged in user's format (revamp rule 6). Do not add a native `type="date"` or
  `datetime-local` control, a readable-format text field or the `dynCalendar`
  widget to a screen that is being revamped; convert the field instead.
- Every user picks the backend date and time format in the profile
  (`PROFILE > account`, `$BL['be_profile_label_dateformat']`). It is stored in
  `phpwcms_user.usr_date_format` (revision 557, `iso` by default) and loaded into
  `$_SESSION['wcs_user_date_format']` at login; `phpwcms.php` turns it into
  `$backend_date_format` through `backend_date_format()`.
- Directly below it the user picks the first day of the week for the calendars
  (`$BL['be_profile_label_weekstart']`, Monday or Sunday). It is stored in
  `phpwcms_user.usr_weekstart` (revision 558, `1` = Monday by default) and loaded
  into `$_SESSION['wcs_user_weekstart']` at login; `phpwcms.php` turns it into
  `$backend_weekstart` through `backend_weekstart()`. The stored value is the one
  flatpickr expects for `firstDayOfWeek`: `1` is Monday, `0` is Sunday.
  `backend_weekstarts()` is the list, its keys are the stored values and its
  values extend to the `$BL['be_profile_weekstart_monday']`/
  `$BL['be_profile_weekstart_sunday']` labels.
- Dates that are only displayed follow the same preference: read the stored
  value or timestamp from the query and render it with
  `date($backend_date_format['php'], $time)`, guarding `strtotime()` so an empty
  or unparsable value stays as it is. Do not format in SQL with `DATE_FORMAT()`
  and do not use `$BL['be_longdatetime']` or `$BL['be_shortdatetime']`. Converted
  that way: the article summary header, HOME's two *last edited* cards
  (`include/inc_tmpl/be_start.tmpl.php`) and the article information panel with
  its content part list (`include/inc_tmpl/articlecontent.list.tmpl.php`: last
  edit, created, start and end date, each content part's timestamp and its live
  and kill dates).
- `window.phpwcmsDatePickerSet(inputOrId, canonicalValue)` is the module's
  setter for other inline handlers: it writes the canonical value through the
  picker instance (so the visible twin follows) and dispatches `change`;
  an empty value clears the field. Use it instead of touching `.value`.
- `backend_date_formats()` in `include/inc_lib/backend.functions.inc.php` is the
  single list: `iso` (`2026-11-15 19:07:02`), `swiss` (`15.11.2026 19:07:02`),
  `uk` (`15/11/2026 19:07:02`) and `us` (`11/15/2026 7:07:02 PM`). Each entry
  carries the PHP pattern (`php`), the flatpickr tokens (`flatpickr`),
  `time_24hr` and a `sample`, and the sample is the `option` label of the profile
  select, so a new format needs no language string.
- The picker is flatpickr, vendored at `include/inc_ext/flatpickr/` (4.6.13, MIT:
  `flatpickr.min.js`, `flatpickr.min.css`, `LICENSE`). `initJsDatePicker()`
  registers the stylesheet, the library and the ES module
  `include/inc_js/backend.datepicker.js`; the article screens load it next to
  `initJsCalendar()`.
- The native `datetime-local` control is deliberately not used: its face is fixed
  by the browser or operating system locale (Safari takes it from the system
  language list, Chromium from the region), so the same field reads differently
  for every editor and no markup can change that. The preference plus the picker
  is the replacement.
- A picker field is `<input type="text" name="…" id="…" class="form-control"
  value="canonical Y-m-d H:i:s" data-datepicker="1"
  data-datepicker-alt-format="…" data-datepicker-24h="0|1"
  data-datepicker-weekstart="0|1" data-datepicker-label="…">`. The module creates
  the visible twin, copies the `class` and the label, and dispatches `change` on
  the canonical input, so the existing inline `onchange` handlers keep working.
  Typing is parsed on blur. Render the three values from `$backend_date_format`
  and `$backend_weekstart`, recomputed in the template when it also runs outside
  the backend frame.
- `data-datepicker-defaulthour`, `-defaultminute` and `-defaultseconds` set the
  time a day gets when it is picked without one (flatpickr's own default is
  noon). The content part editor uses `0/0/0` for the live date and `23/59/59`
  for the kill date, which is what its old calendar widget wrote.
- Month names stay English, the ones flatpickr ships with: only the date and time
  pattern and the week start day follow the preference.

## Content part editor dates

- The live and kill fields of the content part editor
  (`include/inc_tmpl/articlecontent.edit.tmpl.php`, `clivedate`/`ckilldate` with
  their `set_livedate`/`set_killdate` checkboxes) are picker fields too. They hold
  the canonical `Y-m-d H:i:s`, show the format and week start from the profile
  and got a live/kill time of `00:00:00`/`23:59:59` from their default time
  attributes, the values the replaced `dynCalendar` widget wrote. The template
  calls `initJsDatePicker()` itself because the frame does not do it for
  `aktion=2`.
- `content_date_changed()` keeps the two rules `aBegin`/`aEnd` carried: picking a
  date ticks its checkbox, and a live date after the kill date clears that kill
  date (and the other way round), compared by date only.
- `content_date_set(field, value)` is what the checkboxes call: it writes the
  stored value through `window.phpwcmsDatePickerSet()` and ticks or unticks the
  box, so unticking empties the field and ticking it restores the value the
  server rendered.
- The screen is still the legacy table layout; only the two date fields were
  converted, so the picker fields keep the legacy `width150` class instead of
  `form-control`.

## JavaScript libraries

- The backend does not need MooTools any more. Remove it there in small steps, but
  only after a human confirmed that specific step - it is a legacy dependency with
  several remaining consumers, not a change to make on the side.
- Facts to work from when a step is approved: `phpwcms.php` calls `initMootools()`
  for every backend page, while only `phpwcms.js` (`flush_image_cache()`),
  `filebrowser.php`, the content part editors (`cnt8`, `cnt13`, `cnt23`, `cnt31`,
  `cnt32`) and `mod_calendar/backend.editform.php` still use the MooTools API. The
  MooTools autocompleter (`include/inc_js/mootools/cnet/`) is the only piece
  without a vanilla equivalent yet.
- The frontend needs MooTools only as an optional compatibility library. Keep
  `include/inc_front/lib/js.mootools-1.1.inc.php`, `js.mootools-1.2.inc.php` and
  `template/lib/mootools/` available for existing templates, but stop shipping
  anything new that requires it - new frontend code uses the configured library
  (jQuery) or plain DOM, and shipped templates should move off MooTools
  (Slimbox/lightbox paths) on their own schedule.
- Do not replace MooTools with another framework. The target is vanilla DOM plus
  Bootstrap components; jQuery stays only where a screen already asks for it with
  `initJQuery()`.
- While MooTools is still loaded in the backend frame it replaces `window.Event`
  and `window.Element`, which is why `include/inc_js/backend.bootstrap.js` bridges
  both. Do not remove those bridges before MooTools is gone.

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
