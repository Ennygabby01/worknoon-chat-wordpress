# Worknoon Chat WordPress

WordPress plugin integration for the Worknoon realtime chat assessment.

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
3. Open **Settings > Worknoon Chat**.
4. Set the frontend app URL for the active Next.js chat environment.
5. Set the backend API URL for the active environment when direct backend calls are enabled.
6. Enable **Site-wide Widget** to show the floating launcher on every public page, or add `[worknoon_chat]` to a specific page, post, or widget area.

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

## REST Routes

`GET /wp-json/worknoon-chat/v1/config`

Returns public widget configuration.

`POST /wp-json/worknoon-chat/v1/sessions`

Creates a private `chat_session` post for the current WordPress user. Requires a valid `X-WP-Nonce` header.

## Current Status

This plugin covers the required WordPress path from the assessment: custom post type, shortcode, and REST integration surface. The public shortcode now renders a floating iframe widget pointed at the configured Next.js frontend.
