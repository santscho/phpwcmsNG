/**
 * phpwcms content management system
 *
 * Bootstrap in the backend frame.
 *
 * MooTools 1.1 replaces two globals Bootstrap works with directly:
 *
 * 1. `window.Event` - MooTools wraps a native event (`new Event(nativeEvent)`),
 *    Bootstrap dispatches custom events with the native signature
 *    (`new Event('show.bs.tooltip', {bubbles: true})`). Event is bridged: a
 *    string first argument builds a native event, anything else still goes
 *    through the MooTools constructor.
 * 2. `window.Element` - MooTools' constructor object has no
 *    `querySelector`/`querySelectorAll`/`closest`, which Bootstrap's
 *    SelectorEngine calls as `Element.prototype.querySelector.call(...)`. The
 *    three DOM methods are added to it, non-enumerable so MooTools does not pick
 *    them up when it extends its own elements.
 *
 * After that the backend's Bootstrap tooltips can be initialised: they are opt-in,
 * so every `[data-bs-toggle="tooltip"]` element is set up here.
 *
 * The Bootstrap bundle is only reachable as an ES module, hence this file.
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU GPL-2
 */

import { Tooltip } from '../inc_ext/bootstrap/bootstrap.bundle.min.js';

const nativeEvent = document.createEvent('Event').constructor;
const nativeElementPrototype = Object.getPrototypeOf(document.createElement('div'));

if (window.Event !== nativeEvent) {
    const LegacyEvent = window.Event;

    function BackendEvent(event, config) {
        if (typeof event === 'string') {
            return new nativeEvent(event, config || {});
        }
        return new LegacyEvent(event, config);
    }

    BackendEvent.prototype = LegacyEvent.prototype;
    Object.keys(LegacyEvent).forEach(function (name) {
        BackendEvent[name] = LegacyEvent[name];
    });

    window.Event = BackendEvent;
}

if (window.Element && window.Element !== nativeElementPrototype.constructor) {
    ['closest', 'querySelector', 'querySelectorAll'].forEach(function (name) {
        if (typeof window.Element.prototype[name] !== 'function') {
            Object.defineProperty(window.Element.prototype, name, {
                configurable: true,
                writable: true,
                value: nativeElementPrototype[name]
            });
        }
    });
}

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
    Tooltip.getOrCreateInstance(element);
});
