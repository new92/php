# Gitspy · public GitHub profile explorer

Explore public GitHub account details, repositories, followers and following through the official HTTPS GitHub REST API.

## Run locally

Requires PHP **8.1+ with cURL**. From the repository root:

```bash
cd Gitspy
php -S 127.0.0.1:8080
```

Open <http://127.0.0.1:8080/index.html>. Enter a GitHub username such as `octocat`.

**Optional API token** for larger rate limits (do not commit tokens):

```bash
# Set in your terminal BEFORE starting the PHP server
export GITHUB_TOKEN=your_personal_token
```

PowerShell: `$env:GITHUB_TOKEN='your_personal_token'`. The token is sent only to `api.github.com` over verified HTTPS. Gitspy works without a token subject to GitHub's unauthenticated rate limits.

## Improvements

- Validates usernames before creating URLs, and only requests fixed GitHub API paths.
- Escapes remote names, bios, repository descriptions and other displayed data.
- Builds outbound links from validated account and repository names rather than trusting remote HTML.
- Uses HTTPS certificate verification, timeouts and a response-size bound.
- Handles API errors and rate limiting gracefully.
- Displays total follower/repository counts from the account profile, clearly distinguishing the 30-item page previews.

Public GitHub API fields and quotas may change. This is a small educational demo, not an exhaustive analytics tool.
