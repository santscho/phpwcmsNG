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

## Confirmation dialog

- Every backend question — delete a file, an article, a content part, empty the
trash — is asked with the Bootstrap dialog component, never with the browser's
own `confirm()` box. The templates keep their inline handlers
(`onclick="return confirm(…)"`, `onchange="if (confirm(…)) …"`), the questions
and the actions stay where they are.
- `include/inc_js/backend.confirm.js` answers them. The backend frame
(`phpwcms.php`) loads it for every page, next to `backend.sortable.js`, and
prints its labels as JSON in `#backend-confirm-labels` from the language keys
`be_confirm_title`, `be_confirm_ok` and `be_confirm_cancel`, so the dialog
speaks the backend language.
- The module listens for `click` and `change` in the capture phase on
`document`, finds the `[onclick]`/`[onchange]` element whose source calls
`confirm(` and reads the question by running that handler once with a `confirm`
that throws straight after it stored the text — nothing after the question runs
in that pass. A handler that fails before it reaches `confirm` keeps the browser
dialog.
- It then cancels the event and shows one `<dialog class="dialog">` per page
(`new Dialog(element)` from the bundle, `.dialog-header/body/footer`, title
`Please confirm`). The question is split at its line breaks into one `<div>` per
line. `Cancel` is `btn btn-outline theme-secondary btn-sm`; `OK` is the solid
primary button, and switches to `theme-danger` when the question is a
destructive one (`delete`, `remove`, `trash`, `empty`, `clear` and their German,
French, Spanish and Dutch words).
- On OK the same click or change is dispatched again with `window.confirm =
() => true` while the element carries `data-backend-confirm-running`, which the
module ignores — so the handler runs exactly as before and the navigation or
form submit it triggers still happens. The action runs only after the dialog is
closed: a modal dialog makes the rest of the page inert.
- Bootstrap drops a hide requested while the dialog is still opening, so
`close()` asks again and falls back to the native `close()`.
- `window.phpwcmsConfirm(question)` is the same question as a promise for
scripts that have no inline handler:
`phpwcmsConfirm('Delete this file?').then(confirmed => { if (confirmed) … })`.
- Do not test a node with `instanceof Element` here: MooTools replaces the
global constructor in the backend frame.
- A question text is written in the template as before and escaped with
`js_singlequote()`; that helper turns line breaks into the `\n` escape, because a
raw line break inside a JavaScript string is a syntax error — the handler then
never runs and the action fires without asking. A text that already carries
JavaScript `\n` escapes (the frame's confirm messages do) is printed as it is.
- A declined question runs a `change` handler once more with `window.confirm`
answering no, because those handlers put their control back in the else branch
(the content type select returns to the old type). Click handlers are not
replayed — their default action would follow the link.

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
- **tree, add and sort**: `chevron-right`, `chevron-down`, `chevron-up` (move
  a content part up), `plus`
- **list transfer**: `chevron-right` moves the selected entries into the target
  list, `chevron-left` back (the teaser form's article and source lists)
- **external link marker**: `arrow-up-right`, before a link or feed preview that
  opens somewhere else (the content part listings of link, link list and RSS
  feed)
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
  to   `include/inc_act/act_upload.php` under the field name `qqfile` and reloads
  the listing afterwards. The qq FileUploader widget is gone — neither this
  screen nor the file browser loads it any more, so `include/inc_js/uploader/`
  keeps only the server side `fileuploader.php` that `act_upload.php` requires.

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

## File browser popup

- `filebrowser.php` is the picker behind every `open image browser` button
  (`openFileBrowser()` in `include/inc_js/phpwcms.js` opens it as a resizable
  popup, `max(screen.width / 2.5, 900)` wide and `max(screen.height / 2, 575)`
  high, so the two columns fit next to each other, and it widens a browser
  window that is still open to that size instead of only focusing it). It is a
  standalone page, not part of the backend frame, so it links
  `include/inc_ext/bootstrap/bootstrap.min.css` itself, takes the screen mode
  from `$_SESSION['wcs_user_theme']` for `data-bs-theme` and loads the same `backend.bootstrap.js` (tooltips), `backend.confirm.js` (with
  its label JSON) and `backend.tags.js` modules as the frame.
- The layout is a `bg-primary` navbar carrying the browser title and the file
  type as a `badge theme-secondary`, then a `container-fluid` with the
  collapsible multiple upload card (`details#filebrowser-uploader`) and a
  `row g-5` of the folder list (`col-12 md:col-5`) next to the file list
  (`col-12 md:col-7`). Both are cards with a `list-group list-group-flush`.
- Folder rows are `list-group-item` rows with the open/close chevron
  (`chevron-right`/`chevron-down`, `OPEN_DIR`/`CLOSE_DIR` tooltips), the
  `folder-fill` icon and the name. The nesting is a left padding on the row
  (`folder_indent()`, `ps-9`/`ps-12`), not a margin: a `ms-*` on the row would
  indent its dividers and its background as well, which reads as a shifted list
  instead of a nested one. `folder_list()` takes the depth instead of the old
  pixel indent, the current directory carries `bg-2`.
- File rows are the `backend_file_icon()` mark, the name — a link that picks the
  file and closes the window in the modes that close it — and one take button.
  The `window.opener` calls of every `opt` mode are unchanged. The thumbnail is a
  `rounded mw-100` image under the row, `add all files` is a
  `btn-outline theme-secondary` row with the `plus` icon, and an empty directory
  or a user without files is a `fg-secondary` row with `NO_FILE`.
- `addAllFiles()` asks through `window.phpwcmsConfirm()`, so the popup needs the
  confirm module and its labels like any screen of the frame. `ADD_ALL_CONFIRM`
  carries JavaScript `\n` escapes like the frame's confirm texts, so they are
  turned into real line breaks before `js_singlequote()` escapes the string for
  the script — the dialog splits the question at real line breaks, and doubling
  the backslashes would show a literal `\n`. Invisible control characters in a
  language string show up as a missing glyph in the dialog: `ADD_ALL_CONFIRM`
  (en, bs) and `TAKE_IMAGE` (cs, cz) ended in a stray U+0085 and carry an
  ellipsis now.
- The upload panel posts through `include/inc_js/backend.upload.js`: a
  `[data-upload-dropzone]` with a multiple `form-control` file input and a
  `[data-upload-fields]` container whose named fields are added to the request
  as query parameters, the way the qq FileUploader's `setParams()` sent them —
  `act_upload.php` reads `file_dir`, `file_aktiv`, `file_public`,
  `file_longinfo`, `file_copyright` and `file_tags` from `$_GET`. `file_tags`
  is copied from the tag field's `as-values-keyword-autosuggest` value before
  the upload starts.
- The popup no longer loads `phpwcms.min.css`, `autoSuggest.css`,
  `fileuploader.css` or the qq FileUploader; `phpwcms.min.js` stays for
  `tmt_winControl()`.
- The frame and the popup link `phpwcms.min.js` as
  `include/inc_js/phpwcms.min.js?v=<filemtime>`. Without the stamp, browsers keep
  a heuristically cached copy of the script (there is no `Cache-Control` on it)
  and a change like the browser size would not reach an editor who has the
  backend open.

## Article center (article tree)

- The tree of `ARTICLE > article center` (`article.structlist.tmpl.php` with
  `struct_list()`, `struct_levellist()`, `struct_articlelist()` and
  `struct_articlecontentlist()` in `include/inc_lib/admin.functions.inc.php`) has
  four row kinds and they do not carry the same actions:
  - the root row (`Home`) offers the `plus` new article action only — the root
    level itself can neither be cut nor copied, so it shows no scissors, copy or
    delete button;
  - a structure level row offers `plus` and its `backend_sort_handle()`; the
    scissors and copy buttons are paste targets for an article that is marked
    for cut or copy, so they only appear while one is marked, and never as a
    disabled pair;
  - an article row keeps `pen`, cut, copy, visibility and delete plus its sort
    handle, and a content part row keeps `pen`, cut, copy, visibility, delete and
    its sort handle.
- `ADMIN > site structure` (`admin.structlist.tmpl.php`) uses the same helper
  with `$listmode = 1`. Its root row shows only the `plus` new level action and
  the `pen` of the index page, because the root level can neither be cut nor
  copied; a level below it is a real cut/copy source, so its scissors and copy
  buttons stay, disabled only where the level is invalid for the operation.

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

## Article edit screen (content parts)

- `ARTICLE > article center` and the article tree open this screen with the pen
  action (`phpwcms.php?do=articles&p=2&s=1&id=<aid>`), it is
  `include/inc_tmpl/articlecontent.list.tmpl.php`. It is one form
  (`name="addcontent" id="addcontent"`) with two stacked cards in a `row g-4`.
- The first card is the article: `journal-bookmark-fill`, the title as a link to
  the basis form, `AID:<id>`, visible/inactive and the language code as badges in
  the header, the pen, visibility (`eye` `theme-success` / `eye-slash`
  `theme-danger`) and article delete actions on the right. The body is a
  `row g-4` of label/value pairs from `$info_row()` (full width for summary,
  images, status and robots), the article images as thumbnails. The footer
  carries `article center` and `preview`.
- Status marks are `badge theme-success`/`theme-secondary` and keep the
  conditions the old list used — sitemap `article_nositemap == 1`, search
  `article_nosearch != 1`, RSS `article_norsr != 1`, opengraph `== 1`, archive
  `== 1`; robots noindex/nofollow are `badge theme-danger`.
- The second card lists the content parts and carries the add form in its
  header: the `ctype` select (changing it submits, `w-auto` keeps it narrow) and
  the `image` submit, plus the hidden `csorting` at the end of the form. The POST
  opens the content part editor for the chosen type, it does not insert a row.
- The list is a `list-group list-group-flush`: block headers (`bg-2`, uppercase
  `small fw-bold`) and tabbed headers, then one `list-group-item` per content
  part with `postcard-fill` (plus `lock`/`person-fill` for the granted states),
  the type as `small fw-bold text-uppercase`, `ID:<id>` badge, its date in the
  user's format, meta badges (paginate page, space before/after, top link,
  anchor), a visible/inactive badge, the live and kill dates,   the type info line and the action group: edit `pen`, copy `copy`,
  visibility `eye`/`eye-slash`, delete `trash`. The row is reordered by dragging
  the `backend_sort_handle()` at its end, exactly like the article list: the
  `list-group` carries `data-backend-sortable`, the token and the error text, a
  row carries `data-sort-group="content"`, `data-sort-id`, `data-sort-parent`
  (the article) and `data-sort-block`, and only an article with more than one
  content part in that block gets them. Block and tab header rows stay plain
  items, so a part can only be dropped between rows of its own block. The old
  `act_articlecontent.php?sort=<id>:<sort>|<id>:<sort>` up/down arrows are gone
  from this screen; the duplicate
  (`act_structure.php?do=8|<cid>|<aid>|<sorting+5>`), the visibility toggle
  (`do=2,<aid>,<cid>,<switch>`) and delete (`do=9,<aid>,<cid>`) calls and the
  confirm texts are unchanged.
- The 35 type listings `include/inc_tmpl/content/cnt*.list.inc.php` (and
  `mod_glossary/inc/cnt.list.php`) are included per row. They output one
  `<div class="small fg-secondary">` info block now, without table rows, spacer
  gifs or button images, and keep the same variables (`$row`, `$article`,
  `$cinfo`).
- Dates come from `$backend_date_format`, like everywhere else. `$set_begin` and
  `$set_end` are provided by the caller.

## Tag and keyword fields

- Every tag and keyword field runs on the jQuery autoSuggest plugin
  (`include/inc_js/jquery/jquery.autoSuggest.min.js`): the article keywords
  (`#article_keyword_autosuggest`), the news keywords
  (`#news_keyword_autosuggest`), the file tags (`#file_tags_autosuggest`) and the
  news category in the news content part. `initJsAutocompleter()` loads the
  plugin and `include/inc_js/backend.tags.js`; `include/inc_css/autoSuggest.css`
  is gone, every screen maps the generated markup through that module — the file
  browser popup included.
- The plugin builds its own markup, so the module maps the generated elements to
  Bootstrap components: the selections list becomes a `form-control` box, a
  selected term a `badge rounded-pill theme-primary` reading `term ×`, and the
  result list a `list-group` panel with `list-group-item list-group-item-action`
  entries. The plugin marks the hovered suggestion with `active`, which the list
  group item already styles, and it shows and hides the panel itself, so the
  panel cannot be the `menu` component (that one is only visible with `show`).
- The plugin rewrites the result list on every keystroke and the selections after
  every add or remove, so the module decorates from a MutationObserver rather
  than once. It only adds classes and never removes them, which keeps the
  observer from feeding itself. Do not defer that work to
  `requestAnimationFrame`: it never runs in a background tab.
- The plugin's hidden `as_values_<id>` input stays the contract for the screens:
  the article form reads `#as-values-keyword-autosuggest` on submit, and every
  widget keeps its `asHtmlID` and options.

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

## Content part editor

- `include/inc_tmpl/articlecontent.edit.tmpl.php` is the editor for every
  content part type. It is one form (`name="articlecontent"`) of stacked cards
  in a `row g-4`: the article the part belongs to (category link, article title,
  content type select), the placement (display block, subsection, system
  rendering), content title and subtitle with the pagination fields, the type
  form, the spacing and attributes (before/after, top link, anchor, CSS class and
  id), timing and status (live and kill picker, visible, granted, sort value) and
  the notes. The submit buttons (Update, Save & close, Cancel, preview) sit in the
  footer of the type card and again in the footer of the status card, the two
  places they were in before.
- The type forms are included into a slot: `$cp_form_bootstrap` lists the types
  that render their own Bootstrap markup and are included into a `card-body`
  (currently `0`, `1`, `2`, `3`, `4`, `5`, `6`, `7`, `8`, `10`, `11`, `12`, `13`,
  `14`, `15`, `16`, `18`, `19`, `21`, `22`, `24`, `25`, `26`, `27`, `28`, `29`,
  `33`, `50`, `51`, `52`, `89` and `100`), every other type form
  still writes `<tr><td>` rows and is included into a plain `table.w-100` inside
  the card body. Revamping a type means rewriting its `cnt<type>.inc.php` as
  `div`-based Bootstrap markup and adding its number to that list; do not remove
  the table branch before the last type has moved.
- Names, ids, hidden fields and inline handlers are unchanged — `cblock`, `ctab`,
  `ctid`, `ctitle`, `csubtitle`, `cpaginate_page`/`_title`, `ccb`/`cbefore`,
  `cca`/`cafter`, `ctop`, `canchor`, `cattr_class`/`_id`, `set_livedate`,
  `clivedate`, `set_killdate`, `ckilldate`, `cvisible`, `cgranted`, `csorting`,
  `caktion`, `caid`, `cid`, `ctype`, `ccomment`, `Submit`, `SubmitClose`,
  `donotsubmit` — and the form name stays `articlecontent`, because the type
  forms address their fields as `document.articlecontent.*`.
- The sections that used to be table rows are now hidden or shown as blocks, so
  `checkTabStatus()` and `checkCntBlockPaginate()` toggle `display` of the
  `#ctab2` and `#system1` divs; `checkCntBlockPaginate()` is called at the end of
  the form with `document.getElementById('cblock')` instead of the MooTools
  `getObjectById()`.
- The confirm texts of the frame (`be_dialog_warn_nosave`,
  `be_func_switch_contentpart`, `be_cnt_subsection_warning`,
  `confirm_cp_tab_warning`) carry the `\n` escapes of JavaScript themselves and
  are printed as they are — passing them through `js_singlequote()` would double
  those backslashes and the dialog would show a literal `\n`.

## Content part editor: text, list, link and HTML forms

- The eight smallest type forms moved to Bootstrap in one batch: plain text `0`,
  link & email `3`, bullet list `4`, link list `5`, HTML `6`, the deprecated
  email form generator `10`, PHP variables `52` and list `100`. Each one is a
  plain `row g-4` grid without a wrapper element of its own, because the frame
  puts it into the `card-body` of the type card.
- Field names, ids and inline handlers are untouched: `template`, `ctext`,
  `chtml`, `cvar`, `clink`, `ctarget`, `cmailsubject`, `cmailrecipient`,
  `cmailbutton`, `cmailform`, plus the radios `ctext_format`, `cmailhtml` and
  `clist_type`. The `is_checked()`/`is_selected()` calls are the original ones,
  including the `is_checked(..., 0, 0)` form of `cnt100` that echoes its own
  attribute.
- The radio groups became `.form-field`/`.radio`/`<label for>` pairs in a
  `d-flex flex-wrap gap-4` row instead of nested tables; the option labels of the
  list form keep their `&lt;ul&gt;`/`&lt;ol&gt;`/`&lt;dl&gt;` text. The Wikipedia
  links of the plain text format radios are Bootstrap tooltips
  (`data-bs-toggle="tooltip"`).
- Code-like textareas (`ctext` of plain text, `chtml`, `cvar`) are monospace
  through `style="font-family: var(--bs-font-mono)"` and keep the `autosize`
  class; the remaining textareas are `form-control autosize` with their `wrap`
  attribute. The deprecation notice of the email form generator is an
  `alert theme-warning`.
- The plain text form still calls `initMootools('1.2')`; it is the only form of
  the batch that asks for MooTools. Nothing else in these eight forms uses it.
- Verified with one temporary content part per type on article 1: every form
  renders inside the card body without a table row, keeps all of its fields, and
  a save round-trip of the plain text form stored `acontent_text` and the
  `ctext_format` key of the form data unchanged. The temporary parts were deleted
  again, so article 1 keeps the parts it had.

## Content part editor: sitemap, pages, RSS, FAQ, login and poll forms

- The next six type forms are Bootstrap too: sitemap `19`, ext. content `21`,
  RSS feed `22`, FAQ `27`, frontend login `28` and poll `89`. Same slot rule as
  the batch above: `row g-4` grids without a wrapper, straight into the card
  body.
- Every field keeps its name, id and handler — `csitemap_*`, `cpage_source`,
  `cpage_custom`, `cpage_file`, `crss_template`, `crss_url`, `crss_item`,
  `crss_contenttype`, `crss_cacheoff`, `crss_timeout`, `faq_template`,
  `faq_question`, `cimage_name`/`cimage_id`/`cimage_width`/`cimage_height`/
  `cimage_zoom`/`cimage_caption`, `cookie_expire`, `date_format`, `locale`,
  `validate_userdetail`, `validate_backenduser`, `accept_email_login`,
  `profile_registration`, `profile_manage`, `profile_manage_redirect`,
  `cpoll_buttonstyle`, `cpoll_buttontext`, `cpoll_caption`, `cimage_list[]`,
  `cpoll_width`/`cpoll_height`/`cpoll_zoom`. The `is_checked()` and
  `is_selected()` calls are unchanged, including `is_selected()` called with
  `(value, option, 0, 0)` for the RSS cache select.
- Image pickers are one row: the text field, the `open image browser` button and
  a `trash` `backend_action()`; the poll and image lists keep the `multiple`
  select with the up/down/trash actions next to it and the thumbnails as a
  `d-flex flex-wrap` row. The FAQ thumbnail and caption share a row.
- The page list of the ext. content form (`browse_pages_dir()`) no longer builds
  table rows: each file is one `.form-field` radio with a generated id
  (`cpage_file_` plus eight characters of the path hash) and a label, grouped
  under a `small fg-secondary` directory heading, inside a bordered
  `overflow-auto` box. The radio value stays the full path, so the frontend
  lookup is untouched.
- The RSS form drops the dead comment block around `crss_cut1st` and the
  `l538_70.gif` rule, which is an `<hr>` now; the info links of the frontend
  login form are `(?)` links with a Bootstrap tooltip instead of the famfamfam
  sprite.
- Two guards were hardened because the forms are also reached with a `false`
  value in their form array (nothing new, the old code printed a PHP warning):
  `page_file` and `poll_list` are initialised when they are not arrays, and the
  poll select size no longer reads `poll_select` without checking it.
- Verified with one temporary content part per type on article 1: no table
  leftovers, no PHP warnings, all fields rendered. The temporary parts were
  removed again.

## Content part editor: teaser, search and media player

- Three more type forms are Bootstrap: teaser/link article `8`, search `13` and
  media player `25`. Field names, ids and inline handlers are unchanged.
- Teaser: the mode rows keep their ids — `calink_manual_0/1/2`,
  `calink_auto_0/1` and `prio0`/`prio1` — but are `div` blocks now, because
  `showHide_TeaserArticleSelection()` in `contentpart.js` only toggles `display`
  by id. `prio0` wraps the checkbox and `prio1` the label, so the priority mark
  still disappears in manual mode while `calink_prio` keeps its id.
- The article and source lists stay `multiple` selects (`calink[]` with id
  `calink`, `calinklist`) because `moveSelectedOptions()`, `moveOptionUp()` and
  `moveOptionDown()` from `optionselect.js` address them through the
  `teaser_items`/`source_items` globals the template defines. Their actions are
  `backend_action()` buttons: `chevron-up`, `chevron-down`, `chevron-left` (back
  to the source list, `theme-danger`) and `chevron-right` (into the selection).
- The filter row's `input type="image"` magnifier is a primary button labelled
  with `be_filter` and still named `Submit`, so the filter values reach the POST
  the way they did.
- The teaser tags use the jQuery autoSuggest widget like every other tag field:
  the visible `#calink_keyword_autosuggest` sits next to the hidden
  `calink_category` that is filled on submit from
  `#as-values-teaser-category-autosuggest`, so the server still reads
  `$_POST['calink_category']`. The MooTools `Autocompleter.Ajax.Json` of this
  form and its `initMootools()` calls are gone.
- Search: result/word/minimum-character fields with their marks, the start-level
  select, the default type, the "search not" marks, the news module block
  (language field, tag autosuggest over the hidden `csearch_news_category`, AND/OR
  and target), the module marks, the label fields and the intro/result/no-result
  texts. The news tag field moved to autoSuggest; the news **language** field
  stays a plain text field with its `keyup` filter, because the `lang` action of
  the connector answers plain strings and autoSuggest needs objects.
- Media player: the H.264, WebM and Ogg blocks are generated from one list, each
  with its internal/external radios, the internal id, the file field, the media
  browser button and a `trash` action, plus the Flash fallback block and the
  thumbnail. The settings are marks, selects and the five hidden legacy fields,
  and the whole `setIdName()`/`setImgIdName()`/`setPlayerSize()`/marker script is
  unchanged.
- Verified with one temporary content part per type on article 1: no table rows,
  no spacer gifs, no PHP warnings. A save round-trip of the teaser form stored
  `alink_id`, `alink_category` (split from the hidden field), type, priority,
  max, columns, unique and AND/OR. The temporary parts were removed again.

## Content part editor: guestbook, recipe, images div, news, rollover images, map

- Six more type forms are Bootstrap grids in the card body: guestbook `18`,
  recipe `26`, images `<div>` `29`, news `33`, rollover images (`reference`)
  `50` and map `51`. Field names, ids and inline handlers are unchanged, and the
  forms start by turning an empty `acontent_form` (`false` after unserializing)
  into an array so PHP 8.5 does not warn about writing into `false`.
- Guestbook: the alias ID input keeps the select of other guestbook content
  parts that still have no alias; listing/all, per-page count, login marks, image
  upload with its maximum size in bytes, date sorting, captcha length, URL check,
  banned words, flooding cookie and interval, e-mail notification and the entry
  editor iframe all sit in one grid. The iframe keeps its `CSRF_GET_TOKEN` src and
  is `width="100%"` now instead of 440 pixels.
- Recipe: the keyword select keeps `insertAtCursorPos()` and gets the `plus`
  action button that inserts the picked keyword into the keywords textarea. The
  section labels (Zutaten, Zuber.Zeit, Nährwert, Schwierigkeit, Zubereitung) are
  hardcoded in the file — there are no language keys for them — and stay as they
  are; severity is a loop over the five radios.
- Images `<div>`: the header keeps the template select, the alignment select
  `cimage_center`, width/height with the crop mark and columns, space, random and
  the limit for the list. `setCimageCenterInactive()` still disables alignment
  and crop while no size is typed; its script moved below the grid because the
  card body no longer has table rows to hang it on.
- News: the sorting select had copied the `calink_type` id of the teaser form,
  which nothing uses, so it is `news_sort` now. Tags keep the jQuery
  `autoSuggest` field (`news_keyword_autosuggest` plus the hidden
  `news_category`), the language marks are checkboxes with language names instead
  of the famfamfam flag gifs (classes `allowedLang` and id `langAll` unchanged),
  and the pagination, archive and link fields keep their script untouched.
- Rollover images: text, the `cimage_list[]` image list (`opt=3` browser) with
  actions and thumbnails, the caption with its helper, the zoom mark, the large
  image size and border, the alignment basis with the position select and the
  block width/height, space and border share two section labels from the
  language file instead of bold table headers.
- Map: map images are radios with a thumbnail and their file name; the wz_tooltip
  `Tip('<img …>')` preview is gone because the thumbnail shows the same image.
  Choosing one shows the location editor (X/Y with `doMapChange()`, the `open map`
  popup, title, zip, city, entry editor, hidden id and its own save button) and
  the location list, now a `list-group` with `pen` and `trash` actions instead of
  a zebra table row per location. The delete confirmation has to survive quotes
  and the newline inside `be_btn_delete`: its text goes through `json_encode()`
  and then `html()` into the `onclick` attribute, and the tooltip uses the same
  text with the newline collapsed. The empty map folder is a `fg-danger small`
  note instead of the `error` table cell.
- No new icons were needed: the batch uses `plus`, `chevron-up`, `chevron-down`,
  `trash` and `pen` from the existing set.
- Verified with one temporary content part per type on article 1: no table rows,
  no spacer gifs, no PHP warnings and the expected field names. The map branch
  needed a temporary map image and three temporary `phpwcms_map` rows to render
  its editor and list; image, rows and content parts were removed afterwards. A
  save round-trip of the news form stored all thirteen values, including the
  language mark, the split tag list, sort, pagination and both link fields.

## Content part editor: text w/image, images, file list, newsletter and WYSIWYG

- Five more type forms are Bootstrap: text w/image `1`, images `2`, file list
  `7`, newsletter subscription `12` and WYSIWYG `14`. They are `row g-4` grids
  in the card body, like the batches above.
- The image forms (`1`, `2`) and the file list (`7`) keep their `multiple`
  selects (`cimage_list[]`, `cfile_list[]`) with the up/down/trash
  `backend_action()` group and the `open image browser` / `open file browser`
  text button next to them; the thumbnails are a `d-flex flex-wrap` row instead
  of spacer gifs and `<br>`. The caption and description textareas keep their
  field names and carry the `caption|alt|link title|copyright` helper as
  `small fg-secondary` text.
- **The ten-icon image position picker is gone from `1` and `2`.** It was ten
  `img/button/image_pos*.gif` buttons plus ten `imgpos*` marker images swapped by
  `changeImagePos()`, and it only wrote the value the `cimage_pos` select next to
  it already offers (all ten positions, with their labels). The select is the
  control now, without its `changeImagePosMenu()` handler; the two functions stay
  in `phpwcms.js` because the multimedia form `9` still uses them, and they need
  the `imgpos*` images it still renders.
- `cimage_pos`, `cimage_usetable`, `cimage_width`, `cimage_height`,
  `cimage_crop`, `cimage_limit`, `cimage_random`, `cimage_col`, `cimage_space`,
  `cimage_zoom`, `cimage_lightbox` (still ticks `cimage_zoom`),
  `cimage_nocaption`, `cimage_caption`, `cfile_direct`, `cfile_list[]`,
  `cfile_descr`, `cnewsletter_*` and `customfield[<key>]` keep name, id and
  inline handlers. A blocked direct download of the file list is an
  `alert theme-warning` instead of the `error` table cell.
- The newsletter form still embeds `createOptionTransferSelectList()`: that
  shared helper builds its own table with gif arrows and is used by other
  screens, so it is not rewritten inside a content part form. It is the one
  legacy block left in a converted form; revamping it means changing the helper
  and every caller.
- The WYSIWYG form's custom field groups are `div` blocks now (`col-12` for
  wysiwyg and textarea fields, `col-12 md:col-6` otherwise) instead of
  `tr.tab-collapsable-row` rows. Controls get an id `customfield_<key>` (option
  radios `customfield_<key>_<8 char md5 of the value>`) so their labels can use
  `for`; the field names stay `customfield[<key>]`. The `tab-collapsable-row`,
  `tab-option-radio` and `tab-option-checkbox` classes stay on the new elements,
  and `toggleTabsTemplate()` plus the hidden `cnt_fieldgroup` are unchanged.
- The `takeval` includes of these types now turn an empty form value into an
  array (`image_list`, `file`, `newsletter`): an unserialize of an empty column
  returns `false`, and writing into it made PHP 8.5 print the "automatic
  conversion of false to array" deprecation.
- Verified with one temporary content part per type on article 1: no table rows
  or spacer gifs, no PHP warnings, all fields rendered. A save round-trip of the
  text w/image form stored `cimage_crop` (and the lightbox/nocaption marks) in
  the form data. The temporary parts were removed again.

## Content part editor: e-card

- `include/inc_tmpl/content/cnt16.inc.php` is the fourth type form in Bootstrap:
  a `row g-4` of `col-12` and `col-12 md:col-6` cells and no wrapper element of
  its own, because the frame puts it into the `card-body` of the type card.
- The fields keep their names, ids and values: `cecard_subject`, the selector
  radios `cecard_selector` (`0` radio button, `1` JavaScript functionality),
  `cecard_onover`/`cecard_onclick`/`cecard_onout` (the first one has the id
  `cecard_over`), the image list `cimage_list[]`, `cecard_pos`, the
  `cecard_width`/`cecard_height` pair, `cecard_col`, `cecard_space`, the
  `cecard_zoom` mark, `cecard_caption` and the three template textareas
  `cecard_form`, `cecard_send`, `cecard_mail`.
- The image list stays a `multiple` select named `cimage_list[]` with the id
  `cimage_list`, because the inline handlers address it as
  `document.articlecontent.cimage_list`: `moveOptionUp()`,
  `moveOptionDown()`, `removeSelectedOptions()` and the form's own
  `selectAllOptions(this.cimage_list)` are unchanged. Its three actions are
  `backend_action()` buttons (`chevron-up`, `chevron-down`, and `trash` in
  `theme-danger`) next to the one `btn-outline theme-secondary` that opens
  `openFileBrowser('filebrowser.php?opt=3&amp;target=nolist')`; the spacer gifs
  around the thumbnails are gone and the thumbnails are one `d-flex flex-wrap`
  row.
- The default sender form template is data, not markup: the HTML table the code
  builds into `cecard_form` while the field is empty is kept verbatim, the
  textarea prints it through `html()`, and it round-trips byte for byte through
  a save. All three template textareas are monospace through
  `style="font-family: var(--bs-font-mono)"` and keep the `autosize` class.
- The `###TAG###` lines above the templates are `small fg-secondary` hints, the
  `onKeyUp` guards of the numeric fields are untouched, and the `px` suffixes of
  width, height and image space sit in `input-group-text` spans instead of table
  cells.

## Content part editor: content part alias

- `include/inc_tmpl/content/cnt24.inc.php` is the third type form in Bootstrap:
  the alias ID (`calias`) as a `form-control w-auto` next to the button that
  opens the aliased content part, and the five `take over` marks (`cablock`,
  `caspaces`, `catitle`, `catop`, `castatus`) as `.form-field`/`.check` pairs on
  the right.
- The link to the aliased part used to be assembled as table cells; it is one
  `btn btn-outline theme-secondary btn-sm` now, opening the part in a new tab, and
  a `badge theme-danger` with `be_cnt_plugin_n.a.` when the aliased part is a
  plugin that is not installed. The lookup itself is unchanged.

## Content part editor: code

- `include/inc_tmpl/content/cnt11.inc.php` is the second type form rebuilt with
  Bootstrap: the template select (`template`, the files of
  `template/inc_cntpart/code`) as a `form-control` in a `col-12 md:col-6` cell,
  then the code textarea (`ccode`, `rows="20"`) full width as
  `form-control font-monospace autosize`.
- The code field is monospace through the framework's own token,
  `style="font-family: var(--bs-font-mono)"`: the `font-monospace` utility does
  not reach a `textarea` in Bootstrap 6.0.0-alpha.1 (the form control font wins
  in the cascade, even from a wrapper that carries the class). No font stack and
  no stylesheet is added for it.
- The textarea keeps the `autosize` class for the jQuery autosize and the rule
  that prepends a space to code starting with a line break; the legacy `code` and
  `width440` classes are gone.

## Content part editor: article menu

- `include/inc_tmpl/content/cnt15.inc.php` is the first type form rebuilt with
  Bootstrap: a `row g-4` of `col-12 md:col-6` cells, no wrapper element of its
  own, because the frame puts it into the `card-body` of the type card.
- Every field keeps its name, id, value and the `is_checked()`/`is_selected()`
  helpers: `calist_cat` with the `calist_catid` structure select (site level),
  `calist_ul` (display as `<ul>`, `<div>`, `<dl>`, `<span>` or table),
  `calist_class`, `calist_titlewrap`, `calist_label`, `calist_break`,
  `calist_maxchar`, `calist_morelink` and the `calist_titleasnumber`,
  `calist_headertext` and `calist_hideactive` marks.
- Radio groups and checkboxes are `.form-field` wrappers with `.radio`/`.check`
  inputs and a `<label for>`; the text fields and both selects are
  `form-control`, like everywhere else in Bootstrap 6. The nested tables,
  spacer rows, `chatlist`/`v11` classes and inline widths are gone.
- The older `be_articlemenu_maxchar` key is not the one in use: the label of the
  max chars field is `be_cnt_articlemenu_maxchar`.

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
