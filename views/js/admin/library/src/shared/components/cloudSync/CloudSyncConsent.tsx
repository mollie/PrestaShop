/**
 * PrestaShop CloudSync React Component
 * Handles dynamic loading, initialization, and lifecycle management of CloudSync
 */

import React, { useEffect, useRef, useState, useCallback } from 'react'
import { CloudSyncScriptLoader } from '../../lib/script-loader'
import { prestashopConfig } from '../../lib/prestashop-config'

interface CloudSyncProps {
  /** Container ID for CloudSync (default: 'cloudsync-consent') */
  containerId?: string
  /** Customer email for CloudSync initialization */
  customerEmail?: string
  /** Shop ID for CloudSync */
  shopId?: string
  /** Additional configuration options */
  config?: Record<string, any>
  /** Callback when onboarding is completed */
  onOnboardingCompleted?: (isCompleted: boolean) => void
  /** Callback when CloudSync is ready */
  onReady?: () => void
  /** Callback for errors */
  onError?: (error: Error) => void
  /** CSS class for the container */
  className?: string
  /** Inline styles for the container */
  style?: React.CSSProperties
  /** Whether to auto-initialize CloudSync */
  autoInit?: boolean
}

interface CloudSyncState {
  isLoading: boolean
  isReady: boolean
  isInitialized: boolean
  error: string | null
}

export const CloudSyncConsent: React.FC<CloudSyncProps> = ({
  containerId = 'cloudsync-consent',
  customerEmail,
  shopId,
  config = {},
  onOnboardingCompleted,
  onReady,
  onError,
  className = '',
  style = {},
  autoInit = true
}) => {
  const containerRef = useRef<HTMLDivElement>(null)
  const cloudSyncRef = useRef<any>(null)
  
  const [state, setState] = useState<CloudSyncState>({
    isLoading: false,
    isReady: false,
    isInitialized: false,
    error: null
  })

  /**
   * Update state helper
   */
  const updateState = useCallback((updates: Partial<CloudSyncState>) => {
    setState(prev => ({ ...prev, ...updates }))
  }, [])

  /**
   * Handle errors
   */
  const handleError = useCallback((error: Error) => {
    console.error('CloudSync Error:', error)
    updateState({ error: error.message, isLoading: false })
    onError?.(error)
  }, [onError, updateState])

  /**
   * Initialize CloudSync
   */
  const initializeCloudSync = useCallback(async () => {
    if (state.isInitialized || state.isLoading) {
      return
    }

    updateState({ isLoading: true, error: null })

    try {
      // Load CloudSync dependencies
      await CloudSyncScriptLoader.loadCloudSyncDependencies()

      // Verify CloudSync is ready
      if (!CloudSyncScriptLoader.isCloudSyncReady()) {
        throw new Error('CloudSync failed to initialize properly')
      }

      const cdc = (window as any).cloudSyncSharingConsent
      cloudSyncRef.current = cdc

      // Get configuration
      const email = customerEmail || prestashopConfig.getString('customerEmail')
      const shop = shopId || prestashopConfig.getString('shopId')
      
      // Initialize with container selector
      const selector = `#${containerId}`
      
      // Initialize CloudSync
      cdc.init(selector, {
        customerEmail: email,
        shopId: shop,
        ...config
      })

      // Set up event listeners
      if (onOnboardingCompleted) {
        cdc.on('OnboardingCompleted', onOnboardingCompleted)
      }

      // Check if onboarding is already completed
      if (typeof cdc.isOnboardingCompleted === 'function') {
        cdc.isOnboardingCompleted((isCompleted: boolean) => {
          if (prestashopConfig.isDebugMode()) {
            console.log('CloudSync onboarding status:', isCompleted)
          }
          onOnboardingCompleted?.(isCompleted)
        })
      }

      updateState({ 
        isLoading: false, 
        isReady: true, 
        isInitialized: true 
      })
      
      onReady?.()

    } catch (error) {
      handleError(error instanceof Error ? error : new Error(String(error)))
    }
  }, [
    state.isInitialized,
    state.isLoading,
    containerId,
    customerEmail,
    shopId,
    config,
    onOnboardingCompleted,
    onReady,
    handleError,
    updateState
  ])

  /**
   * Destroy CloudSync instance
   */
  const destroyCloudSync = useCallback(() => {
    if (cloudSyncRef.current && typeof cloudSyncRef.current.destroy === 'function') {
      try {
        cloudSyncRef.current.destroy()
        cloudSyncRef.current = null
        updateState({ 
          isReady: false, 
          isInitialized: false 
        })
      } catch (error) {
        console.warn('Failed to destroy CloudSync:', error)
      }
    }
  }, [updateState])

  /**
   * Manually reinitialize CloudSync
   */
  const reinitialize = useCallback(async () => {
    destroyCloudSync()
    await initializeCloudSync()
  }, [destroyCloudSync, initializeCloudSync])

  /**
   * Initialize on mount if autoInit is true
   */
  useEffect(() => {
    if (autoInit) {
      initializeCloudSync()
    }

    // Cleanup on unmount
    return () => {
      destroyCloudSync()
    }
  }, [autoInit, initializeCloudSync, destroyCloudSync])

  /**
   * Re-initialize if container ID changes
   */
  useEffect(() => {
    if (state.isInitialized && containerRef.current) {
      reinitialize()
    }
  }, [containerId, reinitialize, state.isInitialized])

  // Combine CSS classes
  const containerClasses = `cloudsync-container ${className}`.trim()

  return (
    <div className="cloudsync-wrapper">
      {/* Loading indicator */}
      {state.isLoading && (
        <div className="cloudsync-loading" style={{ padding: '20px', textAlign: 'center' }}>
          <div style={{ display: 'inline-block', marginBottom: '10px' }}>
            Loading CloudSync...
          </div>
          <div 
            style={{
              width: '20px',
              height: '20px',
              border: '2px solid #f3f3f3',
              borderTop: '2px solid #3498db',
              borderRadius: '50%',
              animation: 'cloudsync-spin 1s linear infinite',
              margin: '0 auto'
            }}
          ></div>
        </div>
      )}

      {/* Error message */}
      {state.error && (
        <div 
          className="cloudsync-error"
          style={{
            padding: '15px',
            backgroundColor: '#fff2f2',
            borderLeft: '4px solid #ff6b6b',
            color: '#d63031',
            marginBottom: '15px'
          }}
        >
          <strong>CloudSync Error:</strong> {state.error}
          <button
            onClick={() => reinitialize()}
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

      {/* CloudSync container */}
      <div
        ref={containerRef}
        id={containerId}
        className={containerClasses}
        style={{
          minHeight: state.isReady ? 'auto' : '100px',
          ...style
        }}
      />

      {/* Manual controls (only in debug mode) */}
      {prestashopConfig.isDebugMode() && (
        <div 
          className="cloudsync-debug-controls"
          style={{
            marginTop: '10px',
            padding: '10px',
            backgroundColor: '#f8f9fa',
            borderRadius: '4px',
            fontSize: '12px'
          }}
        >
          <div><strong>CloudSync Debug Info:</strong></div>
          <div>Status: {state.isReady ? 'Ready' : 'Not Ready'}</div>
          <div>Initialized: {state.isInitialized ? 'Yes' : 'No'}</div>
          <div>Container ID: {containerId}</div>
          <div style={{ marginTop: '5px' }}>
            <button
              onClick={initializeCloudSync}
              disabled={state.isLoading || state.isInitialized}
              style={{
                marginRight: '5px',
                padding: '5px 10px',
                fontSize: '11px'
              }}
            >
              Initialize
            </button>
            <button
              onClick={destroyCloudSync}
              disabled={!state.isInitialized}
              style={{
                marginRight: '5px',
                padding: '5px 10px',
                fontSize: '11px'
              }}
            >
              Destroy
            </button>
            <button
              onClick={reinitialize}
              disabled={state.isLoading}
              style={{
                padding: '5px 10px',
                fontSize: '11px'
              }}
            >
              Reinitialize
            </button>
          </div>
        </div>
      )}

      {/* Add CSS keyframes for loading spinner - you may need to add this to your global CSS */}
      <style>
        {`
          @keyframes cloudsync-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `}
      </style>
    </div>
  )
}

/**
 * Hook for managing CloudSync state externally
 */
export const useCloudSync = () => {
  const [isReady, setIsReady] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const initialize = useCallback(async (containerId: string, config?: Record<string, any>) => {
    setIsLoading(true)
    setError(null)

    try {
      await CloudSyncScriptLoader.loadCloudSyncDependencies()
      
      if (!CloudSyncScriptLoader.isCloudSyncReady()) {
        throw new Error('CloudSync not available')
      }

      const cdc = (window as any).cloudSyncSharingConsent
      cdc.init(`#${containerId}`, config)
      
      setIsReady(true)
      setIsLoading(false)
    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : String(err)
      setError(errorMsg)
      setIsLoading(false)
    }
  }, [])

  const checkOnboardingStatus = useCallback((callback: (isCompleted: boolean) => void) => {
    if (CloudSyncScriptLoader.isCloudSyncReady()) {
      const cdc = (window as any).cloudSyncSharingConsent
      if (typeof cdc.isOnboardingCompleted === 'function') {
        cdc.isOnboardingCompleted(callback)
      }
    }
  }, [])

  return {
    isReady,
    isLoading,
    error,
    initialize,
    checkOnboardingStatus,
    isCloudSyncAvailable: CloudSyncScriptLoader.isCloudSyncReady()
  }
}

export default CloudSyncConsent