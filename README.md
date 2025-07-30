# Strict CSP

Enables a Strict Content Security Policy on the frontend and login screen.

**Contributors:** [westonruter](https://profile.wordpress.org/westonruter)  
**Tags:**         security  
**Tested up to:** 6.8  
**Stable tag:**   0.3.0  
**License:**      [GPLv2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

## Description

This plugin enables a [Strict Content Security Policy](https://web.dev/articles/strict-csp) (CSP) on the frontend and login screen; the policy cannot yet be applied to the WP Admin yet (see [#59446](https://core.trac.wordpress.org/ticket/59446)).

In [#58664](https://core.trac.wordpress.org/ticket/58664), the manual construction of script tags was eliminated from `WP_Scripts` and inline scripts on frontend/login screen, thanks to the helper functions which had previously been introduced in [#39941](https://core.trac.wordpress.org/ticket/39941). This made it possibly to apply Strict CSP, as long as themes and plugins are not manually constructing scripts but rather are using the relevant APIs in WordPress for adding scripts, including `wp_enqueue_script()`, `wp_add_inline_script()`, `wp_localize_script()`, `wp_print_script_tag()`, `wp_print_inline_script_tag()`, `wp_enqueue_script_module()` among others.

This plugin also ensures that scripts added to the page from embeds (e.g. Tweets) also get the `nonce` attribute added.

## Installation

### Automatic

1. Visit **Plugins > Add New** in the WordPress Admin.
2. Search for **Strict CSP**.
3. Install and activate the **Strict CSP** plugin.
4. Log out of WordPress and log back in with the “Remember Me” checkbox checked.

You may also install and update via [Git Updater](https://git-updater.com/) using the [plugin's GitHub URL](https://github.com/westonruter/strict-csp).

### Manual

1. Download the plugin ZIP either [from WordPress.org](https://downloads.wordpress.org/plugin/strict-csp.zip) or [from GitHub](https://github.com/westonruter/strict-csp/archive/refs/heads/main.zip). Alternatively, if you have a local clone of the repo, run `npm run plugin-zip`.
2. Visit **Plugins > Add New Plugin** in the WordPress Admin.
3. Click **Upload Plugin**.
4. Select the `strict-csp.zip` file on your system from step 1 and click **Install Now**.
5. Click the **Activate Plugin** button.

## Changelog

### 0.3.0

* Add `nonce` attributes to scripts added by embeds.

### 0.2.0

* Disable Strict CSP from Site Editor.
* Restrict policy to frontend and login screen.

### 0.1.0

* Initial release.
