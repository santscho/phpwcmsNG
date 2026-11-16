/**
 * phpwcms content management system
 *
 * Two factor code fields.
 *
 * The Bootstrap otp input renders the code as its own slots, so a recovery code
 * cannot be typed into it. The recovery field next to it is the alternative:
 * once something is typed there, the code control stops being required.
 *
 * A field with `data-2fa-recovery="<id>"` belongs to the code control with that
 * id, and opening its `<details>` puts the cursor there.
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 */

document.querySelectorAll('[data-2fa-recovery]').forEach(function (field) {
    const code = document.getElementById(field.getAttribute('data-2fa-recovery'));

    if (!code) {
        return;
    }

    const sync = function () {
        code.required = field.value.trim() === '';
    };

    field.addEventListener('input', sync);
    sync();

    const details = field.closest('details');

    if (details) {
        details.addEventListener('toggle', function () {
            if (details.open) {
                field.focus();
            }
        });
    }
});
