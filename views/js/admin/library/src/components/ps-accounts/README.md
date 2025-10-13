# PS Accounts Integration for Mollie Module

This integration adds PrestaShop Accounts support to the Mollie payment module, allowing merchants to connect their stores to PrestaShop's ecosystem services.

## How it works

The PS Accounts integration is built using React components and follows the same pattern as the Klarna module but adapted for React instead of Smarty templates.

## Components

### PSAccounts Component (`src/components/ps-accounts/PSAccounts.tsx`)

A React component that:
- Dynamically loads PS Accounts and CloudSync JavaScript libraries
- Initializes the PS Accounts widget
- Handles loading and error states
- Provides callbacks for onboarding completion

### Key Features

1. **Dynamic Script Loading**: Scripts are loaded asynchronously to avoid blocking the UI
2. **Error Handling**: Shows user-friendly error messages if scripts fail to load
3. **Loading States**: Displays a spinner while scripts are loading
4. **Onboarding Callbacks**: Triggers callbacks when PS Accounts onboarding is completed

## Integration Points

### Frontend (React)

1. **PSAccounts Component**: Renders the PS Accounts widget
2. **Authorization Form**: Includes PS Accounts at the top of the configuration page
3. **API Service**: Fetches PS Accounts configuration from the backend

### Backend (PHP)

1. **AJAX Endpoint**: `ajaxGetPSAccountsConfig()` method in `AdminMollieAuthenticationController`
2. **CDN URL Configuration**: Returns the PS Accounts and CloudSync CDN URLs
3. **Module Detection**: Checks if PS Accounts and CloudSync modules are installed

## Usage

The PS Accounts widget automatically appears at the top of the Mollie authentication page when:
- PS Accounts module is installed and active
- CloudSync module is installed and active
- The CDN URLs are properly configured

## Configuration

The system automatically detects installed PS modules and provides the appropriate CDN URLs:

```php
// PS Accounts CDN URL
'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle/ps-accounts.js'

// CloudSync CDN URL  
'https://unpkg.com/@prestashopcorp/cloudsync-cdc/dist/cloudsync-cdc.umd.js'
```

## API Endpoints

### GET /admin/mollie-authentication?ajax=1&action=getPSAccountsConfig

Returns PS Accounts configuration:

```json
{
  "success": true,
  "data": {
    "psAccountsCdnUrl": "https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle/ps-accounts.js",
    "cloudSyncCdnUrl": "https://unpkg.com/@prestashopcorp/cloudsync-cdc/dist/cloudsync-cdc.umd.js"
  }
}
```

## Styling

Custom CSS is provided in `ps-accounts.css` for:
- Loading spinner animation
- Error message styling
- Container spacing and layout

## Comparison with Klarna Implementation

| Aspect | Klarna (Smarty) | Mollie (React) |
|--------|-----------------|----------------|
| Template Engine | Smarty (.tpl) | React (.tsx) |
| Script Loading | Static script tags | Dynamic script loading |
| State Management | jQuery/vanilla JS | React hooks |
| Error Handling | Basic | Comprehensive with UI states |
| Loading States | None | Spinner with loading message |

## Benefits

1. **Better User Experience**: Loading states and error handling
2. **Non-blocking**: Asynchronous script loading doesn't freeze the UI
3. **Type Safety**: Full TypeScript support
4. **Maintainable**: Clean React component architecture
5. **Reusable**: Component can be used in other parts of the admin

## Future Enhancements

- Add retry mechanism for failed script loads
- Implement caching for CDN URLs
- Add more granular error messages
- Support for multiple PS Accounts configurations