# Mollie Authentication React Component

Clean React authentication page for Mollie PrestaShop module admin interface.

## Structure

```
library/
├── src/pages/authentication/          # Authentication page
│   ├── index.tsx                      # Entry point
│   ├── AuthenticationPage.tsx         # Main component
│   ├── components/                    # Your authentication components
│   └── services/                      # API service layer
├── components/ui/                     # Essential UI components only
│   ├── badge.tsx, button.tsx         # Used components
│   ├── card.tsx, input.tsx
│   ├── label.tsx, tabs.tsx
├── dist/assets/authentication.js      # Built bundle
└── Build configuration files
```

## Commands

- `npm install` - Install dependencies  
- `npm run build` - Build for production
- `npm run dev` - Development server

## Usage

1. Add your authentication components to `src/pages/authentication/components/`
2. Add API service to `src/pages/authentication/services/`
3. Build with `npm run build`
4. Include `dist/assets/authentication.js` in your PHP template



