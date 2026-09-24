# Mollie admin React apps

React front ends for the Mollie PrestaShop module back office pages.

## Structure

```
library/
├── src/app/                           # Vite entry points, one per back office page
│   ├── authorization.tsx
│   ├── payment-methods.tsx
│   └── advanced-settings.tsx
├── src/pages/                         # Page implementations
│   ├── authorization/
│   ├── payment-methods/
│   └── advanced-settings/
├── src/services/                      # API service layer
│   ├── AuthenticationApiService.ts
│   ├── PaymentMethodsApiService.ts
│   └── AdvancedSettingsApiService.ts
├── src/shared/                        # Shared UI components, hooks, types and styles
└── dist/assets/                       # JS build output, git ignored
    ├── authorization.js
    ├── mollie-payment-methods.js
    └── mollie-advanced-settings.js
```

Stylesheets are written to `views/css/admin/library/` instead, also git ignored. PrestaShop expects
stylesheets under `/views/css`, so a Vite plugin in `vite.config.ts` moves them out of `dist/`.

## Commands

- `npm install` - Install dependencies
- `npm run dev` - Development server
- `npm run build` - Type check and build for production
- `npm run lint` - Run ESLint

From the module root, `make build-react` installs and builds in one step.

## Notes

- `dist/` is git ignored. The release workflow builds it, and a local environment only picks up a
  source change after `make build-react`.
- The admin controllers register the scripts from `views/js/admin/library/dist/assets/` and the
  stylesheets from `views/css/admin/library/`, so the built file names are part of the contract and
  must match the Vite entry names.
- All page styles are scoped to `.mollie-admin-app` by `postcss.config.js` so they cannot leak into
  the PrestaShop back office theme or other modules.
- `src/` and the build configuration are stripped from the release ZIP; only `dist/` ships.
