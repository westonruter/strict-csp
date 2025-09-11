# Strict CSP

Enforces a Strict Content Security Policy on the frontend and login screen to help mitigate any XSS vulnerabilities.

<!-- markdownlint-disable-next-line no-inline-html -->
<img src=".wordpress-org/banner.svg" alt="Banner for the Strict CSP plugin" width="1544" height="500">

**Contributors:** [westonruter](https://profile.wordpress.org/westonruter)  
**Tags:**         security  
**Tested up to:** 6.8  
**Stable tag:**   0.3.2  
**License:**      [GPLv2 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)

## Description

This plugin enforces a [Strict Content Security Policy](https://web.dev/articles/strict-csp) (CSP) on the frontend and login screen. This helps mitigate [cross-site scripting](https://developer.mozilla.org/en-US/docs/Web/Security/Attacks/XSS) (XSS) vulnerabilities. The policy cannot yet be applied to the WP Admin (see [#59446](https://core.trac.wordpress.org/ticket/59446)).

In [#58664](https://core.trac.wordpress.org/ticket/58664), the manual construction of script tags was eliminated from `WP_Scripts` and inline scripts on frontend/login screen, thanks to the helper functions which had previously been introduced in [#39941](https://core.trac.wordpress.org/ticket/39941).  This made it possible to apply Strict CSP, as long as themes and plugins are not directly printing `<script>` tags. Some bundled WordPress core themes [still do this](https://github.com/search?q=repo%3AWordPress%2Fwordpress-develop+path%3A%2F%5Esrc%5C%2Fwp-content%5C%2Fthemes%5C%2F%2F+%2F%3Cscript%5B%5E%3E%5D*%3E%2F&type=code) incorrectly (which has been reported in Trac as [#63806](https://core.trac.wordpress.org/ticket/63806)). For example, do not do this:

```php
function my_theme_supports_js() {
	echo '<script>document.body.classList.remove("no-js");</script>'; // ❌
}
add_action( 'wp_footer', 'my_theme_supports_js' );
```

Instead, do this:

```php
function my_theme_supports_js() {
	wp_print_inline_script_tag( 'document.body.classList.remove("no-js");' ); // ✅
}
add_action( 'wp_footer', 'my_theme_supports_js' );
```

So in order for scripts to execute, they must be printed using the relevant APIs in WordPress for adding scripts, including [`wp_enqueue_script()`](https://developer.wordpress.org/reference/functions/wp_enqueue_script/), [`wp_add_inline_script()`](https://developer.wordpress.org/reference/functions/wp_add_inline_script/), [`wp_localize_script()`](https://developer.wordpress.org/reference/functions/wp_localize_script/), [`wp_print_script_tag()`](https://developer.wordpress.org/reference/functions/wp_print_script_tag/), [`wp_print_inline_script_tag()`](https://developer.wordpress.org/reference/functions/wp_print_inline_script_tag/), and [`wp_enqueue_script_module()`](https://developer.wordpress.org/reference/functions/wp_enqueue_script_module/). Otherwise, a script's execution will be blocked and an error will appear in the console, for example:

> Refused to execute inline script because it violates the following Content Security Policy directive: "script-src 'nonce-9b539cfe47' 'unsafe-inline' 'strict-dynamic' https: http:". Note that 'unsafe-inline' is ignored if either a hash or nonce value is present in the source list.

This also blocks scripts inside of [event handler attributes](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes#event_handler_attributes), such as `onclick`, `onchange`, `onsubmit`, and `onload`. As noted on MDN:

> Warning: The use of event handler content attributes is discouraged. The mix of HTML and JavaScript often produces unmaintainable code, and the execution of event handler attributes may also be blocked by content security policies.

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

### 0.3.2

* Use `wp_generate_password()` to create CSP nonce instead of using `wp_create_nonce()`. Props [kasparsd](https://profiles.wordpress.org/kasparsd/). ([#13](https://github.com/westonruter/strict-csp/pull/13))

### 0.3.1

* Update required PHP version to 7.2 instead of 8.1.

### 0.3.0

* Add `nonce` attributes to scripts added by embeds.

### 0.2.0

* Disable Strict CSP from Site Editor.
* Restrict policy to frontend and login screen.

### 0.1.0

* Initial release.
