# ChatGPT Context — ZCNCore

Purpose: Minimal Saf PHP core with Router, View resolver (theme>module>app), CSRF, Events,
Logger, Cache, SEO head & sitemap/robots registries. Modules are self-contained features.

When generating code:
- Respect public API surface in docs/ARCHITECTURE.md
- Prefer adding new capability as a **module**, not core.
- Provide full file contents for any edits; avoid partial diffs when possible.
