<img width="1200" height="670" alt="PHP Utilities project banner" src="https://github.com/user-attachments/assets/1f6f2cb2-2933-4fb2-bf7c-ee68c6925997" />

A collection of **seven browser-based PHP applications and one command-line utility**, developed by [new92](https://github.com/new92) for web development, public-data research, filesystem operations, and practical security experimentation.

The redesigned suite features a **central project launcher**, consistent responsive interfaces, locally hosted CSS and JavaScript, accessible forms, searchable results, and optional light/dark mode. Each application remains independent and can also be launched on its own.

> [!IMPORTANT]
> These are **educational tools intended for local, authorized use**, not production-ready security services. Do not expose the PHP development server to the internet or inspect systems without permission.

## Table of contents

- [Quick start](#quick-start)
- [Projects](#projects)
- [Architecture](#architecture)
- [Requirements](#requirements)
- [Configuration](#configuration)
- [User interface](#user-interface)
- [Security and limitations](#security-and-limitations)
- [Testing and development](#testing-and-development)
- [Contributing](#contributing)
- [License](#license)

## Quick start

Clone the repository, enter its root directory, and start the PHP development server:

```bash
git clone https://github.com/new92/php.git
cd php
php -S 127.0.0.1:8080
```

Open **http://127.0.0.1:8080/** to access the workbench and choose an application. Press **Ctrl+C** in the terminal to stop the server.

You can also run each web application independently:

```bash
cd Gitspy
php -S 127.0.0.1:8080
```

Then open **http://127.0.0.1:8080/index.html**.

> [!NOTE]
> No database, Node.js runtime, frontend framework, or asset build step is needed to launch the web interfaces. Some features require PHP extensions, environment variables, or external API access, as described below.

## Projects

| Project | Purpose | Entry point |
| --- | --- | --- |
| [Gitspy](Gitspy/) | Explore public GitHub profiles, repositories, and limited follower/following previews via the GitHub API. | [Launch](Gitspy/index.html) |
| [Investigator](Investigator/) | Retrieve approximate geolocation and network metadata for a valid IPv4 or IPv6 address through ipwho.is. | [Launch](Investigator/index.html) |
| [Protector](Protector/) | Inspect HTTP status and response headers from an explicitly approved public HTTPS host. **Not a phishing detector or safety verdict.** | [Launch](Protector/input.html) |
| [Spider](Spider/) | Extract and classify up to 200 unique links from a single approved webpage, without recursive crawling. | [Launch](Spider/input.html) |
| [DirReader](DirReader/) | List files, directories, and symbolic links within a configured local root, without recursion. | [Launch](DirReader/form.html) |
| [File Uploader](File%20uploader/) | Validate and privately store JPEG, PNG, or GIF images up to 500 KB. | [Launch](File%20uploader/index.html) |
| [WeatherApp](WeatherApp/) | Show up to five days of weather forecast information using the Visual Crossing Timeline API. | [Launch](WeatherApp/index.php) |
| [Cleaner](Cleaner/) | Inspect and optionally clean a chosen subdirectory of the system temporary directory; **dry-run by default**. | **CLI only:** `php Cleaner/cleaner.php --help` |

Each application includes its own README with more detailed setup instructions. **Cleaner has no web entry point** so visiting a page cannot initiate deletion.

**Legacy scope:** Maskify and Trooper are not included in this redesigned distribution.

## Architecture

The suite shares a lightweight UI design system while keeping the PHP applications and their backend responsibilities separate.

```text
php/
├── index.html                # Central project launcher
├── ui.css                    # Source stylesheet
├── ui.js                     # Source frontend script
├── Gitspy/
├── Investigator/
├── Protector/
├── Spider/
├── DirReader/
├── File uploader/
├── WeatherApp/
├── Cleaner/                  # Command-line utility (no web GUI)
├── shared/
│   └── safe_http.php        # Constrained HTTPS requests for Protector/Spider
├── tools/
│   └── sync_ui.py           # Synchronizes local frontend assets
├── tests/                    # PHP, HTTP, shell, and GUI smoke tests
├── .github/workflows/         # Automated PHP checks
└── SECURITY.md
```

For independence, each browser application includes local copies of `static/ui.css` and `static/ui.js`. The root `ui.css` and `ui.js` are the source files; see [Testing and development](#testing-and-development) for synchronization commands.

## Requirements

- **PHP 8.1 or newer**. The included CI workflow targets PHP **8.1, 8.3, and 8.4**.
- **Git** to clone the repository and a modern web browser.
- PHP **cURL** for Gitspy, Investigator, Protector, Spider, and WeatherApp.
- PHP **DOM/XML** for Spider; **fileinfo** for File Uploader.
- Working HTTPS/TLS support for external API requests.
- Internet access for tools that query third-party services; local interfaces themselves use no external frontend dependencies.
- **Python 3** only if you want to run the optional UI-asset synchronization tool.

Verify your installation:

```bash
php -v
php -m
```

The redesigned Spider does not require Composer or its legacy Symfony dependencies to execute.

## Configuration

Set the relevant variables in the terminal **before starting** the PHP server:

| Environment variable | Project | Description |
| --- | --- | --- |
| `PHP_DEMO_ALLOWED_HOSTS` | Protector, Spider | Comma-separated list of exact public HTTPS hostnames you are authorized to inspect; **empty by default**. |
| `DIRREADER_ROOT` | DirReader | Absolute path to the permitted directory root; defaults to the DirReader project directory. |
| `PHP_DEMO_UPLOAD_DIR` | File Uploader | Private upload directory outside the web root; defaults to a dedicated folder under the operating system's temporary directory. |
| `VISUAL_CROSSING_API_KEY` | WeatherApp | Required Visual Crossing API key; read server-side. |
| `GITHUB_TOKEN` | Gitspy | Optional GitHub API token to increase applicable request limits. |

**Linux, macOS, or Windows Git Bash:**

```bash
# Replace with a hostname you own or are authorized to inspect.
export PHP_DEMO_ALLOWED_HOSTS=example.com
export VISUAL_CROSSING_API_KEY=your_api_key_here

php -S 127.0.0.1:8080
```

**Windows PowerShell:**

```powershell
$env:PHP_DEMO_ALLOWED_HOSTS = 'example.com'
$env:VISUAL_CROSSING_API_KEY = 'your_api_key_here'

php -S 127.0.0.1:8080
```

You only need to configure variables for the applications you intend to use. **Never commit tokens, credentials, local uploads, or other sensitive files.** Provider availability, limits, and terms still apply.

### Cleaner: safe command-line usage

Cleaner is intentionally separate from the web workbench. For example, on Linux/macOS or Git Bash, create a disposable subdirectory under your system temporary folder and preview its contents before deleting anything:

```bash
mkdir -p "$(php -r 'echo sys_get_temp_dir();')/my-app-cache"
php Cleaner/cleaner.php --dir="$(php -r 'echo sys_get_temp_dir();')/my-app-cache"
```

Only after carefully checking the dry-run output, use `--apply` to remove the selected directory's **contents**:

```bash
php Cleaner/cleaner.php --dir="$(php -r 'echo sys_get_temp_dir();')/my-app-cache" --apply
```

See [Cleaner/README.md](Cleaner/README.md) for Windows examples and safeguards. Deletion is irreversible.

## User interface

The redesigned GUI provides:

- A central, responsive dashboard linking to all seven browser applications.
- Consistent navigation, descriptive forms, status messages, and readable result cards.
- Client-side filtering of **already loaded** tables and lists, without performing additional server requests.
- Light/dark mode with browser-local preference storage.
- Visible keyboard focus, semantic labels, skip navigation, and reduced-motion support.
- Drag-and-drop image selection and local previews in File Uploader; **server-side validation remains authoritative**.
- Locally served fonts, styles, and scripts—**no CDN or frontend analytics dependencies**.

The PHP forms and results remain usable with JavaScript disabled. For implementation and accessibility details, see the [GUI guide](docs/GUI_GUIDE.md) and [GUI changelog](GUI_CHANGELOG.md).

## Security and limitations

The revised code includes several defensive improvements, but **these do not make the applications suitable for public or multi-user hosting**.

- **Outbound requests:** Protector and Spider use [`shared/safe_http.php`](shared/safe_http.php), which requires an explicit hostname allowlist, HTTPS on port 443, public IPv4 resolution, bounded responses, and no redirects. The guard is **not** a substitute for network-level egress filtering and authentication.
- **File operations:** DirReader confines results to a configured directory root; File Uploader checks file content and writes randomly named files to private storage by default; Cleaner refuses web execution and requires an explicit target and `--apply` to delete files.
- **Data handling:** Dynamic output is escaped in the revised result pages. Provider data can still be incomplete, outdated, or rate-limited.
- **Scope:** Protector inspects response metadata and cannot determine whether a site is safe. Spider analyzes one page only. IP geolocation is approximate, not person identification.
- **Third-party services:** Gitspy uses GitHub's API, Investigator queries ipwho.is, and WeatherApp calls Visual Crossing. Review privacy policies, rate limits, and licensing terms before use.

> [!WARNING]
> Run this workbench on `127.0.0.1` in a trusted environment. Do not expose it publicly without an independent security review and appropriate authorization, isolation, monitoring, and abuse controls. Only inspect networks or websites you own or have explicit permission to test.

Read the [security policy](SECURITY.md) and [review notes](docs/REVIEW_NOTES.md) for implementation boundaries and known limitations.

## Testing and development

Run the following from the **repository root** in Linux, macOS, or Git Bash:

```bash
# Syntax-check first-party PHP files
find . -type f -name '*.php' -not -path '*/vendor/*' -print0 | xargs -0 -n1 php -l

# Backend validation and safety tests
php tests/dirreader_test.php
bash tests/cleaner_smoke.sh
bash tests/uploader_smoke.sh
php tests/network_guard_test.php
php tests/validation_test.php
php tests/spider_parser_test.php
bash tests/web_guard_smoke.sh

# Frontend integration smoke test
bash tests/gui_smoke.sh

# Confirm that each project's UI assets match the root source
python3 tools/sync_ui.py --check
```

When changing the shared styles or script, synchronize all project copies with:

```bash
python3 tools/sync_ui.py
```

The included [GitHub Actions workflow](.github/workflows/php-checks.yml) defines automated checks for PHP 8.1, 8.3, and 8.4. Its GUI smoke test verifies pages and assets over a local server; it is **not a substitute for manual cross-browser, accessibility, security, or live API testing**.

## Contributing

Bug fixes, accessible UI enhancements, safer data handling, tests, and documentation improvements are welcome. Open a [GitHub issue](https://github.com/new92/php/issues) for general feedback. For vulnerabilities, follow [SECURITY.md](SECURITY.md) rather than publicly disclosing sensitive details.

## License

The project is distributed under the [MIT License](https://github.com/new92/php/blob/main/LICENSE). Third-party APIs and dependencies are subject to their respective licenses and terms.