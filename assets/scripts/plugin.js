/**
 * @file
 * ES6 version of plugin scripts, importable without polyfills.
 */

import $ from 'jquery';

class GeneroCookieConsent {
  constructor(plugin, options = {}) {
    $(window).load(this.init.bind(this));

    const wpOptions = window._genero_cookieconsent ? window._genero_cookieconsent : {};

    this.options = Object.assign({
      onPopupOpen: this.createCallback('popup-open'),
      onPopupClose: this.createCallback('popup-close'),
      onInitialise: this.createCallback('initialise'),
      onStatusChange: this.createCallback('status-change'),
      onRevokeChoice: this.createCallback('revoke-choice'),
    }, options, wpOptions.options);

    this.plugin = plugin;

    const $document = $(document);
    $document.on('cookieconsent:status-change', this.onStatusChange.bind(this));
    $document.on('cookieconsent:revoke-choice', this.onRevokeChoice.bind(this));
    $document.on('cookieconsent:initialise', this.onInitialise.bind(this));
  }

  init() {
    if (!this.plugin && window.cookieconsent) {
      this.plugin = window.cookieconsent;
    }

    if (!this.plugin) {
      console.error('cookieconsent not available');
      return;
    }

    this.plugin.initialise(this.options);
  }

  onInitialise(event, popup, status) {
    const type = popup.options.type;

    console.debug('CookieConsent: onInitialise', type, status);

    // Opt-In users need to allow cookies.
    if (type === 'opt-in' && status === 'allow') {
      this.enableCookies();
    }
  }

  onRevokeChoice(event, popup) {
    const type = popup.options.type;

    console.debug('CookieConsent: onRevokeChoice', type);

    if (type === 'opt-in') {
      this.disableCookies();
    }
    if (type === 'opt-out') {
      this.enableCookies();
    }
  }

  onStatusChange(event, popup, status/*, chosenBefore */) {
    const type = popup.options.type;

    console.debug('CookieConsent: onStatusChange', type, status);

    if (type === 'opt-in' && status === 'allow') {
      this.enableCookies();
      location.reload();
    }
    if (type === 'opt-out' && status === 'deny') {
      this.disableCookies();
    }
  }

  createCallback(eventName) {
    const self = this;
    return function (...args) {
      console.log(args);
      self.triggerEvent(eventName, [this].concat(args));
    }
  }

  triggerEvent(eventName, args) {
    $(document).trigger(`cookieconsent:${eventName}`, args);
  }

  enableCookies() {
    console.debug('enable cookies');
  }

  disableCookies() {
    console.debug('disable cookies');
  }

    }
  }
}

export default GeneroCookieConsent;
