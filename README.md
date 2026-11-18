<img width="1200" height="670" alt="logo" src="https://github.com/user-attachments/assets/1f6f2cb2-2933-4fb2-bf7c-ee68c6925997" />


A collection of **10 independent PHP projects** by [new92](https://github.com/new92), built to explore server-side development, HTML/CSS interfaces, filesystem operations, public APIs, and web security concepts.

## Contents

- [Projects](#projects)
- [Requirements](#requirements)
- [Run a project locally](#run-a-project-locally)
- [Security and reliability limitations](#security-and-reliability-limitations)
- [Suggested improvements](#suggested-improvements)
- [Contributing](#contributing)
- [License](#license)

## Projects

| Project | What it does | Start page / entry point |
| --- | --- | --- |
| [Cleaner](Cleaner/) | Attempts to delete files and folders from the system temporary directory. **Destructive: not recommended to run as-is.** | `Cleaner/cleaner.php` (read the warning below) |
| [DirReader](DirReader/) | Lists entries in a directory supplied through a web form. | `form.html` |
| [File uploader](File%20uploader/) | Demonstrates uploading image files to a local `uploads/` directory. | `index.html` |
| [Gitspy](Gitspy/) | Displays publicly available GitHub account, follower, and repository information. | `index.html` |
| [Investigator](Investigator/) | Looks up IP-address geolocation and related network information using an external API. | `index.html` |
| [Maskify](Maskify/) | Demonstrates URL shortening and masked-link formatting. **Legacy; do not use for deceptive links.** | `index.html` |
| [Protector](Protector/) | Retrieves URL, server, and HTTP-header information and performs an experimental IP comparison. **Not a reliable phishing or safety detector.** | `input.html` |
| [Spider](Spider/) | Fetches a webpage and categorizes extracted links, including social, internal, and external URLs. | `input.html` |
| [Trooper](Trooper/) | Attempts to retrieve Instagram profile information from an unofficial web endpoint. | `form.html` |
| [WeatherApp](WeatherApp/) | Displays weather information requested through the Visual Crossing service on RapidAPI. | `index.php` |

Each project has its own directory and README. Some upstream services or endpoints may have changed since these examples were written.

## Requirements

- PHP **8.0 or newer** for language features used in the repository; a currently supported PHP release is recommended. The projects are **not covered by a documented version-test matrix**.
- A browser for the web-based examples.
- Network access for projects that call external APIs or websites.
- Depending on the project, the PHP **cURL**, **DOM/XML**, and **OpenSSL** extensions, plus `allow_url_fopen` for scripts using HTTP(S) stream wrappers.
- Git to clone the repository. Spider includes a committed `vendor/` directory with Symfony-related dependencies; review those dependencies before use.

Check your local PHP installation:

```bash
php -v
php -m
```

No database or application framework is required for the repository as a whole.

## Run a project locally

Clone the repository:

```bash
git clone https://github.com/new92/php.git
cd php
```

For example, to open **Gitspy**:

```bash
cd Gitspy
php -S 127.0.0.1:8080
```

Visit <http://127.0.0.1:8080/index.html> in your browser. Press **Ctrl+C** to stop the server.

For another project, start PHP from **that project's directory** and open its entry point from the table above. For example, DirReader uses `/form.html`, while WeatherApp uses `/index.php`. Many projects also contain a `localhost.php` helper, but starting PHP directly makes the local bind address explicit.

> [!WARNING]
> **Local development only.** PHP's built-in server is not a production server. Bind it to `127.0.0.1`; do not expose these demos on the public internet or a shared network. A localhost bind does **not** correct vulnerabilities in the application itself. Prefer an isolated development environment and avoid sensitive data.

### Project-specific setup

- **File uploader:** `upload.php` expects an `uploads/` folder under the project directory. The current implementation checks filename extensions and a size limit, but does **not** perform robust file-content validation or use private, randomly named storage. Do not expose it publicly.
- **WeatherApp:** `WeatherApp/index.php` currently uses the Visual Crossing **RapidAPI** endpoint and contains an `X-RapidAPI-Key: <API_KEY>` placeholder. The `VISUAL_CROSSING_API_KEY` environment variable is **not implemented** in the current script. Refactor the code to read a key from the environment before using a real credential; never commit API keys.
- **Gitspy:** Uses unauthenticated GitHub API requests, which may be rate-limited. The current code does **not** use a `GITHUB_TOKEN` environment variable.
- **Investigator:** Uses `ip-api.com` over **HTTP**, not `ipwho.is` or a guaranteed HTTPS connection. IP geolocation is approximate and does not identify a person.
- **Spider:** Uses the bundled Symfony BrowserKit/HttpClient/DomCrawler dependencies. It does **not** currently implement a target-host allowlist or the proposed `PHP_DEMO_ALLOWED_HOSTS` setting. Request only pages you are permitted to inspect.
- **Cleaner:** **Do not run this script without reviewing and rewriting its deletion behavior.** It attempts to remove contents of the system temporary directory immediately, without confirmation or a dry run; `--help` is not implemented. It also uses Windows-style path separators, which can cause portability problems.

## Security and reliability limitations

The repository is experimental. **Do not assume the following protections exist** in the current code:

- **Input validation and output encoding:** Several scripts handle form fields or API responses directly, without consistent validation or HTML escaping.
- **Filesystem restrictions:** DirReader accepts a user-provided path without an enforced directory allowlist. The uploader saves files using their supplied names in a web-accessible directory. Cleaner can delete files without a preview.
- **Safe outbound requests:** Protector and Spider do not provide a shared, allowlist-driven HTTP client or comprehensive server-side request forgery (SSRF) defenses. Some legacy scripts disable TLS certificate verification.
- **Trustworthy safety verdicts:** Protector's IP-address comparison cannot establish whether a website is legitimate or free from phishing and malware. Its displayed result is **not** a security assessment.
- **Guaranteed external API support:** Third-party endpoints can change, reject requests, enforce rate limits, or require authentication. Trooper relies on an unofficial Instagram endpoint; Maskify relies on a URL-shortening service.
- **Automated test coverage:** The current repository does not include the proposed `tests/` suite, GitHub Actions workflow, `shared/` HTTP client, `SECURITY.md`, or `docs/REVIEW_NOTES.md`.

Use these tools only for lawful, authorized experimentation. Do not submit third-party sites to network-inspection scripts without permission, share misleading masked links, or rely on any result for consequential security decisions.

## Suggested improvements

The following are **recommendations, not implemented features**:

1. Make Cleaner CLI-only, constrain its target directory, and require an explicit dry-run review before deletion.
2. Add strict path validation and output escaping; keep uploaded files outside the web root and validate actual file content.
3. Add outbound HTTPS allowlists, SSRF protections, TLS verification, redirect restrictions, and network timeouts.
4. Move API credentials into environment variables and handle missing or rate-limited responses gracefully.
5. Review and update Spider's dependencies, then add reproducible tests and continuous integration.
6. Clearly distinguish informational URL metadata from verified security findings.

## Contributing

Bug reports, security fixes, documentation updates, accessibility improvements, and tests are welcome. Please avoid committing credentials, uploaded files, temporary output, or other sensitive information. Open an [issue](https://github.com/new92/php/issues) for general feedback; avoid posting vulnerability details or secrets publicly.

## License

Distributed under the [MIT License](LICENSE). Third-party packages and external services have their own licenses and terms of use.
