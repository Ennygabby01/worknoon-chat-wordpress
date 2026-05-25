# Worknoon Chat WordPress

![WordPress](https://img.shields.io/badge/WordPress-Plugin-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![REST API](https://img.shields.io/badge/REST_API-Integration-0F766E)
![Shortcode](https://img.shields.io/badge/Shortcode-Widget-17324D)

WordPress plugin integration for the Worknoon realtime chat assessment.

## Technologies

- WordPress plugin API
- PHP 8.1+
- WordPress REST API
- Custom post types
- Shortcodes
- Vanilla JavaScript
- CSS

## What It Provides

- `chat_session` custom post type for local WordPress chat session records.
- `[worknoon_chat]` shortcode for embedding a floating iframe chat launcher.
- WordPress REST API routes under `/wp-json/worknoon-chat/v1`.
- Namespaced PHP classes loaded through the plugin autoloader.
- Admin settings for backend API URL, frontend app URL, widget title, default chat context, site-wide widget toggle, and widget position.
- Escaped frontend output, sanitized settings, and nonce-protected session creation.
- Uninstall cleanup for plugin options and `chat_session` records.

## Requirements

- WordPress 6.4 or newer.
- PHP 8.1 or newer.
- Authenticated WordPress user for starting a chat session.
- Worknoon frontend app URL for the iframe widget.
- Worknoon backend API URL when direct backend bridging is enabled.

## Installation

1. Copy this folder into `wp-content/plugins/worknoon-chat`.
2. Activate **Worknoon Chat** from the WordPress admin plugins screen.
3. Open **Chat Sessions > Settings**.
4. Set the frontend app URL for the active Next.js chat environment.
5. Set the backend API URL for the active environment when direct backend calls are enabled.
6. Enable **Site-wide Widget** to show the floating launcher on every public page, or add `[worknoon_chat]` to a specific page, post, or widget area.

## Local Commands

```bash
make lint
make package
make clean
```

`make lint` runs PHP syntax checks. `make package` creates `dist/worknoon-chat.zip`.

## Shortcode

```text
[worknoon_chat]
```

Optional attributes:

```text
[worknoon_chat context="support" title="Need help?"]
```

You can also override the frontend URL and floating position:

```text
[worknoon_chat url="https://chat.example.com" position="bottom-right"]
```

Supported contexts:

- `support`
- `designer`
- `merchant`

The context is passed to the frontend iframe and stored on the local `chat_session` record. It is not a WordPress post type; it simply tells the chat frontend whether to start a support, designer, or merchant flow.

Supported positions:

- `bottom-right`
- `bottom-left`

## Widget Architecture

The WordPress plugin owns the commerce-site embed surface: shortcode rendering, floating launcher, iframe container, local `chat_session` records, settings, and WordPress REST endpoints.

The Next.js frontend owns the chat product UI inside the iframe. The iframe URL receives:

- `embed=wordpress`
- `context`
- `sourceUrl`

The backend remains the source of truth for authentication, conversations, messages, read state, and realtime events.

The Next.js frontend must allow the WordPress site to frame it. In local development, configure the frontend with the WordPress origin:

```env
WORDPRESS_FRAME_ANCESTORS=http://localhost:8080,http://172.20.10.4:8080
```

Use the actual WordPress origin/port for your environment.

## Screenshots

Widget closed:

![Worknoon WordPress widget closed](https://raw.githubusercontent.com/Ennygabby01/screenshots/main/004.png)

Widget open:

![Worknoon WordPress widget open](https://raw.githubusercontent.com/Ennygabby01/screenshots/main/001.png)
![Worknoon WordPress widget conversation](https://raw.githubusercontent.com/Ennygabby01/screenshots/main/002.png)
![Worknoon WordPress widget embedded chat](https://raw.githubusercontent.com/Ennygabby01/screenshots/main/003.png)

## REST Routes

`GET /wp-json/worknoon-chat/v1/config`

Returns public widget configuration.

`POST /wp-json/worknoon-chat/v1/sessions`

Creates a private `chat_session` post for the current WordPress user. Requires a valid `X-WP-Nonce` header.

## Architecture

The plugin keeps the WordPress integration thin:

- `worknoon-chat.php` bootstraps constants, autoloading, activation, and deactivation.
- `src/PostType/ChatSessionPostType.php` registers the `chat_session` custom post type.
- `src/Settings/SettingsRepository.php` owns settings defaults and sanitization.
- `src/Admin/AdminPage.php` renders and saves plugin settings.
- `src/PublicView/ChatShortcode.php` renders the shortcode and site-wide widget.
- `src/Rest/RestController.php` exposes the public config and session-record routes.
- `assets/css` and `assets/js` contain separate admin and public widget assets.

## Challenges and Tradeoffs

- The PDF allowed either a plugin or a Storefront child theme. I chose a plugin because the required deliverables were a custom post type, shortcode, and REST API integration, and a plugin is more portable for an ecommerce site.
- I avoided rebuilding the chat UI inside WordPress. WordPress owns the embed shell, settings, `chat_session` records, and site context; the Next.js app owns the actual chat experience.
- I had to clarify shortcode context values. `support`, `designer`, and `merchant` are not WordPress post types; they are chat-entry contexts passed to the frontend and stored with the local session record.
- The site-wide toggle and shortcode needed to behave consistently. They now share the same render path: global settings drive the automatic widget, while shortcode attributes can override context, title, URL, and position per page.
- The widget initially risked clobbering an existing page scroll-lock state. It now preserves any existing inline `body` overflow value and restores it when the panel closes.
- The plugin records local sessions only for authenticated WordPress users. The backend remains the source of truth for chat users, conversations, messages, read state, and realtime behavior.

## Current Status

This plugin covers the required WordPress path from the assessment: custom post type, shortcode, and REST integration surface. The public shortcode now renders a floating iframe widget pointed at the configured Next.js frontend.

## Validation

```bash
make lint
```
