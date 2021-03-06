/**
 * @file
 * ES6 version of plugin scripts, importable without polyfills.
 */

import $ from 'jquery';

class GeneroCookieConsent {
  constructor(plugin, options = {}) {
    const wpOptions = window._genero_cookieconsent ? window._genero_cookieconsent : {};

    this.options = Object.assign({
      onPopupOpen: this.createCallback('popup-open'),
      onPopupClose: this.createCallback('popup-close'),
      onInitialise: this.createCallback('initialise'),
      onStatusChange: this.createCallback('status-change'),
      onRevokeChoice: this.createCallback('revoke-choice'),
    }, options, wpOptions.options);

    this.plugin = plugin;
    this.gaTracker = window.ga || window.__gaTracker;

    const $document = $(document);
    $document.on('cookieconsent:status-change', this.onStatusChange.bind(this));
    $document.on('cookieconsent:revoke-choice', this.onRevokeChoice.bind(this));
    $document.on('cookieconsent:initialise', this.onInitialise.bind(this));
    $document.on('cookieconsent:popup-open', this.onPopupOpen.bind(this));
    $document.ready(this.init.bind(this));
  }

  init() {
    if (!this.plugin && window.cookieconsent) {
      this.plugin = window.cookieconsent;
    }

    if (!this.plugin) {
      console.error('cookieconsent not available');
      this.enableCookies();
      return;
    }

    this.plugin.initialise(this.options);
  }

  onInitialise(event, popup, status) {
    const type = popup.options.type;
    this.track('init', status);

    // Opt-In users need to allow cookies.
    if (type === 'opt-in' && status === 'allow') {
      this.enableCookies();
    }
    if (type === 'info') {
      this.enableCookies();
    }

    if (this.options.alwaysShowRevoke) {
      window.requestAnimationFrame(() => popup.toggleRevokeButton(true));
    }
  }

  onPopupOpen(event, popup) {
    this.track('init', 'show');

    // If info, clicking anywhere gives consent.
    if (popup.options.type === 'info' && !popup.hasAnswered()) {
      $(document).one('mousedown keyup touchstart', 'a:not(.cc-link):not(.cc-dismiss), button, input[type=submit]', () => {
        popup.setStatus('dismiss');
      });
    }
  }

  onRevokeChoice(event, popup) {
    const type = popup.options.type;
    const status = (type === 'opt-in' ? 'deny': (type === 'opt-out' ? 'allow' : 'dismiss'));
    this.track('revoke', status);

    if (type === 'opt-in') {
      this.disableCookies();
      location.reload();
    }
    if (type === 'opt-out') {
      this.enableCookies();
    }
  }

  onStatusChange(event, popup, status/* , chosenBefore */) {
    const type = popup.options.type;
    this.track('click', status);

    if (status === 'allow') {
      this.enableCookies();
    }
    if (status === 'deny') {
      this.disableCookies();
      location.reload();
    }
    if (type === 'info' && status === 'dismiss') {
      this.enableCookies();
    }
  }

  createCallback(eventName) {
    const self = this;
    return function (...args) {
      self.triggerEvent(eventName, [this].concat(args));
    }
  }

  triggerEvent(eventName, args) {
    console.debug(`CookieConsent: trigger cookieconsent:${eventName}`, args);
    $(document).trigger(`cookieconsent:${eventName}`, args);
  }

  enableCookies() {
    this.triggerEvent('allow');
    this.track('allow');
  }

  disableCookies() {
    this.triggerEvent('deny');
    this.track('deny');
  }

  track(action, label) {
    const category = 'cookieconsent';
    if (window.Gevent) {
      window.Gevent(category, action, label);
    } else {
      if (this.gaTracker && typeof this.gaTracker === 'function') {
        this.gaTracker('send', 'event', category, action, label);
      }
      if (typeof dataLayer !== 'undefined') {
        window.dataLayer.push({
          event: `${category}.${action}`,
          category: category,
          action: action,
          label: label || '',
        });
      }
    }
  }
}

export default GeneroCookieConsent;
