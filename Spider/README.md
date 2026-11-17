# Spider · single-page link analyzer

Extract and categorize up to **200 unique links** from one explicitly approved HTML page. HTTPS links are classified as internal, external, or social; `mailto:` addresses are shown separately. Spider does **not** follow links or redirects.

## Run locally

Requires PHP 8.1+ with the **cURL** and **DOM/XML** extensions. Composer is **not needed for the revised Spider**, which uses built-in HTML DOM parsing instead of the checked-in legacy Symfony `vendor/` directory.

From the repository root:

```bash
# Linux, macOS or Git Bash
export PHP_DEMO_ALLOWED_HOSTS=example.com
cd Spider
php -S 127.0.0.1:8080
```

For PowerShell: `$env:PHP_DEMO_ALLOWED_HOSTS='example.com'` before starting the local PHP server.

Open <http://127.0.0.1:8080/input.html> and enter `https://example.com/`.

## Safeguards and limitations

- Host allowlist **required**; defaults to an empty list. Exact hostname matching only.
- HTTPS port 443 only, no userinfo credentials, local IP targets or redirects.
- DNS lookups require public IPv4 addresses; connections are pinned to verified IPs. IPv6-only sites are not supported.
- 4-second connect, 10-second overall timeout; 512 KiB response body cap.
- HTML parsing disables external network access and outputs escaped links; `javascript:`/`data:` links are ignored.
- One request only, no recursion. Limits do not replace authentication, quotas or network-level egress filtering for public deployments.

**Only inspect websites you own or have permission to inspect.** The existing `Spider/vendor` folder is no longer used by the new dashboard; remove the tracked legacy dependencies in a separate, reviewed cleanup commit if desired.
