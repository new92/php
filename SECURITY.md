# Security policy

This repository collects educational PHP applications. They are not production services and are not covered by a professional security assurance program. **Do not expose these demos publicly.**

## Reporting a vulnerability

Email **new92github@gmail.com** with the affected project/path, reproducible steps, the expected and observed behavior, and an assessment of impact. Avoid publishing exploitable details or personal data in public issues. Do not test unrelated systems or third-party accounts without authorization.

## Safeguards in the modernized examples

- The constrained HTTPS client defaults to **no outbound allowlisted hosts**. The operator must explicitly permit exact public hostnames with `PHP_DEMO_ALLOWED_HOSTS`.
- Only HTTPS port 443 is permitted. Redirects, userinfo, IP-literal URLs and destinations resolving to nonpublic IPv4 addresses are rejected. The final connection is pinned to the checked IPv4 addresses.
- A network firewall / container egress restriction remains necessary for public multi-user applications. The PHP client alone is not a substitute for network isolation, rate limits, quotas or authentication.
- HTML output is escaped, untrusted remote pages are not rendered, and request responses have size/time limits.
- API tokens must be stored in the environment, not in source control.

## Known limitations

- **Protector** reports HTTP metadata only, **not a safety verdict**. A valid certificate and public IP are not evidence that a website is legitimate.
- **Spider** extracts links from one page, without recursive crawling, and does not handle IPv6-only origins.
- **Gitspy** uses GitHub API data, subject to rate limits and display limits.
- **Investigator** sends submitted IP addresses to a third party and reports approximate metadata, not attribution.
- **WeatherApp** depends on a third-party paid/free weather API and its terms.
- **Maskify and Trooper** retain legacy behavior and still require dedicated design and security reviews before public use.
- Filesystem utilities and upload storage lack multi-user access control; run only on loopback.

Please treat all contributions as potentially security-sensitive, especially changes to filesystem paths, URL handling, HTML rendering and network access.
