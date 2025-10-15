/**
 * PrestaShop Accounts Integration Component
 * Handles PrestaShop Accounts Vue component integration in React
 */

import React, { useEffect, useRef, useState, useCallback } from 'react'
import { prestashopConfig } from '../../lib/prestashop-config'
import { scriptLoader } from '../../lib/script-loader'

interface PrestaShopAccountsProps {
  /** MBO CDC container ID (default: 'cdc-container') */
  cdcContainerId?: string
  /** Additional configuration */
  config?: Record<string, any>
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
  cdcContainerId = 'cdc-container',
  onReady,
  onError,
  className = '',
  style = {},
  autoInit = true
}) => {
  const containerRef = useRef<HTMLDivElement>(null)
  const cdcContainerRef = useRef<HTMLDivElement>(null)
  const psAccountsRef = useRef<any>(null)
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

  const initializePrestaShopAccounts = useCallback(async () => {
    if (state.isInitialized || state.isLoading || initializationAttempted.current) {
      return
    }

    initializationAttempted.current = true
    updateState({ isLoading: true, error: null })

    try {
      // Use the context already injected by PS Accounts module
      const w = window as any
      
      console.log('PrestaShop Accounts context available:', w.contextPsAccounts)
      
      // Check if PS Accounts is already available from existing module integration
      if (w.psaccountsVue) {
        console.log('psaccountsVue already loaded, initializing directly...')
      } else {
        // Load from the accounts UI URL if not already loaded
        const accountsUrl = w.contextPsAccounts?.accountsUiUrl
        if (accountsUrl) {
          console.log('Loading PrestaShop Accounts from accountsUiUrl:', accountsUrl)
          await scriptLoader.loadScript(`${accountsUrl}/dist/psaccounts-vue.umd.js`, {
            id: 'prestashop-accounts-script'
          })
        } else {
          // Fallback to standard CDN
          console.log('Loading PrestaShop Accounts from standard CDN...')
          await scriptLoader.loadScript('https://unpkg.com/prestashop_accounts_vue_components@5', {
            id: 'prestashop-accounts-script'
          })
        }
      }

      // Wait a bit for the script to initialize
      await new Promise(resolve => setTimeout(resolve, 500))

      // The <prestashop-accounts> web component should auto-initialize
      // when the script loads and the element is in the DOM
      
      console.log('PrestaShop Accounts context from PHP:', w.contextPsAccounts)
      
      // Check if the web component has initialized
      if (w.psaccountsVue) {
        console.log('PrestaShop Accounts (psaccountsVue) available, initializing...')
        
        // The web component should automatically pick up the contextPsAccounts
        w.psaccountsVue.init()
        
        console.log('PrestaShop Accounts initialized successfully')
      } else {
        console.log('psaccountsVue not available yet, web component may initialize automatically')
      }
      
      // Mark as ready regardless - the web component should handle itself
      updateState({ 
        isLoading: false, 
        isReady: true, 
        isInitialized: true 
      })
      
      onReady?.()

    } catch (error) {
      handleError(error instanceof Error ? error : new Error(String(error)))
    }
  }, []) // Remove dependencies to prevent re-initialization



  const destroyPrestaShopAccounts = useCallback(() => {
    psAccountsRef.current = null
    updateState({ 
      isReady: false, 
      isInitialized: false 
    })
  }, [updateState])

  useEffect(() => {
    let isMounted = true
    
    if (autoInit && isMounted && !state.isInitialized && !state.isLoading) {
      initializePrestaShopAccounts()
    }

    return () => {
      isMounted = false
      destroyPrestaShopAccounts()
    }
  }, [autoInit]) // Only depend on autoInit

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
              animation: 'cloudsync-spin 1s linear infinite',
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

      {/* MBO CDC Container */}
      <div
        ref={cdcContainerRef}
        id={cdcContainerId}
        style={{ marginBottom: '15px' }}
      />

      {/* PrestaShop Accounts Container */}
      <div
        ref={containerRef}
        className={containerClasses}
        style={{
          minHeight: state.isReady ? 'auto' : '200px',
          border: '1px dashed #ccc',
          padding: '15px',
          backgroundColor: '#f9f9f9',
          ...style
        }}
      >
        <div style={{ marginBottom: '10px', color: '#666', fontSize: '12px' }}>
          <strong>✅ PrestaShop Accounts Integration Active</strong><br />
          Status: {state.isReady ? '🟢 Ready' : state.isLoading ? '🔄 Loading...' : '⭕ Initializing'}
          {state.error && <div style={{ color: 'red', marginTop: '5px', fontSize: '11px' }}>⚠️ {state.error}</div>}
        </div>
        {React.createElement('prestashop-accounts', {
          style: {
            display: 'block',
            minHeight: '100px',
            border: '1px solid #ddd',
            backgroundColor: 'white'
          }
        })}
      </div>

      {/* Debug info */}
      {prestashopConfig.isDebugMode() && (
        <div 
          style={{
            marginTop: '10px',
            padding: '10px',
            backgroundColor: '#f8f9fa',
            borderRadius: '4px',
            fontSize: '12px'
          }}
        >
          <div><strong>PrestaShop Accounts Debug:</strong></div>
          <div>Status: {state.isReady ? 'Ready' : 'Not Ready'}</div>
          <div>Initialized: {state.isInitialized ? 'Yes' : 'No'}</div>
          <button
            onClick={initializePrestaShopAccounts}
            disabled={state.isLoading || state.isInitialized}
            style={{
              marginTop: '5px',
              padding: '5px 10px',
              fontSize: '11px'
            }}
          >
            Initialize
          </button>
        </div>
      )}
    </div>
  )
}

export default PrestaShopAccounts