# Threadspace (ADBMS E‑Commerce) — Progress Report
**Date:** 2026-05-06  
**Project:** Threadspace (E‑Commerce Web App)  
**Stack:** PHP + Bootstrap 5 + MySQL + MongoDB (Polyglot Persistence)  
**Location:** `C:\xampp\htdocs\threadspace_group1`  
**Sprint Goal (today):** Establish a unified UI, remove account/admin complexity, and align system flow toward guest shopping with polyglot persistence (MongoDB products + MySQL orders).

---

## 1) Summary of What Was Accomplished Today

### 1.1 UI/UX Milestones (Threadspace unified design)
- Implemented a consistent **Threadspace design system** using the palette:
  - `#850F8D` (primary), `#C738BD` (secondary), `#E49BFF` (accent), `#F8F9D7` (background)
- Adopted a modern, consistent layout across pages using:
  - Bootstrap 5.3.3
  - Bootstrap Icons
  - Google Fonts: Playfair Display + DM Sans
  - Custom CSS in `css/styles.css`
- Standardized global UI elements:
  - **Navbar** with working search bar and cart badge
  - “Shop / Sale / Categories / About” navigation (guest-friendly)
  - Consistent spacing, cards, tables, and surfaces via shared utility classes

### 1.2 Major Architecture Change: Simplification to Guest Flow
- Removed requirement for **login/register/admin** (by updated project direction).
- Implemented a **guest cart** stored in PHP sessions: `$_SESSION['cart']`.
- Ensured the app remains coherent without user management while still satisfying polyglot persistence.

### 1.3 Functional Milestones (Frontend ↔ Backend flow)
- Home page supports product discovery + links to product detail pages.
- Implemented separate pages for:
  - **Shop:** full product listing (`shop.php`)
  - **Sale:** sale/trending products (`sale.php`)
  - **Categories:** clothing/footwear/bags/accessories (`category.php?c=...`)
- Product cards:
  - Click-through to **product page** (Mongo ObjectId-based)
  - Add-to-cart only for real MongoDB products (Option A: placeholders are view-only)
- Cart/Checkout flow:
  - `product.php` -> `cart.php` -> `checkout.php` prepared for guest checkout
  - Checkout saving depends on finalized MySQL schema usage (next steps)

---

## 2) Problems Encountered & How They Were Resolved

### 2.1 Mixed “Outdated code” vs new Threadspace UI
- **Issue:** initial codebase had plain HTML/CSS + session login gating.
- **Fix:** merged the functional behavior (search/cart/product) into the new Threadspace UI.

### 2.2 Search bar not working in navbar
- **Issue:** no `<form>` and missing `name="q"` in older header.
- **Fix:** replaced with a GET form pointing to `shop.php` so search is consistent site-wide.

### 2.3 MongoDB shell missing in VS Code environment
- **Issue:** `mongosh` not recognized (`CommandNotFoundException`).
- **Fix:** updated MongoDB via **VS Code MongoDB extension / Playground** instead of mongosh.  
  (Note: Composer is unrelated to mongosh; it installs PHP libraries only.)

### 2.4 MySQL ALTER error: Duplicate column `guest_name`
- **Issue:** `ALTER TABLE orders ADD COLUMN guest_name...` failed with `#1060 Duplicate column name`.
- **Fix:** verified table structure using:
  - `DESCRIBE orders;`
  - `SHOW CREATE TABLE orders;`
  Determined guest columns already existed; no additional add needed.

### 2.5 Placeholder vs real product IDs
- **Issue:** placeholders aren’t real MongoDB ObjectIds; cart code would break/skip them.
- **Resolution (Option A chosen):**
  - Placeholders are **view-only**.
  - Add-to-cart only allowed for real MongoDB `_id` values.
  - Cart cleanup ensures invalid/placeholder IDs are removed from session.

---

## 3) Updates Implemented (UI, Backend, DB)

### 3.1 UI & Page Updates
**Modified/Created:**
- `includes/header.php`  
  - Unified navbar
  - Working search (GET -> `shop.php`)
  - Cart badge based on session cart
  - Links updated to `shop.php`, `sale.php`, `category.php`
- `includes/footer.php`  
  - Converted from minimal closing tags to full Threadspace footer + scripts + closing tags
- `index.php`  
  - Threadspace Home UI
  - Collection preview + trends carousel section
  - Correct links to `shop.php`, `sale.php`, product pages
  - Placeholder-safe card actions (no add-to-cart for placeholders)
- `shop.php` (new)  
  - Full catalog listing
  - Search + category filtering
  - Placeholder-safe add-to-cart logic
- `sale.php` (new)  
  - Sale/trending display with discount simulation (to be replaced with real DB fields later)
- `category.php` (new)  
  - Category listing via `?c=clothing|footwear|bags|accessories`
- `product.php`  
  - Product detail UI aligned with Threadspace
  - Supports placeholders via `?ph=` for display
  - Add-to-cart enabled for real products
- `cart.php`  
  - Session-based guest cart
  - Strictly accepts only real Mongo ObjectId products (Option A)
  - Cleans invalid/placeholder IDs
- `checkout.php`  
  - Guest checkout UI + summary
  - Insert logic staged for MySQL guest fields (final confirmation page planned next)

### 3.2 CSS Updates
- `css/styles.css` expanded to include:
  - unified surfaces/cards/table styles used by cart/checkout/product
  - trends carousel helpers
  - pills/filter UI for categories/shop
  - compatibility helpers for older classes (kept to prevent breakage)

### 3.3 Database Updates (Today)
#### MySQL
- Verified `orders` now contains:
  - `user_id` nullable
  - `guest_name`, `guest_phone`, `guest_address`
  - `order_date`, `total`, `created_at`
- Next: verify `order_items` and implement order confirmation page.

#### MongoDB
- Connected and successfully updated MongoDB using VS Code MongoDB tools.
- Added/normalized fields for future sale/trending support:
  - `category` normalization (e.g., `shirts` -> `clothing`)
  - `is_on_sale`, `discount_pct`, `trending_score`
- Ensured text search and useful indexes exist or planned.

---

## 4) Overall Updated Database Schemas (Recommended Final Target)

> Note: The project currently has a legacy users/cart schema. The recommended “final target” below aligns with the guest-only app design and avoids unused tables.

### 4.1 MySQL (Final Target — Guest Orders)
**Database:** `ecom_store`

**orders**
- `id` INT PK AI
- `user_id` INT NULL (legacy, unused)
- `guest_name` VARCHAR(120) NULL/NOT NULL (preferred NOT NULL)
- `guest_phone` VARCHAR(40) NULL/NOT NULL (preferred NOT NULL)
- `guest_address` VARCHAR(255) NULL/NOT NULL (preferred NOT NULL)
- `total` DECIMAL(10,2) NOT NULL
- `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP (legacy ok)
- `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

**order_items**
- `id` INT PK AI
- `order_id` INT NOT NULL FK -> orders(id) ON DELETE CASCADE
- `product_id` VARCHAR(24) NOT NULL (MongoDB ObjectId as string)
- `quantity` INT NOT NULL
- `price` DECIMAL(10,2) NOT NULL
- (optional) `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP

**Recommended Indexes**
- `order_items(order_id)`
- `order_items(product_id)`

### 4.2 MongoDB (Final Product Document Schema)
**Database:** `ecom_store`  
**Collection:** `products`

**Document shape:**
```js
{
  _id: ObjectId(),
  name: String,
  description: String,
  price: Number,                 // store as numeric
  category: "clothing" | "footwear" | "bags" | "accessories",
  images: [String],              // image paths/URLs
  tags: [String],                // for text search
  is_on_sale: Boolean,           // optional but recommended
  discount_pct: Number,          // 0..80
  trending_score: Number,        // 0..100 (or any scale)
  attributes: {                  // flexible product specs
    sizes: [String|Number],
    colors: [String],
    material: String,
    ...anyOtherAttributes
  },
  created_at: Date,              // optional
  updated_at: Date               // optional
}
```

**Recommended Indexes**
- Text search:
  - `{ name: "text", description: "text", tags: "text" }`
- Filtering:
  - `{ category: 1 }`
- Sale/trending:
  - `{ is_on_sale: 1, trending_score: -1 }`

---

## 5) To‑Do List (Next Days) — Target Deadline: May 20–22

### Day 1 (Next session): Make checkout fully functional end-to-end
- [ ] Confirm `order_items` schema via `DESCRIBE order_items;`
- [ ] Update `checkout.php` to insert orders reliably:
  - insert with `user_id = NULL`
  - insert order_items using MongoDB product `_id`
- [ ] Create `order_success.php` (or repurpose `order_history.php`) to display:
  - order header (MySQL)
  - order items with product details (MongoDB)

### Day 2: Seed MongoDB properly (to remove reliance on placeholders for cart)
- [ ] Create a `seed_products.php` (PHP) or `mongosh` script for inserting 20–40 products:
  - 4 categories evenly distributed
  - 6–10 items set as `is_on_sale: true`
- [ ] Ensure categories match exactly: `clothing/footwear/bags/accessories`
- [ ] Add `is_on_sale`, `discount_pct`, `trending_score` to seeded docs

### Day 3: Upgrade Sale + Trends to real DB logic
- [ ] Update `sale.php` to query:
  - `find({ is_on_sale: true }).sort({ trending_score: -1 })`
- [ ] Update Home “Trends” carousel to pick from sale items:
  - top trending, rotate automatically
- [ ] Ensure “Trends” CTA always leads to `sale.php`

### Day 4: Polish + Stability
- [ ] Cart improvements:
  - update quantity (+/-) per item
  - clear cart button
- [ ] Add basic server-side validation:
  - quantity >= 1
  - order fields required
- [ ] Add “no results” states across Shop/Sale/Category pages

### Day 5: Documentation + Demo readiness
- [ ] Write README:
  - how to run locally (XAMPP + composer install + Mongo extension)
  - required PHP extension: `ext-mongodb`
- [ ] Add screenshots for documentation (optional)
- [ ] Final full flow test:
  - Home -> Shop -> Product -> Cart -> Checkout -> Order success

---

## 6) Possible Low‑Difficulty Features That Improve Functionality

### Cart & Checkout
- Quantity adjustment controls (increase/decrease) without manual editing
- “Clear cart” action
- “Continue shopping” recommended links (already partially present)
- Order success page printing or “copy order id” button

### Shop UX
- Sort dropdown (price low-high / high-low / trending)
- Pagination or “Load more”
- Category chips + active state (already implemented)

### Sale/Trends
- Real sale filters from MongoDB fields (`is_on_sale`)
- Highlight discount percent visually on cards

### Product Page
- Show “related products” (same category, random 4)
- Show tags as clickable filters -> `shop.php?q=tag`

### Stability / Quality
- Centralize placeholder generation into one include/helper (avoid duplication)
- Input sanitization (already using `htmlspecialchars` in many places)

---

## 7) Notes / Reminders
- Current environment constraints:
  - PHP 8.0.30 on XAMPP
  - Composer installed, `mongodb/mongodb` pinned to `^1.17` due to PHP 8.0
- `mongosh` may not be installed; Mongo updates can be run via:
  - VS Code MongoDB extension (Playground), or
  - MongoDB Compass

---

## 8) Evidence Checklist (for grading)
- ✅ MongoDB text search index exists
- ✅ Products stored in MongoDB
- ✅ Orders stored in MySQL (schema ready)
- ⏳ Order confirmation page to prove cross-DB join (next)
- ⏳ Sale page using real MongoDB fields (next)
- ⏳ Seed 20+ real Mongo products for cart/checkout demo (next)

---
**End of report.**