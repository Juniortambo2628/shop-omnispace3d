# OmniShop - Deployment Guide

## What is OmniShop?

OmniShop is a complete e-commerce ordering website for OmniSpace 3D Events Ltd.
Exhibitors at Solar and Storage Live Kenya 2026 can browse and order rental
furniture, equipment, services, catering, and flowers for their exhibition booths.

---

## Quick Start (Run Locally)

### Prerequisites
- Python 3.8 or newer (download from python.org if you don't have it)

### Steps

1. **Open a terminal/command prompt** and navigate to the omnishop folder:
   ```
   cd omnishop
   ```

2. **Install dependencies** (one-time only):
   ```
   pip install -r requirements.txt
   ```

3. **Start the server:**
   ```
   python server.py
   ```

4. **Open your browser** and go to:
   - **Catalog:** http://localhost:8080/solarandstorage
   - **Admin:** http://localhost:8080/admin (password: Silversky#10)

---

## Deploy to the Internet

### Option A: Railway (Recommended - Easiest)

Railway is a simple hosting platform. No coding knowledge needed.

1. Go to https://railway.app and sign up with your GitHub account
2. Click "New Project" → "Deploy from GitHub repo"
3. First, push your code to GitHub:
   - Go to https://github.com/new and create a new repository called "omnishop"
   - Follow the instructions to upload your omnishop folder
4. In Railway, select your omnishop repository
5. Railway will auto-detect it's a Python app and deploy it
6. Go to Settings → Networking → Generate Domain
7. Your site will be live at something like: omnishop-production.up.railway.app

**To use your own domain (omnishop.omnispace3d.com):**
- In Railway Settings → Networking → Custom Domain
- Add: omnishop.omnispace3d.com
- Add a CNAME record in your domain DNS pointing to the Railway domain

### Option B: Render

1. Go to https://render.com and sign up
2. Click "New" → "Web Service"
3. Connect your GitHub repo
4. Settings:
   - Build Command: `pip install -r requirements.txt`
   - Start Command: `python server.py`
5. Click "Create Web Service"

### Option C: Any VPS (DigitalOcean, AWS, etc.)

```bash
# SSH into your server
ssh user@your-server-ip

# Install Python if not present
sudo apt update && sudo apt install python3 python3-pip

# Clone/upload your code
git clone your-repo-url omnishop
cd omnishop

# Install dependencies
pip install -r requirements.txt

# Run with a process manager
pip install gunicorn
gunicorn -w 4 -k tornado server:make_app --bind 0.0.0.0:8080

# Or use systemd for auto-start (create /etc/systemd/system/omnishop.service)
```

---

## Configuration

### Change the Admin Password

Edit `server.py` and change this line:
```python
"admin_password": "Silversky#10",
```

### Change the PayPal Email

Edit `server.py` and change this line:
```python
"paypal_email": "susan@susanmboya.com",
```

### Add a New Event

Edit `server.py` and add to the EVENTS dictionary:
```python
EVENTS = {
    "solarandstorage": { ... },  # existing
    "aef": {
        "name": "Africa Energy Forum 2026",
        "short_name": "AEF 2026",
        "dates": "June 23-25, 2026",
        "venue": "Barcelona, Spain",
        "logo": "/static/images/aef-logo.png",
        "contact_email": "aef@omnispace3d.com",
        "deadlines": [
            {"category": "Furniture", "deadline": "June 10, 2026"},
        ]
    }
}
```

Then access it at: yoursite.com/aef

### Change the Cookie Secret (Important for Production)

In `server.py`, the `cookie_secret` is auto-generated each time the server starts.
For production, set it to a fixed random string in an environment variable:
```python
cookie_secret=os.environ.get("COOKIE_SECRET", "your-random-secret-here"),
```

---

## Important Security Notes

1. **Change the admin password** before going live
2. **Set a fixed cookie_secret** in production
3. **Use HTTPS** - Railway and Render provide this automatically
4. **The SQLite database** (omnishop.db) stores all orders. Back it up regularly.

---

## File Structure

```
omnishop/
├── server.py              # Main server (edit config here)
├── db.py                  # Database module
├── requirements.txt       # Python dependencies
├── Procfile              # For Railway/Render deployment
├── runtime.txt           # Python version
├── data/
│   ├── __init__.py
│   └── catalog.py        # Product catalog (edit to change products)
├── static/
│   ├── css/styles.css    # Stylesheet
│   └── images/           # Logo files
└── templates/
    ├── catalog.html      # Main catalog page
    ├── checkout.html     # Checkout page
    ├── confirmation.html # Order confirmation
    └── admin/
        ├── login.html
        ├── dashboard.html
        └── packing_list.html
```

---

## Support

Built by Claude for OmniSpace 3D Events Ltd.
Contact: solarandstorage@omnispace3d.com
