/**
 * @file
 * Browser version of plugin scripts, includign polyfills and external
 * libraries.
 */

import Plugin from './plugin';
import objectAssign from 'es6-object-assign';
import { CookieConsent } from 'cookieconsent';

objectAssign.polyfill();
window.GeneroCookieConsent = new Plugin(CookieConsent);
