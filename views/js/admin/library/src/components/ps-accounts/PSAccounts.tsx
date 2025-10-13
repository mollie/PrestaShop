import { useEffect, useRef, useState } from 'react'

interface PSAccountsProps {
  psAccountsCdnUrl?: string
  cloudSyncCdnUrl?: string
  onOnboardingCompleted?: (isCompleted: boolean) => void
}

declare global {
  interface Window {
    psaccountsVue?: {
      init: () => void
    }
    cloudSyncSharingConsent?: {
      init: (selector: string) => void
      on: (event: string, callback: (isCompleted: boolean) => void) => void
      isOnboardingCompleted: (callback: (isCompleted: boolean) => void) => void
    }
  }

  namespace JSX {
    interface IntrinsicElements {
      'prestashop-accounts': React.DetailedHTMLProps<React.HTMLAttributes<HTMLElement>, HTMLElement>
    }
  }
}

export default function PSAccounts({
  psAccountsCdnUrl,
  cloudSyncCdnUrl,
  onOnboardingCompleted
}: PSAccountsProps) {
  const psAccountsRef = useRef<HTMLDivElement>(null)
  const cloudSyncRef = useRef<HTMLDivElement>(null)
  const scriptsLoadedRef = useRef(false)
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  console.log('🚀 PSAccounts component rendered!', { psAccountsCdnUrl, cloudSyncCdnUrl })

  useEffect(() => {
    console.log('PSAccounts useEffect triggered with:', { psAccountsCdnUrl, cloudSyncCdnUrl })
    
    const loadScripts = async () => {
      if (scriptsLoadedRef.current) {
        console.log('Scripts already loaded')
        setIsLoading(false)
        return
      }

      // Always set loading to false after a short delay for testing
      setTimeout(() => {
        setIsLoading(false)
      }, 1000)

      try {
        setIsLoading(true)
        setError(null)
        
        console.log('Starting to load scripts...')

        // Load PS Accounts script
        if (psAccountsCdnUrl) {
          console.log('Loading PS Accounts script:', psAccountsCdnUrl)
          await loadScript(psAccountsCdnUrl)
        }

        // Load CloudSync script
        if (cloudSyncCdnUrl) {
          console.log('Loading CloudSync script:', cloudSyncCdnUrl)
          await loadScript(cloudSyncCdnUrl)
        }

        scriptsLoadedRef.current = true
        console.log('Scripts loaded successfully')
        initializeComponents()
        setIsLoading(false)
      } catch (error) {
        console.error('Failed to load PS Accounts scripts:', error)
        setError('Failed to load PS Accounts. Please check your internet connection and try again.')
        setIsLoading(false)
      }
    }

    const loadScript = (src: string): Promise<void> => {
      return new Promise((resolve, reject) => {
        // Check if script is already loaded
        if (document.querySelector(`script[src="${src}"]`)) {
          resolve()
          return
        }

        const script = document.createElement('script')
        script.src = src
        script.onload = () => resolve()
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`))
        document.head.appendChild(script)
      })
    }

    const initializeComponents = () => {
      // Initialize PS Accounts
      if (window.psaccountsVue) {
        window.psaccountsVue.init()
      }

      // Initialize CloudSync
      if (window.cloudSyncSharingConsent && cloudSyncRef.current) {
        const cdc = window.cloudSyncSharingConsent
        cdc.init('#prestashop-cloudsync')

        cdc.on('OnboardingCompleted', (isCompleted: boolean) => {
          console.log('OnboardingCompleted', isCompleted)
          onOnboardingCompleted?.(isCompleted)
        })

        cdc.isOnboardingCompleted((isCompleted: boolean) => {
          console.log('Onboarding is already Completed', isCompleted)
        })
      }
    }

    loadScripts()
  }, [psAccountsCdnUrl, cloudSyncCdnUrl, onOnboardingCompleted])

  const containerStyle: React.CSSProperties = {
    marginBottom: '2rem',
    border: '2px solid #007cba',
    borderRadius: '8px',
    padding: '1rem',
    backgroundColor: '#f9f9f9'
  }

  const loadingStyle: React.CSSProperties = {
    padding: '2rem',
    textAlign: 'center',
    border: '1px solid #e2e8f0',
    borderRadius: '0.5rem',
    backgroundColor: '#f8fafc'
  }

  const errorStyle: React.CSSProperties = {
    padding: '1rem',
    border: '1px solid #fecaca',
    borderRadius: '0.5rem',
    backgroundColor: '#fef2f2',
    color: '#dc2626',
    fontSize: '0.875rem'
  }

  const spinnerStyle: React.CSSProperties = {
    display: 'inline-block',
    width: '1.5rem',
    height: '1.5rem',
    border: '2px solid #e2e8f0',
    borderTop: '2px solid #3b82f6',
    borderRadius: '50%',
    animation: 'spin 1s linear infinite'
  }

  return (
    <div style={containerStyle}>
      <h3 style={{ margin: '0 0 1rem 0', color: '#007cba' }}>🚀 PS Accounts Integration</h3>
      
      {/* Debug info */}
      <div style={{ padding: '1rem', backgroundColor: '#f0f0f0', border: '1px solid #ccc', marginBottom: '1rem', borderRadius: '4px' }}>
        <h4 style={{ margin: '0 0 0.5rem 0' }}>Debug Info</h4>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>PS Accounts CDN URL: {psAccountsCdnUrl || 'Not provided'}</p>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>CloudSync CDN URL: {cloudSyncCdnUrl || 'Not provided'}</p>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>Loading: {isLoading ? 'Yes' : 'No'}</p>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>Error: {error || 'None'}</p>
        <p style={{ margin: '0.25rem 0', fontSize: '0.9rem' }}>Scripts Loaded: {scriptsLoadedRef.current ? 'Yes' : 'No'}</p>
      </div>

      {isLoading && (
        <div style={loadingStyle}>
          <div style={spinnerStyle}></div>
          <p style={{ marginTop: '0.5rem', marginBottom: 0 }}>Loading PS Accounts...</p>
        </div>
      )}

      {error && (
        <div style={errorStyle}>
          {error}
        </div>
      )}

      {!isLoading && !error && psAccountsCdnUrl && cloudSyncCdnUrl && (
        <>
          <div ref={psAccountsRef} dangerouslySetInnerHTML={{ __html: '<prestashop-accounts></prestashop-accounts>' }}></div>
          <br />
          <div id="prestashop-cloudsync" ref={cloudSyncRef}></div>
          <br />
        </>
      )}

      {!isLoading && !error && (!psAccountsCdnUrl || !cloudSyncCdnUrl) && (
        <div style={{ padding: '1rem', backgroundColor: '#fff3cd', border: '1px solid #ffeaa7', borderRadius: '4px' }}>
          <p style={{ margin: 0 }}>⚠️ PS Accounts not configured. Missing CDN URLs.</p>
          <p style={{ margin: '0.5rem 0 0 0', fontSize: '0.9rem' }}>
            This might be because PS Accounts or CloudSync modules are not installed or the API endpoint is not working.
          </p>
        </div>
      )}
    </div>
  )
}