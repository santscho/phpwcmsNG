/**
 * Bootstrap styling for the autoSuggest tag fields.
 *
 * The tag and keyword fields (`#article_keyword_autosuggest`,
 * `#news_keyword_autosuggest`, `#file_tags_autosuggest` and the news category
 * in the news content part) are driven by the jQuery autoSuggest plugin. The
 * plugin builds its own markup — a list of selections, an input list item and a
 * result list — and used to be skinned by `include/inc_css/autoSuggest.css`.
 *
 * That stylesheet is gone. This module maps the generated elements to Bootstrap
 * instead: the field is a `form-control` holding `badge` items, the results are
 * a `dropdown-menu` with `dropdown-item` entries.
 *
 * The plugin rebuilds the result list on every keystroke and rewrites the
 * selections after each add or remove, so the classes are applied again from a
 * MutationObserver. Every class is added once and never removed, which keeps the
 * observer from feeding itself.
 */

const SELECTIONS = ['form-control', 'd-flex', 'flex-wrap', 'align-items-center', 'gap-2', 'py-2'];

// the plugin puts the remove link before the term, so the line is reversed to
// read `term ×` and the link keeps a little space from the text
const SELECTION_ITEM = [
    'badge',
    'rounded-pill',
    'theme-primary',
    'fw-normal',
    'd-inline-flex',
    'flex-row-reverse',
    'align-items-center'
];

const ORIGINAL = ['d-flex', 'flex-grow-1'];

const INPUT = ['border-0', 'bg-transparent', 'p-0', 'shadow-none', 'flex-grow-1', 'w-100'];

// the plugin shows and hides the result list itself, so it cannot be the menu
// component (which is only visible with `show`); the list is a plain panel and
// every suggestion an item of it
const RESULTS = [
    'list-group',
    'position-absolute',
    'top-100',
    'start-0',
    'end-0',
    'z-3',
    'shadow',
    'mt-1',
    'overflow-hidden'
];

const LIST = ['list-unstyled', 'mb-0', 'w-100'];

// the plugin marks the hovered suggestion with `active`, which the list group
// item picks up on its own
const RESULT_ITEM = ['list-group-item', 'list-group-item-action', 'border-0'];

const MESSAGE = ['px-3', 'py-1', 'text-secondary', 'small'];

const CLOSE = ['text-reset', 'text-decoration-none', 'ms-1', 'lh-1'];

function addClasses(element, classes) {
    classes.forEach(className => {
        if (!element.classList.contains(className)) {
            element.classList.add(className);
        }
    });
}

function decorateField(selections) {
    // the plugin renders the result list next to the field, not inside it, so
    // the wrapper has to be the positioning context
    if (selections.parentElement) {
        addClasses(selections.parentElement, ['position-relative']);
    }

    addClasses(selections, SELECTIONS);

    selections.querySelectorAll('li.as-original').forEach(item => {
        addClasses(item, ORIGINAL);
    });

    selections.querySelectorAll('input.as-input').forEach(input => {
        // the field itself is the form control now, the input only carries the
        // text that is being typed; keeping form-control would nest a second
        // border and its focus ring inside it
        input.classList.remove('form-control');
        addClasses(input, INPUT);
    });

    selections.querySelectorAll('li.as-selection-item').forEach(item => {
        addClasses(item, SELECTION_ITEM);

        item.querySelectorAll('a.as-close').forEach(close => {
            addClasses(close, CLOSE);
        });
    });

    const results = document.getElementById(selections.id.replace(/^as-selections-/, 'as-results-'));

    if (!results) {
        return;
    }

    addClasses(results, RESULTS);

    results.querySelectorAll('ul.as-list').forEach(list => {
        addClasses(list, LIST);
    });

    results.querySelectorAll('li.as-result-item').forEach(item => {
        addClasses(item, RESULT_ITEM);
    });

    results.querySelectorAll('li.as-message').forEach(item => {
        addClasses(item, MESSAGE);
    });
}

function decorateTagFields() {
    const fields = document.querySelectorAll('ul.as-selections');

    if (!fields.length) {
        return;
    }

    fields.forEach(decorateField);
}

// the plugin inserts and rewrites its markup, which is a childList change; the
// classes added here are attribute changes, so they never trigger the observer
// again
const observer = new MutationObserver(decorateTagFields);

observer.observe(document.documentElement, { childList: true, subtree: true });

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', decorateTagFields);
} else {
    decorateTagFields();
}
