/**
 * @file
 * Browser version of plugin scripts, includign polyfills and external
 * libraries.
 */

import CookieConsent from './plugin';
import objectAssign from 'es6-object-assign';

objectAssign.polyfill();
window.GeneroCookieConsent = new CookieConsent();
