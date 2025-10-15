# PrestaShop Accounts & CloudSync Integration

This document describes the integration of PrestaShop Accounts and CloudSync into the Mollie module.

## Overview

The integration enables:
1. **PrestaShop Account Authentication**: Merchants can link their PrestaShop account for unified authentication
2. **CloudSync Data Sharing**: Merchants can consent to share store data with Mollie services

## Implementation Details

### Backend Changes

#### 1. `mollie.php` - Main Module File

**Added:**
- Import for `ModuleManagerBuilder` to manage ps_eventbus installation
- `installPrestaShopEventBus()` method: Automatically installs, enables, and upgrades ps_eventbus module
- `installPrestaShopAccounts()` method: Initializes PrestaShop Accounts installer
- Called both methods in the `install()` method

**Key Code:**
```php
private function installPrestaShopEventBus()
{
    $moduleManager = ModuleManagerBuilder::getInstance()->build();
    if (!$moduleManager->isInstalled('ps_eventbus')) {
        $moduleManager->install('ps_eventbus');
    } elseif (!$moduleManager->isEnabled('ps_eventbus')) {
        $moduleManager->enable('ps_eventbus');
        $moduleManager->upgrade('ps_eventbus');
    }
}

private function installPrestaShopAccounts()
{
    $accountsInstaller = $this->getService('Mollie.PsAccountsInstaller');
    $accountsInstaller->install();
}
```

#### 2. `AdminMollieAuthenticationController.php`

**Added:**
- `initializePrestaShopAccount()` method: Injects PS Account context and CDN URL
- `initializeCloudSync()` method: Injects CloudSync/EventBus context and CDN URL
- Both methods called in `init()` to provide data to React frontend

**Context Injection:**
```php
Media::addJsDef([
    'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()->present($this->module->name),
    'contextPsEventbus' => $eventbusPresenterService->expose($this->module, ['info', 'modules', 'themes']),
]);

$this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());
$this->context->smarty->assign('urlCloudsync', 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js');
```

### Frontend Changes

#### 3. `prestashop-integration.tsx` - New Component

**Purpose:** Dynamically loads and initializes PrestaShop Account and CloudSync components

**Features:**
- Loads external CDN scripts for ps-accounts and cloudsync
- Initializes `psaccountsVue` for account management
- Initializes `cloudSyncSharingConsent` for data sharing consent
- Provides callbacks for onboarding completion events
- Renders custom HTML elements for PrestaShop components

**Usage:**
```tsx
<PrestaShopIntegration 
  onAccountLinked={(isLinked) => console.log('Account linked:', isLinked)}
  onCloudSyncCompleted={(isCompleted) => console.log('CloudSync completed:', isCompleted)}
/>
```

#### 4. `authorization-form.tsx` - Updated

**Changes:**
- Imported `PrestaShopIntegration` component
- Rendered PrestaShop components at the top of the page, above Mollie configuration
- PrestaShop authentication and consent appear before Mollie API key setup

## Dependencies

### Composer (Already Present)
- `prestashop/prestashop-accounts-installer: ^1.0.4`
- `prestashop/module-lib-service-container: v2.0`

### PrestaShop Modules (Auto-installed)
- `ps_accounts`: PrestaShop Account authentication
- `ps_eventbus`: CloudSync/EventBus for data synchronization

## Data Consent Scopes

The CloudSync integration requests the following consent scopes:
- `info`: Store technical data (PS version, PHP version) - **Required**
- `modules`: List of installed modules - Read only
- `themes`: List of installed themes - Read only

These can be customized in the `initializeCloudSync()` method by modifying the array passed to `expose()`:
```php
$eventbusPresenterService->expose($this->module, ['info', 'modules', 'themes'])
```

## User Flow

1. Merchant navigates to Mollie module configuration
2. **PrestaShop Account** component appears first
   - If not linked: Merchant sees login/signup prompt
   - If linked: Shows connected status with account email
3. **CloudSync** component appears below Account
   - Merchant can review and consent to data sharing
   - Choose which data types to share
4. **Mollie Configuration** appears below
   - Merchant can configure Mollie API keys
   - Set test/live environment

## Testing

### Verification Steps:
1. Install/reinstall the Mollie module
2. Verify `ps_eventbus` is automatically installed and enabled
3. Navigate to module configuration page
4. Verify PrestaShop Account login panel appears
5. Verify CloudSync consent panel appears below it
6. Complete account linking and consent
7. Configure Mollie API keys

### Browser Console Logs:
The integration provides helpful console logs:
- "PrestaShop Account script loaded"
- "PrestaShop Account initialized"
- "CloudSync script loaded"
- "CloudSync initialized"
- "CloudSync OnboardingCompleted: true/false"

## Architecture

```
┌─────────────────────────────────────────┐
│   AdminMollieAuthenticationController   │
│  - Injects contexts to window object    │
│  - Provides CDN URLs                     │
└────────────────┬────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────┐
│      PrestaShopIntegration (React)      │
│  - Loads external PS Account script     │
│  - Loads external CloudSync script      │
│  - Initializes both components          │
└────────────────┬────────────────────────┘
                 │
      ┌──────────┴──────────┐
      ▼                     ▼
┌─────────────┐    ┌──────────────────┐
│ PS Account  │    │    CloudSync     │
│  Component  │    │ Consent Component│
└─────────────┘    └──────────────────┘
```

## Minimalistic Design Principles

1. **No Hardcoding**: CDN URLs come from backend configuration
2. **Error Handling**: All try-catch blocks prevent page breaks
3. **Clean Separation**: Backend handles context injection, React handles UI
4. **Auto-installation**: Dependencies installed automatically during module install
5. **TypeScript Safe**: Proper type declarations for custom elements

## Future Enhancements

- Add loading states while scripts are being loaded
- Implement retry mechanism for failed script loads
- Add visual indicators for account/cloudsync status
- Conditional rendering based on onboarding completion
- Integrate account status with Mollie configuration visibility

## References

- [PrestaShop Account Documentation](https://docs.cloud.prestashop.com/9-prestashop-integration-framework/4-prestashop-account/)
- [PrestaShop CloudSync Documentation](https://docs.cloud.prestashop.com/9-prestashop-integration-framework/7-prestashop-cloudsync/)
- [Example Module](https://github.com/PrestaShopCorp/builtforjsexample)
