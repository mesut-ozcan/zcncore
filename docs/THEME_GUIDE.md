# Theme Guide

- `theme.json` describes layouts/regions/assets.
- Place layouts under `views/layouts/*.php`.
- Override module/app views by mirroring relative paths in `views/overrides/{Module}/{name}.php`.
- Use `<?= head()->render() ?>` to output SEO meta.