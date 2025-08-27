# Security Notes

- CSRF tokens required for POST forms (use `csrf_field()`).
- Cookies: HttpOnly, SameSite=Lax recommended at server level.
- Uploads: whitelist by MIME/ext; consider secure deletion for sensitive files.
- Rate limiting and auth middleware can be added per project/module.
