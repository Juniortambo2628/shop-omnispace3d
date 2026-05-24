# OmniShop — Deployment Brief for Web Developer / IT

## What this is

A new event ordering portal for OmniSpace 3D Events Ltd, built in Python (Tornado framework).
It needs to be deployed on the same server as the existing OmniShop Seamless instance.

## Target URL

**https://omnishop.omnispace3d.com/solarandstorage**

(Same subdomain as the existing Seamless deployment at /seamless)

## What's in this folder

A self-contained Python web application:
- `server.py` — main app (Tornado web server)
- `db.py` — SQLite database module
- `data/catalog.py` — product catalog data
- `templates/` — HTML pages
- `static/` — CSS, images
- `requirements.txt` — two dependencies: tornado, bcrypt

## Deployment steps

1. Copy the `omnishop` folder to the server
2. Install dependencies: `pip install -r requirements.txt`
3. Run the server: `python server.py` (or via gunicorn/systemd)
4. The app listens on port 8080 by default (set `PORT` env variable to change)
5. Configure your reverse proxy (nginx/Apache) to route:
   - `omnishop.omnispace3d.com/solarandstorage` → this app
   - `omnishop.omnispace3d.com/admin` → this app's admin
   - `omnishop.omnispace3d.com/static` → this app's static files
   - `omnishop.omnispace3d.com/api` → this app's API

## Notes

- Uses SQLite — database file `omnishop.db` is created automatically on first run
- No external database server needed
- The existing Seamless instance should be unaffected (different route prefix)
- Admin login: password is `Silversky#10` (can be changed in server.py)
- If running alongside Seamless on the same server, use a different port (e.g. PORT=8081)

## Quick test

After deploying, visit:
- https://omnishop.omnispace3d.com/solarandstorage — should show the catalog
- https://omnishop.omnispace3d.com/admin — should show login page
