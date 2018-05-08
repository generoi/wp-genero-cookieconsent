# wp-genero-cookieconsent

> A wordpress plugin providing a cookie consent popup using [Cookie Consent by Insites](https://cookieconsent.insites.com).

## Installation

Regular

## Requirements

ACF is required to get an options page but if not available you can provide options using filters.

## Features

- Has layouts for Foundation Reveal and Callout
- Basic fallback for sites without Foundation
- WP Super Cache integration
- Supports Opt in, Opt out and notice
- Supports custom messages and button texts
- Uses WordPress default Privacy Policy if available but can be set to a custom page.
- Triggers events to GA and GTM

## API

```php
// Modify values sent to JS.
add_filter('wp-genero-cookieconsent/options', function ($options) {
  $options['options']['enabled'] = !WP_DEBUG;
  return $options;
});
```

## Events

### Google Analytics

|category | action | label | description|
|---------|--------|-------|------------|
|`cookieconsent`|`init`|`dismiss/allow/deny/show`|Triggered as soon as the plugin is initialized|
|`cookieconsent`|`enabled`||Triggered if cookies should be enabled|
|`cookieconsent`|`disabled`||Triggered if cookies should be disabled|
|`cookieconsent`|`revoke`|`allow/deny/dismiss`|Triggered when a choice is revoked. Label will be set to the new consent status|
|`cookieconsent`|`click`|`allow/deny/dismiss`|Triggered when the user clicks a popup button|

### Google Tag Manager

The events are sent as `<category>.<action>`, for example: `cookieconsent.enable`.

## GDPR

|Category|Cookie|Description|
|--------|------|-----------|
|Strictly necessary|`cookieconsent_status`|Saves the consent status that has been given so we know what we can show the user. Has a lifetime of 1 year.|

|Third pary|Type|Description|
|----------|----|-----------|
|-|-|-|

## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run test

Build assets

    # Minified assets which are to be committed to git
    npm run build

    # Development assets while developing the plugin
    npm run build:development

    # Watch for changes and re-compile while developing the plugin
    npm run watch

Rebuild POT files (copy to each language as `languages/wp-genero-cookieconsent-<langcode>.po` and translate it)

    npm run lang:pot

Compile MO files (requires `msgfmt` which is available with  `brew install gettext && brew link gettext --force`)

    npm run lang:mo
