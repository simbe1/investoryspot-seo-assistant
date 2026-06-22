=== InvestorySpot SEO Assistant ===
Contributors: simbe1
Tags: seo, ai, meta description, meta title, search engine optimization
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered SEO plugin using the Groq API (third-party AI service). Generate meta titles, descriptions, and get SEO analysis with one click.

== Description ==

InvestorySpot SEO Assistant brings the power of AI to your WordPress SEO workflow. It connects to the **Groq API**, a third-party AI service, to generate optimized meta titles and descriptions, analyze your content for SEO best practices, and track your SEO score — all from within the post editor.

= Features =

* **AI-Powered Meta Generation** — Generate SEO-optimized titles and descriptions with one click using Groq's fast AI models.
* **Focus Keyphrase** — Set a focus keyphrase and get AI suggestions for relevant keywords.
* **SEO Score & Analysis** — Get a visual SEO score (0-100) with detailed checklist and AI-powered improvement suggestions.
* **Content Analysis** — Automatic checks for content length, headings, images, links, keyphrase usage, and readability.
* **Post List Score Column** — See SEO scores at a glance in the posts/pages list table. Sortable and color-coded.
* **Auto-Generate** — Optionally auto-generate SEO metadata when saving posts.
* **Multiple AI Models** — Choose from Llama 3.3 70B, Llama 3.1 8B, Mixtral 8x7B, and Gemma 2 9B models.
* **Custom Post Types** — Enable SEO for any public post type.

= How It Works =

1. Get a free API key from https://console.groq.com
2. Enter the key in Settings → InvestorySpot SEO Assistant
3. Edit any post or page — the InvestorySpot SEO Assistant meta box appears below the editor
4. Set a focus keyphrase and click "Generate with AI" for instant SEO title & description
5. Click "Analyze Now" to get your SEO score and actionable suggestions

== Installation ==

1. Upload the `investoryspot-seo-assistant` folder to the `/wp-content/plugins/` directory, or install the plugin from the WordPress plugin repository.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → InvestorySpot SEO Assistant to configure your Groq API key.
4. Get a free API key at https://console.groq.com
5. Edit a post or page to start using the SEO features.

== Frequently Asked Questions ==

= Do I need an API key? =

Yes. This plugin uses the Groq API for AI-powered features. Get a free API key at https://console.groq.com. Groq offers free credits to get started.

= Is my content sent to third parties? =

Your post content is sent to Groq's API only when you click "Generate with AI" or "Analyze" buttons. It is used solely to generate SEO recommendations and is not stored.

= Which AI models are supported? =

The plugin supports Llama 3.3 70B (recommended), Llama 3.1 8B (faster), Mixtral 8x7B, and Gemma 2 9B models via Groq.

= Does this work with custom post types? =

Yes. Enable SEO for any public post type in Settings → InvestorySpot SEO Assistant.

= What about existing SEO plugins? =

InvestorySpot SEO Assistant stores its own meta fields and does not interfere with other SEO plugins.

== Privacy Notices ==

This plugin uses the **Groq API**, a third-party AI inference service.

- **Groq Website**: https://console.groq.com
- **Groq Terms of Service**: https://groq.com/terms-of-use
- **Groq Privacy Policy**: https://groq.com/privacy-policy

= What data is sent? =

When you click "Generate with AI" or "Analyze" buttons, the following data is sent to Groq:
- Your post/page content (up to 300 words)
- The focus keyphrase if set
- No personal user data, login information, or site credentials are ever sent

= When is data sent? =

Data is sent only when you explicitly click a button:
- "Generate with AI" — generates an SEO title or description
- "Analyze Now" — performs content analysis
- "Suggest" (keyphrase) — suggests relevant keyphrases
- If **Auto-generate on save** is enabled in settings, data is sent automatically when saving a post. This setting is disabled by default.

= Data storage and retention =

Groq does not store your content. It is processed in real-time only to generate the requested output and is not retained.

= Site Privacy Policy =

If you are running a public website, we recommend adding a note to your WordPress Privacy Policy indicating that your site uses the Groq API to generate SEO metadata and that post content may be sent to Groq's servers for processing when editors use the SEO tools.

== Changelog ==

= 1.0.0 =
* Initial release
* AI-powered meta title and description generation via Groq API
* Focus keyphrase with AI suggestions
* SEO score calculation and analysis
* Content checklist (length, headings, images, links, readability)
* Post list SEO score column with sorting
* Auto-generate on save option
* Multiple AI model support
* Custom post type support

== Upgrade Notice ==

= 1.0.0 =
Initial release.
