"use client"

import { useEffect, useState, useRef } from "react"

// Extend JSX to support custom HTML elements
declare global {
  namespace JSX {
    interface IntrinsicElements {
      'prestashop-accounts': React.DetailedHTMLProps<React.HTMLAttributes<HTMLElement>, HTMLElement>
    }
  }
}

interface PrestaShopIntegrationProps {
  onAccountLinked?: (isLinked: boolean) => void
  onCloudSyncCompleted?: (isCompleted: boolean) => void
}

/**
 * Component that integrates PrestaShop Account and CloudSync
 * Loads external scripts and initializes the PrestaShop components
 */
export default function PrestaShopIntegration({
  onAccountLinked,
  onCloudSyncCompleted,
}: PrestaShopIntegrationProps) {
  const [accountScriptLoaded, setAccountScriptLoaded] = useState(false)
  const [cloudSyncScriptLoaded, setCloudSyncScriptLoaded] = useState(false)
  const [accountCdnAvailable, setAccountCdnAvailable] = useState(false)
  const [cloudSyncCdnAvailable, setCloudSyncCdnAvailable] = useState(false)
  const accountsInitialized = useRef(false)
  const cloudSyncInitialized = useRef(false)

  // Check if CDN URLs are available
  useEffect(() => {
    const urlAccountsCdn = (window as any).urlAccountsCdn
    const urlCloudsync = (window as any).urlCloudsync
    
    setAccountCdnAvailable(!!urlAccountsCdn)
    setCloudSyncCdnAvailable(!!urlCloudsync)
  }, [])

  // Load PrestaShop Account script
  useEffect(() => {
    const urlAccountsCdn = (window as any).urlAccountsCdn
    
    console.log('PrestaShop Account CDN URL:', urlAccountsCdn)
    console.log('All window vars:', {
      urlAccountsCdn: (window as any).urlAccountsCdn,
      urlCloudsync: (window as any).urlCloudsync,
      contextPsAccounts: (window as any).contextPsAccounts,
      contextPsEventbus: (window as any).contextPsEventbus,
    })
    
    if (!urlAccountsCdn) {
      console.warn('PrestaShop Account CDN URL not found - ps_accounts module may not be installed or configured')
      return
    }

    // Check if script already exists
    if (document.querySelector(`script[src="${urlAccountsCdn}"]`)) {
      setAccountScriptLoaded(true)
      return
    }

    const script = document.createElement('script')
    script.src = urlAccountsCdn
    script.async = true
    script.onload = () => {
      console.log('PrestaShop Account script loaded')
      setAccountScriptLoaded(true)
    }
    script.onerror = () => {
      console.error('Failed to load PrestaShop Account script')
    }

    document.head.appendChild(script)

    return () => {
      // Cleanup script if component unmounts
      const existingScript = document.querySelector(`script[src="${urlAccountsCdn}"]`)
      if (existingScript && existingScript.parentNode) {
        existingScript.parentNode.removeChild(existingScript)
      }
    }
  }, [])

  // Load CloudSync script
  useEffect(() => {
    const urlCloudsync = (window as any).urlCloudsync
    
    console.log('CloudSync CDN URL:', urlCloudsync)
    
    if (!urlCloudsync) {
      console.warn('CloudSync CDN URL not found - ps_eventbus module may not be installed or configured')
      return
    }

    // Check if script already exists
    if (document.querySelector(`script[src="${urlCloudsync}"]`)) {
      setCloudSyncScriptLoaded(true)
      return
    }

    const script = document.createElement('script')
    script.src = urlCloudsync
    script.async = true
    script.onload = () => {
      console.log('CloudSync script loaded')
      setCloudSyncScriptLoaded(true)
    }
    script.onerror = () => {
      console.error('Failed to load CloudSync script')
    }

    document.head.appendChild(script)

    return () => {
      // Cleanup script if component unmounts
      const existingScript = document.querySelector(`script[src="${urlCloudsync}"]`)
      if (existingScript && existingScript.parentNode) {
        existingScript.parentNode.removeChild(existingScript)
      }
    }
  }, [])

  // Initialize PrestaShop Account
  useEffect(() => {
    if (!accountScriptLoaded || accountsInitialized.current) {
      return
    }

    const psaccountsVue = (window as any).psaccountsVue
    if (!psaccountsVue) {
      console.warn('psaccountsVue not found on window object')
      return
    }

    try {
      psaccountsVue.init()
      accountsInitialized.current = true
      console.log('PrestaShop Account initialized')

      // Check if account is already linked
      const isLinked = psaccountsVue.isOnboardingCompleted()
      onAccountLinked?.(isLinked)
    } catch (error) {
      console.error('Failed to initialize PrestaShop Account:', error)
    }
  }, [accountScriptLoaded, onAccountLinked])

  // Initialize CloudSync
  useEffect(() => {
    if (!cloudSyncScriptLoaded || cloudSyncInitialized.current) {
      return
    }

    const cdc = (window as any).cloudSyncSharingConsent
    if (!cdc) {
      console.warn('cloudSyncSharingConsent not found on window object')
      return
    }

    try {
      cdc.init('#prestashop-cloudsync')
      cloudSyncInitialized.current = true
      console.log('CloudSync initialized')

      // Listen for onboarding completion
      cdc.on('OnboardingCompleted', (isCompleted: boolean) => {
        console.log('CloudSync OnboardingCompleted:', isCompleted)
        onCloudSyncCompleted?.(isCompleted)
      })

      // Check if already completed
      cdc.isOnboardingCompleted((isCompleted: boolean) => {
        console.log('CloudSync already completed:', isCompleted)
        onCloudSyncCompleted?.(isCompleted)
      })
    } catch (error) {
      console.error('Failed to initialize CloudSync:', error)
    }
  }, [cloudSyncScriptLoaded, onCloudSyncCompleted])

  return (
    <div className="prestashop-integration-wrapper mb-8">
      {/* Debug Info */}
      {!accountCdnAvailable && !cloudSyncCdnAvailable && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
          <p className="text-yellow-800 text-sm">
            <strong>Note:</strong> PrestaShop Account and CloudSync modules are not configured. 
            Please ensure ps_accounts and ps_eventbus modules are installed and configured.
          </p>
        </div>
      )}

      {/* PrestaShop Account Component */}
      {accountCdnAvailable && (
        <div className="prestashop-account-container mb-6">
          <div dangerouslySetInnerHTML={{ __html: '<prestashop-accounts></prestashop-accounts>' }} />
        </div>
      )}

      {/* CloudSync Component */}
      {cloudSyncCdnAvailable && (
        <div className="prestashop-cloudsync-container">
          <div id="prestashop-cloudsync"></div>
        </div>
      )}
    </div>
  )
}
