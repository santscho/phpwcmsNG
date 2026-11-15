/**
 * phpwcms content management system
 *
 * Drag and drop ordering for backend lists. Rows opt in with
 * data-sort-group, data-sort-id and data-sort-parent, the container with
 * data-backend-sortable and data-backend-sort-token. A row is only draggable
 * while its [data-sort-handle] is pressed, so links and text stay selectable.
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 */

(function () {
    'use strict';

    function init() {

    var list = document.querySelector('[data-backend-sortable]');
    if (!list) {
        return;
    }

    var endpoint = list.getAttribute('data-backend-sort-endpoint') || 'include/inc_act/ajax_connector.php';
    var token = list.getAttribute('data-backend-sort-token') || '';
    var errorMessage = list.getAttribute('data-backend-sort-error') || 'Reordering failed.';
    var rows = [];
    var dragged = null;
    var marked = null;

    function collectRows() {
        var all = list.querySelectorAll('[data-sort-group][data-sort-id]');
        rows = [];
        for (var i = 0; i < all.length; i++) {
            rows.push(all[i]);
        }
    }

    function sameGroup(row, other) {
        return row.getAttribute('data-sort-group') === other.getAttribute('data-sort-group') &&
            row.getAttribute('data-sort-parent') === other.getAttribute('data-sort-parent') &&
            row.getAttribute('data-sort-block') === other.getAttribute('data-sort-block');
    }

    function orderOf(row) {
        return rows.filter(function (item) {
            return sameGroup(row, item);
        }).map(function (item) {
            return item.getAttribute('data-sort-id');
        });
    }

    function unmark() {
        if (marked) {
            marked.style.boxShadow = '';
            marked = null;
        }
    }

    function mark(row, after) {
        unmark();
        marked = row;
        row.style.boxShadow = after ? 'inset 0 -3px 0 0 currentColor' : 'inset 0 3px 0 0 currentColor';
    }

    function failed(row) {
        row.draggable = false;
        row.style.opacity = '';
        unmark();
        window.alert(errorMessage);
    }

    function save(row, ids) {
        var params = new URLSearchParams();
        params.set('action', 'backend_sort');
        params.set('value', ids.join(','));
        params.set('ids', ids.join(','));
        params.set('group', row.getAttribute('data-sort-group'));
        params.set('parent', row.getAttribute('data-sort-parent'));
        if (row.getAttribute('data-sort-block') !== null) {
            params.set('block', row.getAttribute('data-sort-block'));
        }
        params.set('csrftoken', token);

        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'},
            body: params.toString()
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        }).then(function (result) {
            if (!result || result.status !== 'ok') {
                throw new Error('not saved');
            }
            window.location.reload();
        }).catch(function () {
            failed(row);
        });
    }

    function bind(row) {
        var handle = row.querySelector('[data-sort-handle]');
        if (!handle) {
            return;
        }

        handle.addEventListener('mousedown', function () {
            row.draggable = true;
        });

        handle.addEventListener('touchstart', function () {
            row.draggable = true;
        }, {passive: true});

        // a plain click on the handle must not leave the row draggable
        document.addEventListener('mouseup', function () {
            if (!dragged) {
                row.draggable = false;
            }
        });

        row.addEventListener('dragstart', function (event) {
            if (!row.draggable) {
                event.preventDefault();
                return;
            }
            dragged = row;
            row.style.opacity = '0.4';
            event.dataTransfer.effectAllowed = 'move';
            try {
                event.dataTransfer.setData('text/plain', row.getAttribute('data-sort-id'));
            } catch (ignored) {
                // older browsers refuse custom drag data, the row is tracked in memory
            }
        });

        row.addEventListener('dragend', function () {
            row.draggable = false;
            row.style.opacity = '';
            dragged = null;
            unmark();
        });

        row.addEventListener('dragover', function (event) {
            if (!dragged || dragged === row || !sameGroup(dragged, row)) {
                return;
            }
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            var box = row.getBoundingClientRect();
            mark(row, event.clientY > box.top + box.height / 2);
        });

        row.addEventListener('dragleave', function () {
            if (marked === row) {
                unmark();
            }
        });

        row.addEventListener('drop', function (event) {
            if (!dragged || dragged === row || !sameGroup(dragged, row)) {
                return;
            }
            event.preventDefault();

            var current = dragged;
            var box = row.getBoundingClientRect();
            var after = event.clientY > box.top + box.height / 2;
            var ids = orderOf(current).filter(function (id) {
                return id !== current.getAttribute('data-sort-id');
            });
            var index = ids.indexOf(row.getAttribute('data-sort-id'));
            if (index === -1) {
                unmark();
                return;
            }
            ids.splice(after ? index + 1 : index, 0, current.getAttribute('data-sort-id'));
            unmark();
            save(current, ids);
        });
    }

    collectRows();
    rows.forEach(bind);

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
