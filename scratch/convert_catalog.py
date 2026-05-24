import json
import sys
import os

# Add project root to path for imports
root = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, root)

from data.catalog import CATEGORIES, PRODUCTS

data = {
    "CATEGORIES": CATEGORIES,
    "PRODUCTS": PRODUCTS
}

with open(os.path.join(root, "data", "catalog.json"), "w", encoding="utf-8") as f:
    json.dump(data, f, indent=4)

print("Catalog converted to JSON.")
