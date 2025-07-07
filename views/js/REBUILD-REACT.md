# Mollie PrestaShop Module: React Component Rebuild Guide

## 1. Prerequisites

- **Node.js 16.x** (do NOT use Node 17+ or 18+)
  - Use `nvm` to manage Node versions

- **npm** (comes with Node.js)
- **PrestaShop instance** with the Mollie module installed

---

## 2. Install/Update Dependencies

Navigate to the Mollie JS directory:
```bash
cd modules/mollie/views/js
```

**Option A: Using Makefile (Recommended)**
```bash
make install
```

**Option B: Manual installation**
```bash
npm install --legacy-peer-deps
```
> If you see errors about peer dependencies, always use `--legacy-peer-deps`.

---

## 3. Edit React Components

- All React source files are in:
  ```
  modules/mollie/views/js/src/
  ```
- For admin UI, edit files in:
  ```
  modules/mollie/views/js/src/back/
  ```
- For shared or frontend, use:
  ```
  modules/mollie/views/js/src/shared/
  modules/mollie/views/js/src/front/
  ```

---

## 4. Build the Components

**Option A: Using Makefile (Recommended)**
```bash
make build
```
The Makefile automatically detects your Node.js version and applies the correct settings.

**Option B: Manual build**
```bash
npm run build
```

**If you are forced to use Node.js 17+ (not recommended):**
```bash
NODE_OPTIONS=--openssl-legacy-provider npm run build
```

---

## 5. Troubleshooting Build Issues

- **html-webpack-plugin/webpack errors:**
  If you see errors about `html-webpack-plugin` or `webpack` versions, run:
  ```bash
  npm install html-webpack-plugin@4.5.2 --legacy-peer-deps
  ```

- **Peer dependency errors:**
  Always use `--legacy-peer-deps` with `npm install`.

- **Missing or broken JS in admin:**
  - Check that the files in `modules/mollie/views/js/dist/` are present.
  - If the React UI disappears, hardcode the main script tags in `order_info.tpl`:
    ```smarty
    <script src="{$publicPath}vendors~app.min.js"></script>
    <script src="{$publicPath}app.min.js"></script>
    <script src="{$publicPath}transaction.min.js"></script>
    ```

---

## 6. Clear Cache

- **PrestaShop cache:**
  Admin → Advanced Parameters → Performance → Clear cache

- **Browser cache:**
  Hard refresh (Ctrl+F5 or Cmd+Shift+R)

---

## 7. Verify

- Go to the admin order detail page.
- The Mollie React panel should appear.
- If not, check browser console for errors and ensure the correct JS files are loaded.

---

## 8. Common Problems & Solutions

| Problem | Solution |
|---------|----------|
| Build fails with OpenSSL error | Use Node.js 16, or set `NODE_OPTIONS=--openssl-legacy-provider` |
| html-webpack-plugin/webpack version error | `npm install html-webpack-plugin@4.5.2 --legacy-peer-deps` |
| React UI not showing in admin | Hardcode script tags in `order_info.tpl` and clear cache |
| JS files missing in `dist/` | Re-run build, check for errors, ensure correct Node version |

---

## 9. Quick Reference Commands

**Using Makefile (Recommended):**
```bash
# Check Node.js version
make check-node

# Install dependencies
make install

# Build components
make build

# Full rebuild (clean + install + build)
make rebuild

# Quick rebuild (install + build)
make quick-rebuild

# Watch for changes
make watch

# Check build output
make check-build
```

**Manual commands:**
```bash
# Always use Node.js 16
nvm use 16

# Install dependencies
npm install --legacy-peer-deps

# Build
npm run build

# If using Node 17+ (not recommended)
NODE_OPTIONS=--openssl-legacy-provider npm run build

# Fix html-webpack-plugin version
npm install html-webpack-plugin@4.5.2 --legacy-peer-deps
```

---

## 10. Where to Edit

- **React code:** `modules/mollie/views/js/src/`
- **Build output:** `modules/mollie/views/js/dist/`
- **Admin order panel template:** `modules/mollie/views/templates/hook/order_info.tpl`

---

**If you follow this guide, you'll avoid 99% of the pain points with rebuilding or editing React in the Mollie module. If you hit a new error, copy the error message and search this doc or ask for help!**