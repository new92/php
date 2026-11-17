# Cleaner 🧹

A **CLI-only** PHP utility for inspecting and optionally deleting the contents of a **specific subdirectory** inside your operating system's temporary folder. It is intentionally **dry-run by default**.

The previous version deleted files throughout the system temporary directory whenever its web page was visited. This version no longer does that.

## Usage

Requires **PHP 8.1+**.

Create a disposable temporary subdirectory containing only files you own and want to clean. Example (Linux/macOS):

```bash
mkdir -p "$(php -r 'echo sys_get_temp_dir();')/my-app-cache"
cd Cleaner
php cleaner.php --dir="$(php -r 'echo sys_get_temp_dir();')/my-app-cache"
```

Inspect the dry-run output carefully. When you are certain, run:

```bash
php cleaner.php --dir="$(php -r 'echo sys_get_temp_dir();')/my-app-cache" --apply
```

On Windows, determine the system temporary folder using `php -r "echo sys_get_temp_dir();"` and pass a chosen subdirectory with `--dir="C:\path\to\temp\my-app-cache"`. You can run these commands in PowerShell or Git Bash.

**Safety rules:** Cleaner refuses to run through a web server, refuses the temporary folder itself, and refuses paths outside it. Symbolic links are not followed. It deletes **contents** of the chosen directory, not the directory itself. Deletions are irreversible; always inspect the dry run first.
