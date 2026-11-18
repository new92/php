#!/usr/bin/env python3
"""Keep per-project assets identical to the repository-root UI source.

Use --check in CI to detect divergence. Offline and standard-library-only.
"""
from __future__ import annotations
import argparse
from pathlib import Path

BASE = Path(__file__).resolve().parent.parent
PROJECTS = ("DirReader", "File uploader", "Gitspy", "Investigator", "Protector", "Spider", "WeatherApp")
FILES = ("ui.css", "ui.js")

def run(check: bool) -> int:
    pending: list[Path] = []
    for project in PROJECTS:
        directory = BASE / project / "static"
        for name in FILES:
            source = BASE / name
            target = directory / name
            if not target.is_file() or target.read_bytes() != source.read_bytes():
                pending.append(target.relative_to(BASE))
                if not check:
                    directory.mkdir(parents=True, exist_ok=True)
                    target.write_bytes(source.read_bytes())
    if pending:
        message = "UI assets out of sync" if check else "Updated UI assets"
        for p in pending:
            print(f"{message}: {p}")
    else:
        print("UI assets are synchronized across all seven projects.")
    return 1 if check and pending else 0

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true", help="check without writing assets")
    args = parser.parse_args()
    raise SystemExit(run(args.check))
