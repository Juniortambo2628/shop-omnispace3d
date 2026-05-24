# OmniShop — Plain English Guide

---

## What is OmniShop?

OmniShop is your exhibitor ordering website for Solar and Storage Live Kenya 2026.
Exhibitors visit the site, browse your catalog of 190 products, add items to a cart,
fill in their details, and submit their order. You receive all orders in the admin panel.

---

## PART 1: Running It On Your Computer (For Testing)

Think of this like a website that only runs on YOUR laptop — only you can see it.
This is good for testing before you make it live on the internet.

### What you need first: Python

Python is a free program your computer needs to run OmniShop. It's like a language
your website speaks — your computer needs to understand that language.

**Check if you already have Python:**
1. Press the Windows key + R on your keyboard
2. Type `cmd` and press Enter
3. In the black window that appears, type `python --version` and press Enter
4. If you see something like "Python 3.10.0" — great, you have it!
5. If you get an error — you need to install it (see below)

**Installing Python (if needed):**
1. Go to https://www.python.org/downloads/
2. Click the big yellow "Download Python" button
3. Run the installer
4. **IMPORTANT:** On the first screen, tick the box that says "Add Python to PATH"
5. Click "Install Now"

### Starting OmniShop (the easy way):

1. Open the `omnishop` folder on your computer
   (it's in: My Drive → Events → OmniSpace → Cowork → Catalog Design → Solar and Storage Live 2026 → omnishop)

2. Double-click the file called **"START OMNISHOP.bat"**

3. A black window will appear — don't close it! It's running your website.

4. Your browser should open automatically to your catalog page.
   If it doesn't, open Chrome and type: **http://localhost:8080/solarandstorage**

5. To see the admin panel: go to **http://localhost:8080/admin**
   Password: **Silversky#10**

6. When you're done testing, just close the black window.

---

## PART 2: Making It Live on the Internet

Right now the site only works on your laptop. To let exhibitors actually use it,
you need to put it on the internet — this is called "hosting" or "deploying."

Think of it like printing a flyer on your home printer vs. sending it to a print shop
to make thousands of copies. Right now it's on your home printer. Hosting = the print shop.

### The Easiest Way: Railway (Free to start, ~$5/month after)

Railway is a website that hosts your app on the internet. It's like parking your website
on their servers so anyone in the world can reach it.

**Step 1: Put your files on GitHub (GitHub = free online storage for website files)**
1. Go to https://github.com and create a free account
2. Click the + button (top right) → "New repository"
3. Name it "omnishop" → Click "Create repository"
4. Follow the instructions to upload your omnishop folder

**Step 2: Deploy on Railway**
1. Go to https://railway.app
2. Sign up with your GitHub account
3. Click "New Project" → "Deploy from GitHub repo"
4. Select your "omnishop" repository
5. Railway will automatically set everything up
6. After a minute or two, it gives you a website address like:
   `omnishop-production.up.railway.app`
7. That's your live website! Share that link with exhibitors.

**Step 3 (Optional): Use your own address**
Instead of a random Railway address, you can use something like:
`omnishop.omnispace3d.com`

This requires changing a setting in wherever you registered your domain (omnispace3d.com).
Ask your web developer or IT person — it's a simple 5-minute change called a "CNAME record."

---

## PART 3: The Admin Panel (How You Manage Orders)

Go to your-website-address/admin and log in with password: **Silversky#10**

**What you can do:**
- See all orders as they come in
- Click any order to see full details
- Change order status: Pending → Approved → Invoiced → Fulfilled
- Print a Packing List (grouped by product category — perfect for your warehouse team)
- Export all orders to Excel (CSV file)

---

## PART 4: Making Changes

### Change the admin password:
Open the file called `server.py` in Notepad.
Find the line that says: `"admin_password": "Silversky#10",`
Change `Silversky#10` to whatever you want.
Save the file.

### Add your PayPal email:
In the same `server.py` file, find:
`"paypal_email": "susan@susanmboya.com",`
Change to your actual PayPal business email.

### Change a product price or name:
Open the file `data/catalog.py` in Notepad.
Find the product you want to change and edit the name or price.
Save the file and restart the server.

---

## Quick Reference

| What | Address |
|------|---------|
| Exhibitor catalog | yoursite.com/solarandstorage |
| Admin panel | yoursite.com/admin |
| Admin password | Silversky#10 |

---

## Getting Help

If something doesn't work, the most common issues are:

**"Python is not found"** → Python isn't installed, or the "Add to PATH" box wasn't ticked
**"Port 8080 already in use"** → Another copy of OmniShop is already running — close it first
**The website looks broken** → Make sure you're running it from the `omnishop` folder

For technical help deploying this to the internet, any web developer or IT person
can do it in under 30 minutes — just show them this folder and the DEPLOY.md file.

---

*Built by Claude for OmniSpace 3D Events Ltd. | www.omnispace3d.com*
