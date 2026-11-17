# Code improvement notes — second pass

This package **includes the first improvement patch** (Cleaner, DirReader and File uploader) plus the second-pass improvements below. It is an **overlay patch**, not the full repository.

## Completed in this package

1. **Protector:** Removed IP-equality safety verdicts and unsafe TLS settings. Implemented an allowlist-controlled HTTPS HEAD inspector with bounded headers, no iframe preview, no shared header export and a clear non-certification notice.
2. **Spider:** Removed the old Symfony execution dependency for its dashboard. A shared network guard validates and pins public IPv4 destinations; links are parsed via DOM/XML, capped, classified by real domain boundaries and escaped.
3. **Gitspy:** Validates usernames; queries fixed GitHub API routes; handles missing accounts, rate limits and network failures; escapes descriptions/names and builds safe links.
4. **Investigator:** Uses the fixed HTTPS ipwho.is endpoint after validating IPv4/IPv6 input. Escapes output and explains third-party disclosure and approximate results.
5. **WeatherApp:** Uses a supported Visual Crossing HTTPS Timeline API request, city encoding, JSON parsing, safer network options and a server-side `VISUAL_CROSSING_API_KEY`.
6. **CI/docs:** Added tests for network validators and input handling, plus `.github` dependency update configuration and a repository security policy.

## Remaining and out of scope

- **Maskify**: legacy link obfuscation functionality and disabled TLS validation. Can be misused for phishing. Do not deploy as a deceptive link generator. Consider a transparent URL expander/preview in a future separate project.
- **Trooper**: legacy Instagram endpoint, unescaped profile content and unhandled API changes. Requires separate refactoring and privacy/terms review.
- **Protector/Spider**: if ever hosted publicly, add authentication, per-user rate limits, SSRF-resistant **network egress controls**, sandboxing, monitoring and explicit approved-target policies. A PHP-side allowlist is not an assurance of complete SSRF immunity.
- **Gitspy/Investigator/WeatherApp**: API quotas, uptime and exact response schema require live provider integration tests; current tests are offline and do not exercise external APIs.
- **Dependencies**: `Spider/vendor/` remains tracked in the original repository. The revised Spider doesn't use it; review then remove via `git rm -r -- Spider/vendor`. No deletion is performed by copying the package.
- **Deployment**: the PHP built-in web server is for local development only. Multi-user production hardening is not covered.

## Testing summary

Offline PHP syntax lint, validator tests, filesystem and uploader tests, and HTTP invalid-input smoke tests are included in the ZIP. cURL and DOM extensions were not installed in the packaging runtime; real HTTPS data fetching and DOM parsing should also be checked in an environment with those extensions before deploying.
