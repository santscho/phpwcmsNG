/**
 * phpwcms content management system
 *
 * @author Oliver Georgi <og@phpwcms.org>
 * @copyright Copyright (c) 2002-2026, Oliver Georgi
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 * @link http://www.phpwcms.org
 *
 **/

// Turns every input[data-datepicker] into a flatpickr date and time field.
//
// The input keeps the canonical Y-m-d H:i:S value that phpwcms stores; flatpickr
// only adds a visible twin (altInput) that shows the date and time format the
// user picked in the profile, so the displayed format never depends on the
// browser locale and the submitted value never depends on the display. The
// calendar starts on the week day from the same preference.

const initDatePickers = () => {

    if (typeof window.flatpickr !== 'function') {
        return;
    }

    document.querySelectorAll('input[data-datepicker]').forEach((input) => {

        const weekstart = Number.parseInt(input.dataset.datepickerWeekstart, 10);

        const picker = window.flatpickr(input, {
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: input.dataset.datepickerAltFormat || 'Y-m-d H:i:S',
            enableTime: true,
            enableSeconds: true,
            time_24hr: input.dataset.datepicker24h !== '0',
            // flatpickr keeps the week start in its locale, not in the options
            locale: {
                firstDayOfWeek: Number.isInteger(weekstart) ? weekstart : 1,
            },
            allowInput: true,
            disableMobile: true,
            onChange: () => {
                input.dispatchEvent(new Event('change', { bubbles: true }));
            },
        });

        if (picker.altInput) {
            picker.altInput.className = input.className;
            if (input.dataset.datepickerLabel) {
                picker.altInput.setAttribute('aria-label', input.dataset.datepickerLabel);
            }
        }

    });

};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDatePickers);
} else {
    initDatePickers();
}
