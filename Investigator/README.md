# Investigator · public IP metadata

Look up approximate public IP geolocation and network metadata using the encrypted [ipwho.is](https://ipwhois.io/documentation) API. IP geolocation does **not** identify a person or their precise physical location.

## Run locally

Requires PHP 8.1+ with **cURL**. From the repository root:

```bash
cd Investigator
php -S 127.0.0.1:8080
```

Open <http://127.0.0.1:8080/index.html>. Enter a valid IPv4 or IPv6 address, such as `8.8.8.8`.

## Improvements and privacy

- Validates IP input before constructing a URL to a **fixed** third-party API host.
- Uses HTTPS with certificate verification, timeouts, disabled redirects and a response-size cap.
- Parses JSON with explicit error handling; escapes all displayed fields.
- Reports approximate network metadata without claiming geolocation is exact.
- Entered IP is transmitted to the lookup provider; consult its data policy and rate limits. The free service may be restricted to non-commercial uses.

The provider can return unavailable data or rate-limit requests; the page will show a clear error rather than a guessed result.


## Redesigned web interface

The web interface uses the repository-wide responsive design system, with light/dark mode, keyboard-focus styling, accessible feedback and no external UI dependencies. Pages remain usable without JavaScript. See the [GUI guide](../docs/GUI_GUIDE.md).
