=== Omega Guard ===
Contributors: omegapointsolutions
Tags: security, brand protection, anti-phishing, clone detection, anti-piracy
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.0.0
License: Proprietary
License URI: https://omega-guard.omegapointsolutions.com

Protects your site from clones and impostors — injects your Omega Guard canary so a copied site is detected the moment it loads.

== Description ==

Omega Guard is brand-protection and clone-site detection by Omega Point Solutions LLC. Scammers and phishers routinely copy real business websites to steal customers, harvest credentials, and damage your brand. Omega Guard plants an invisible "canary" in your pages so that the instant a stolen copy of your site is loaded anywhere on the internet, you find out.

There is no code to write. Install the plugin, paste the canary token from your Omega Guard dashboard, and you are protected.

**What it does**

* Injects a lightweight canary into your site's `<head>` — a beacon favicon, a hidden 1x1 pixel, and an off-domain detection script.
* The detection script only "phones home" when your page is loaded on a hostname that is NOT yours — meaning it stays silent on your real site and fires when someone clones you.
* Optionally reports server-side recon activity (clone tools like HTTrack, Wget, SingleFile, headless browsers, and scrapers hitting your site) so you get early warning before a clone even goes live.
* All reporting is non-blocking and rate-limited — it never slows down or blocks your pages for real visitors.

**Privacy & performance**

The canary adds only a few hundred bytes to your pages. The off-domain script does nothing on your legitimate domain (and its www / non-www pair). Recon reporting only triggers for known clone/scrape tools and is throttled per IP, so it never floods your server or Omega Guard.

This plugin is generic: the same plugin works for every Omega Guard customer. Your site-specific protection comes from the token you paste in settings.

== Installation ==

1. In your WordPress admin, go to **Plugins → Add New → Upload Plugin** and upload the Omega Guard zip file (or copy the `omega-guard` folder into `wp-content/plugins/`).
2. Click **Activate**.
3. Go to **Settings → Omega Guard**.
4. Open your Omega Guard dashboard → **Protection Kit**, copy your **Canary token**, and paste it into the Canary token field.
5. (Optional) Copy your **Portal token** from the same place and paste it in to enable server-side recon reporting.
6. Click **Save protection settings**. You will see a green "This site is protected" confirmation.

That's it — your site is now protected against clones.

== Frequently Asked Questions ==

= Do I need to be technical to use this? =

No. Install, paste your token, save. There is nothing to edit in your theme or code.

= Where do I get my tokens? =

From your Omega Guard dashboard at https://omega-guard.omegapointsolutions.com — open the **Protection Kit** section. The Canary token is required; the Portal token is optional.

= Will this slow down my site? =

No. The canary is a few hundred bytes. The detection script does nothing on your real domain, and all reporting is fire-and-forget (non-blocking) and rate-limited.

= How does clone detection actually work? =

Omega Guard embeds invisible markers tied to your token. When a copied version of your page loads on a domain that isn't yours, the canary signals Omega Guard with the impostor's hostname. You are alerted so you can act (takedown, warning, evidence collection).

= Does it collect data on my normal visitors? =

The off-domain script intentionally stays silent on your own domain, so normal visitors are not beaconed by it. The optional recon report only fires for recognized clone/scrape tools (e.g. HTTrack, Wget, headless browsers), not for ordinary browsers.

= Is my token a secret? =

Treat your Portal token as a secret (it authorizes recon reporting). The Canary token is embedded in your public pages by design — that is how clones are detected.

= What happens if I deactivate the plugin? =

The canary is no longer injected and recon reporting stops. Your saved tokens remain so you can re-activate anytime.

== Changelog ==

= 1.0.0 =
* Initial release.
* Settings page under Settings → Omega Guard (Canary token + optional Portal token) via the WordPress Settings API.
* Canary injection on `wp_head`: beacon favicon, hidden 1x1 pixel, and off-domain detection script with a same-site allowlist (www / non-www aware).
* Optional non-blocking, rate-limited server-side recon reporting for clone/scrape tools and empty user agents.
* Secure throughout: capability checks, nonce-protected settings, sanitized input, escaped output.

== Upgrade Notice ==

= 1.0.0 =
First public release of Omega Guard for WordPress.
