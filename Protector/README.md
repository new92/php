# Protector · HTTPS response inspector

Educational tool for inspecting HTTP status and response headers of an **explicitly allowlisted public HTTPS website**. It **does not detect phishing** or claim a site is safe based on IP addresses, TLS or headers.

## Requirements and local run

PHP 8.1+ with the **cURL** extension. From the repository root:

```bash
# macOS / Linux / Git Bash; only test sites you own or have permission to inspect
export PHP_DEMO_ALLOWED_HOSTS=example.com
cd Protector
php -S 127.0.0.1:8080
```

In **Windows PowerShell** set `$env:PHP_DEMO_ALLOWED_HOSTS='example.com'` before launching `php -S 127.0.0.1:8080` in `Protector`.

Open <http://127.0.0.1:8080/input.html> and enter `https://example.com/`. Multiple exact hosts can be comma-separated. No wildcard or suffix matching is performed. By default **nothing is allowlisted**.

## Security model

- Eliminates the misleading shared-IP safety verdict.
- Removes disabled SSL checks, arbitrary-host requests, iframe previews, and shared `static/headers.txt` exports.
- Rejects IP-literal, local, non-HTTPS, unallowlisted and nonstandard-port URLs.
- Rejects private/reserved DNS results and pins validated public IPv4 addresses for each connection.
- Blocks redirects, uses short timeouts, caps responses and escapes all output.
- Fetches **headers only**, without rendering remote HTML.

**Limitations:** This is not a complete defense for a publicly accessible multi-user URL inspector. Enforce network-level egress filtering, authentication and quotas before public deployment. No reputation or malware scanning is performed.


## Redesigned web interface

The web interface uses the repository-wide responsive design system, with light/dark mode, keyboard-focus styling, accessible feedback and no external UI dependencies. Pages remain usable without JavaScript. See the [GUI guide](../docs/GUI_GUIDE.md).
