/**
 * PrestaShop Accounts Integration Component
 * Handles PrestaShop Accounts Vue component integration in React
 *
 * Based on PrestaShop Integration Framework documentation:
 * https://docs.cloud.prestashop.com/9-prestashop-integration-framework/4-prestashop-account/
 */

import React, { useEffect, useRef, useState, useCallback } from 'react'
import { scriptLoader } from '../../lib/script-loader'

interface PrestaShopAccountsProps {
  /** Callback when accounts is ready */
  onReady?: () => void
  /** Callback for errors */
  onError?: (error: Error) => void
  /** CSS class for the container */
  className?: string
  /** Inline styles for the container */
  style?: React.CSSProperties
  /** Whether to auto-initialize */
  autoInit?: boolean
}

interface AccountsState {
  isLoading: boolean
  isReady: boolean
  isInitialized: boolean
  error: string | null
}

export const PrestaShopAccounts: React.FC<PrestaShopAccountsProps> = ({
  onReady,
  onError,
  className = '',
  style = {},
  autoInit = true
}) => {
  const initializationAttempted = useRef<boolean>(false)

  const [state, setState] = useState<AccountsState>({
    isLoading: false,
    isReady: false,
    isInitialized: false,
    error: null
  })

  const updateState = useCallback((updates: Partial<AccountsState>) => {
    setState(prev => ({ ...prev, ...updates }))
  }, [])

  const handleError = useCallback((error: Error) => {
    console.error('PrestaShop Accounts Error:', error)
    updateState({ error: error.message, isLoading: false })
    onError?.(error)
  }, [onError, updateState])

  /**
   * Initialize PrestaShop Accounts according to the official documentation
   */
  const initializePrestaShopAccounts = useCallback(async () => {
    if (state.isInitialized || state.isLoading || initializationAttempted.current) {
      console.log('PrestaShop Accounts: skipping initialization (already initialized or in progress)')
      return
    }

    initializationAttempted.current = true
    updateState({ isLoading: true, error: null })

    try {
      const w = window as any

      // Check if contextPsAccounts was injected by PHP
      if (!w.contextPsAccounts) {
        throw new Error('contextPsAccounts not found. Make sure PrestaShop Accounts module is properly configured in your controller.')
      }

      console.log('PrestaShop Accounts: contextPsAccounts found', w.contextPsAccounts)

      // Check if urlAccountsCdn is available (injected via Smarty)
      const urlAccountsCdn = w.prestashop?.urlAccountsCdn || w.urlAccountsCdn

      if (!urlAccountsCdn) {
        throw new Error('urlAccountsCdn not found. Make sure it is assigned in your controller.')
      }

      console.log('PrestaShop Accounts: Loading from CDN:', urlAccountsCdn)

      // Load the PrestaShop Accounts script from the CDN
      if (!w.psaccountsVue) {
        await scriptLoader.loadScript(urlAccountsCdn, {
          id: 'prestashop-accounts-cdn',
          onLoad: () => {
            console.log('PrestaShop Accounts: Script loaded successfully')
          }
        })

        // Wait for psaccountsVue to be available
        await waitForPsAccountsVue(5000)
      }

      // Initialize psaccountsVue
      if (w.psaccountsVue && typeof w.psaccountsVue.init === 'function') {
        console.log('PrestaShop Accounts: Calling psaccountsVue.init()')
        w.psaccountsVue.init()

        console.log('PrestaShop Accounts: Initialized successfully')

        updateState({
          isLoading: false,
          isReady: true,
          isInitialized: true
        })

        onReady?.()
      } else {
        throw new Error('psaccountsVue.init() is not available')
      }

    } catch (error) {
      handleError(error instanceof Error ? error : new Error(String(error)))
      initializationAttempted.current = false // Allow retry
    }
  }, [handleError, onReady, state.isInitialized, state.isLoading, updateState])

  /**
   * Wait for psaccountsVue to be available on window
   */
  const waitForPsAccountsVue = (timeout = 5000): Promise<void> => {
    return new Promise((resolve, reject) => {
      const w = window as any
      if (w.psaccountsVue) {
        resolve()
        return
      }

      let attempts = 0
      const maxAttempts = timeout / 100

      const check = () => {
        if (w.psaccountsVue) {
          resolve()
        } else if (attempts >= maxAttempts) {
          reject(new Error('PrestaShop Accounts script loaded but psaccountsVue not available'))
        } else {
          attempts++
          setTimeout(check, 100)
        }
      }

      check()
    })
  }

  useEffect(() => {
    if (autoInit && !state.isInitialized && !state.isLoading) {
      initializePrestaShopAccounts()
    }
  }, [autoInit, initializePrestaShopAccounts, state.isInitialized, state.isLoading])

  const containerClasses = `prestashop-accounts-container ${className}`.trim()

  return (
    <div className="prestashop-accounts-wrapper">
      {/* Loading indicator */}
      {state.isLoading && (
        <div className="ps-accounts-loading" style={{ padding: '20px', textAlign: 'center' }}>
          <div style={{ marginBottom: '10px' }}>
            Loading PrestaShop Accounts...
          </div>
          <div
            style={{
              width: '20px',
              height: '20px',
              border: '2px solid #f3f3f3',
              borderTop: '2px solid #0040ff',
              borderRadius: '50%',
              animation: 'spin 1s linear infinite',
              margin: '0 auto'
            }}
          />
        </div>
      )}

      {/* Error message */}
      {state.error && (
        <div
          className="ps-accounts-error"
          style={{
            padding: '15px',
            backgroundColor: '#fff2f2',
            borderLeft: '4px solid #ff6b6b',
            color: '#d63031',
            marginBottom: '15px'
          }}
        >
          <strong>PrestaShop Accounts Error:</strong> {state.error}
          <button
            onClick={() => initializePrestaShopAccounts()}
            style={{
              marginLeft: '10px',
              padding: '5px 10px',
              backgroundColor: '#ff6b6b',
              color: 'white',
              border: 'none',
              borderRadius: '3px',
              cursor: 'pointer'
            }}
          >
            Retry
          </button>
        </div>
      )}

      {/* PrestaShop Accounts Web Component */}
      <div
        className={containerClasses}
        style={{
          ...style
        }}
      >
        {React.createElement('prestashop-accounts')}
      </div>
    </div>
  )
}

export default PrestaShopAccounts