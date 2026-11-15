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

const toInteger = (value) => {
    const number = Number.parseInt(value, 10);
    return Number.isInteger(number) ? number : null;
};

const buildOptions = (input) => {
    const options = {
        dateFormat: 'Y-m-d H:i:S',
        altInput: true,
        altFormat: input.dataset.datepickerAltFormat || 'Y-m-d H:i:S',
        enableTime: true,
        enableSeconds: true,
        time_24hr: input.dataset.datepicker24h !== '0',
        // flatpickr keeps the week start in its locale, not in the options
        locale: {
            firstDayOfWeek: toInteger(input.dataset.datepickerWeekstart) ?? 1,
        },
        allowInput: true,
        disableMobile: true,
        onChange: () => {
            input.dispatchEvent(new Event('change', { bubbles: true }));
        },
    };

    // time a day gets when it is picked without one, flatpickr's default is noon
    [['datepickerDefaulthour', 'defaultHour'], ['datepickerDefaultminute', 'defaultMinute'], ['datepickerDefaultseconds', 'defaultSeconds']].forEach(([attribute, option]) => {
        const value = toInteger(input.dataset[attribute]);
        if (value !== null) {
            options[option] = value;
        }
    });

    return options;
};

const initDatePickers = () => {

    if (typeof window.flatpickr !== 'function') {
        return;
    }

    document.querySelectorAll('input[data-datepicker]').forEach((input) => {

        const picker = window.flatpickr(input, buildOptions(input));

        if (picker.altInput) {
            picker.altInput.className = input.className;
            if (input.dataset.datepickerLabel) {
                picker.altInput.setAttribute('aria-label', input.dataset.datepickerLabel);
            }
        }

    });

};

// writes a canonical Y-m-d H:i:S value into a picker field from other inline
// handlers, which cannot reach the flatpickr instance themselves
window.phpwcmsDatePickerSet = (input, value) => {

    const target = typeof input === 'string' ? document.getElementById(input) : input;

    if (!target) {
        return;
    }

    if (target._flatpickr) {
        if (value) {
            target._flatpickr.setDate(value, false, 'Y-m-d H:i:S');
        } else {
            target._flatpickr.clear(false);
        }
    } else {
        target.value = value || '';
    }

    target.dispatchEvent(new Event('change', { bubbles: true }));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDatePickers);
} else {
    initDatePickers();
}
