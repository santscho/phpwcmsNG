/**
 * phpwcms content management system
 *
 * Backend confirmation dialog.
 *
 * Destructive actions ask before they run, and phpwcms asks with the browser's
 * own `confirm()`: `onclick="return confirm('Delete article?')"` on links and
 * buttons, `onchange="if (confirm(…)) …"` on selects. That box ignores the
 * screen mode, cannot be labelled in the backend language and blocks the page
 * while it is open.
 *
 * This module answers those questions with the Bootstrap dialog component. It
 * listens for clicks and changes in the capture phase, reads the question out of
 * the handler and shows the dialog instead of the browser box; on OK the handler
 * runs again with `window.confirm` answering `true`, so the action itself and
 * everything it posts stay untouched.
 *
 * Only handlers that call `confirm()` are taken over. The question is read by
 * running the handler once with a `confirm` that throws, so nothing after the
 * question runs in that pass. A handler that throws before it reaches `confirm`
 * keeps the browser dialog, so a handler doing more than asking still works.
 *
 * @license http://opensource.org/licenses/GPL-2 GNU GPL-2
 */

import { Dialog } from '../inc_ext/bootstrap/bootstrap.bundle.min.js';

const HANDLER_ATTRIBUTES = ['onclick', 'onchange'];
const RUNNING = 'data-backend-confirm-running';
const DESTRUCTIVE = /delete|remove|trash|empty|clear|löschen|entfernen|supprimer|eliminar|verwijderen/i;
const STOP = Symbol('backend-confirm-stop');

const labels = readLabels();

let dialog = null;
let dialogElement = null;
let questionBody = null;
let confirmButton = null;
let pendingAnswer = null;

/*
 *  Labels come from the backend frame, which prints them in the current backend
 *  language
 */
function readLabels() {

    const source = document.getElementById('backend-confirm-labels');
    let config = {};

    if (source) {
        try {
            config = JSON.parse(source.textContent);
        } catch (error) {
            config = {};
        }
    }

    return {
        title:  config.title  || 'Please confirm',
        ok:     config.ok     || 'OK',
        cancel: config.cancel || 'Cancel'
    };

}

/*
 *  The dialog is built once per page and reused for every question
 */
function createDialog() {

    if (dialog) {
        return dialog;
    }

    const element = document.createElement('dialog');

    element.className = 'dialog';
    element.innerHTML =
        '<div class="dialog-header">' +
            '<h2 class="dialog-title"></h2>' +
            '<button type="button" class="btn-close" data-bs-dismiss="dialog"></button>' +
        '</div>' +
        '<div class="dialog-body"></div>' +
        '<div class="dialog-footer justify-content-end">' +
            '<button type="button" class="btn btn-outline theme-secondary btn-sm" data-dialog-cancel></button>' +
            '<button type="button" class="btn theme-primary btn-solid btn-styled btn-sm" data-dialog-confirm></button>' +
        '</div>';

    element.querySelector('.dialog-title').textContent = labels.title;
    element.querySelector('.btn-close').setAttribute('aria-label', labels.cancel);
    element.querySelector('[data-dialog-cancel]').textContent = labels.cancel;

    questionBody  = element.querySelector('.dialog-body');
    confirmButton = element.querySelector('[data-dialog-confirm]');
    confirmButton.textContent = labels.ok;

    element.querySelector('[data-dialog-cancel]').addEventListener('click', () => {
        answer(false);
        close();
    });

    confirmButton.addEventListener('click', () => {
        answer(true);
        close();
    });

    // closing with the cross, Escape or the backdrop is a no; the buttons answer
    // themselves because a close right after an open is swallowed while the
    // dialog is still transitioning
    element.addEventListener('hidden.bs.dialog', () => answer(false));

    document.body.append(element);

    dialogElement = element;
    dialog = new Dialog(element);

    return dialog;

}

function answer(value) {

    const resolve = pendingAnswer;

    pendingAnswer = null;

    if (resolve) {
        resolve(value);
    }

}

/*
 *  Bootstrap drops a close requested while the dialog is still opening, so an
 *  unanswered close is asked again until it takes
 */
function close(attempt = 0) {

    if (!dialog || !dialogElement || !dialogElement.open) {
        return;
    }

    dialog.hide();

    if (!dialogElement.open) {
        return;
    }

    if (attempt < 3) {
        window.setTimeout(() => close(attempt + 1), 120);
    } else {
        // Bootstrap dropped the request because the dialog was still opening;
        // the native close still releases the page below the modal
        dialogElement.close();
    }

}

/*
 *  The dialog is modal, so the action can only run once it is really closed
 */
function afterClose(callback, attempt = 0) {

    if (!dialogElement || !dialogElement.open || attempt > 40) {
        callback();
        return;
    }

    window.setTimeout(() => afterClose(callback, attempt + 1), 50);

}

/*
 *  One line per line of the question, so the `\n` the handlers carry keeps
 *  separating the parts of the text
 */
function showQuestion(question) {

    questionBody.replaceChildren();

    question.split(/\r?\n/).forEach(line => {
        const row = document.createElement('div');
        if (line === '') {
            row.innerHTML = '&nbsp;';
        } else {
            row.textContent = line;
        }
        questionBody.append(row);
    });

    confirmButton.className = DESTRUCTIVE.test(question) ?
        'btn theme-danger btn-solid btn-styled btn-sm' :
        'btn theme-primary btn-solid btn-styled btn-sm';

}

function ask(question) {

    const instance = createDialog();

    // a question that is still open counts as answered no
    answer(false);
    showQuestion(question);

    return new Promise(resolve => {
        pendingAnswer = resolve;
        instance.show();
        confirmButton.focus();
    });

}

/*
 *  The first handler on the element that asks a question
 */
function handlerOf(element) {

    for (const attribute of HANDLER_ATTRIBUTES) {
        const handler = element.getAttribute(attribute);
        if (handler && handler.includes('confirm(')) {
            return { attribute, handler };
        }
    }

    return null;

}

/*
 *  Run the handler with a confirm that stops it right there and keeps the text.
 *  Anything that fails before that point returns nothing and keeps the browser
 *  dialog.
 */
function questionOf(element, handler) {

    let question = null;

    const collect = text => {
        question = String(text === null || text === undefined ? '' : text);
        throw STOP;
    };

    try {
        new Function('confirm', handler).call(element, collect);
    } catch (error) {
        if (error !== STOP) {
            return null;
        }
    }

    return question;

}

/*
 *  The confirmed action: the same click or change again, this time with
 *  `confirm` answering yes. The handler runs exactly as before, including the
 *  navigation or form submit it triggers.
 */
function runConfirmed(element, attribute) {

    const nativeConfirm = window.confirm;

    element.setAttribute(RUNNING, '1');
    window.confirm = () => true;

    try {
        if (attribute === 'onclick') {
            element.click();
        } else {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        }
    } finally {
        window.confirm = nativeConfirm;
        element.removeAttribute(RUNNING);
    }

}

function intercept(event) {

    const target = event.target;

    // MooTools replaces the global `Element` constructor, so `instanceof` is no
    // test for a DOM node here
    if (!target || target.nodeType !== 1 || typeof target.closest !== 'function') {
        return;
    }

    const element = target.closest('[onclick], [onchange]');

    if (!element || element.hasAttribute(RUNNING)) {
        return;
    }

    const handler = handlerOf(element);

    if (!handler) {
        return;
    }

    const question = questionOf(element, handler.handler);

    if (question === null) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    ask(question).then(confirmed => {
        if (confirmed) {
            afterClose(() => runConfirmed(element, handler.attribute));
        }
    });

}

// capture phase: the inline handler and every delegated listener run afterwards,
// so cancelling here really stops the action
document.addEventListener('click', intercept, true);
document.addEventListener('change', intercept, true);

/*
 *  Programmatic question for scripts that ask without an inline handler:
 *  `phpwcmsConfirm('Delete this file?').then(confirmed => { if (confirmed) … })`
 */
window.phpwcmsConfirm = question => ask(String(question === null || question === undefined ? '' : question));
