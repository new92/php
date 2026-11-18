# DirReader 📖

A small, **local-only** PHP directory browser. Lists immediate child files, directories and symbolic links, without following links or allowing access outside its configured root.

## Run locally

Requires **PHP 8.1+**.

```bash
cd DirReader
php -S 127.0.0.1:8080
```

Open <http://127.0.0.1:8080/form.html>. Enter `.` to inspect the current project directory or a relative child directory (for example `static`). Results show file and directory counts; symbolic links are listed separately.

By default the allowed root is `DirReader/`. To inspect a different **trusted local** directory, set `DIRREADER_ROOT` to an absolute path **before starting the server**:

```bash
DIRREADER_ROOT=/path/to/safe/directory php -S 127.0.0.1:8080
```

On Windows PowerShell, set the variable with `$env:DIRREADER_ROOT='C:\path\to\safe\directory'` before running `php -S 127.0.0.1:8080`.

**Security:** Run this only on your machine. Do not expose the development server to the public internet or point its allowed root at sensitive directories.


## Redesigned web interface

The web interface uses the repository-wide responsive design system, with light/dark mode, keyboard-focus styling, accessible feedback and no external UI dependencies. Pages remain usable without JavaScript. See the [GUI guide](../docs/GUI_GUIDE.md).
