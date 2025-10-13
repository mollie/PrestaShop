"use client"

import React, { useState, useEffect } from "react"
import { ExternalLink, CheckCircle } from "lucide-react"
import { Button } from "../../../shared/components/ui/button"
import { Input } from "../../../shared/components/ui/input"
import { authApiService } from "../../../services/AuthenticationApiService"
import { useTranslations } from "../../../shared/hooks/use-translations"


// Mollie Logo Component
const MollieLogo = () => (
  <svg
    version="1.1"
    xmlns="http://www.w3.org/2000/svg"
    xmlnsXlink="http://www.w3.org/1999/xlink"
    viewBox="0 0 320 94"
    xmlSpace="preserve"
    className="h-6 w-auto text-black"
  >
    <style type="text/css">
      {`.st0{fill-rule:evenodd;clip-rule:evenodd;fill:currentColor;}`}
    </style>
    <path
      className="st0"
      d="M289.3,44.3c6.9,0,13.2,4.5,15.4,11h-30.7C276.1,48.9,282.3,44.3,289.3,44.3z M320,60.9c0-8-3.1-15.6-8.8-21.4
      c-5.7-5.8-13.3-9-21.3-9h-0.4c-8.3,0.1-16.2,3.4-22.1,9.3c-5.9,5.9-9.2,13.7-9.3,22c-0.1,8.5,3.2,16.5,9.2,22.6
      c6.1,6.1,14.1,9.5,22.6,9.5h0c11.2,0,21.7-6,27.4-15.6l0.7-1.2l-12.6-6.2l-0.6,1c-3.1,5.2-8.6,8.2-14.7,8.2
      c-7.7,0-14.4-5.1-16.5-12.5H320V60.9z M241.2,19.8c-5.5,0-9.9-4.4-9.9-9.9c0-5.5,4.4-9.9,9.9-9.9s9.9,4.4,9.9,9.9
      C251.2,15.3,246.7,19.8,241.2,19.8z M233.6,92.7h15.2V31.8h-15.2V92.7z M204.5,1.3h15.2v91.5h-15.2V1.3z M175.4,92.7h15.2V1.3h-15.2
      V92.7z M135.3,79c-9.2,0-16.8-7.5-16.8-16.7c0-9.2,7.5-16.7,16.8-16.7s16.8,7.5,16.8,16.7C152.1,71.5,144.6,79,135.3,79z
      M135.3,30.5c-17.6,0-31.8,14.2-31.8,31.7S117.8,94,135.3,94c17.5,0,31.8-14.2,31.8-31.7S152.9,30.5,135.3,30.5z M70.4,30.6
      c-0.8-0.1-1.6-0.1-2.4-0.1c-7.7,0-15,3.1-20.2,8.7c-5.2-5.5-12.5-8.7-20.1-8.7C12.4,30.5,0,42.9,0,58v34.7h14.9V58.5
      c0-6.3,5.2-12.1,11.3-12.7c0.4,0,0.9-0.1,1.3-0.1c6.9,0,12.5,5.6,12.5,12.5v34.6h15.2V58.4c0-6.3,5.2-12.1,11.3-12.7
      c0.4,0,0.9-0.1,1.3-0.1c6.9,0,12.5,5.6,12.6,12.4v34.7h15.2V58.5c0-7-2.6-13.6-7.2-18.8C83.7,34.4,77.3,31.2,70.4,30.6z"
    />
  </svg>
)

const PsAccounts = () => {
  const renderMboCdcDependencyResolver = (window as any).mboCdcDependencyResolver?.render

  useEffect(() => {
    if (renderMboCdcDependencyResolver) {
      const context = {
        onDependenciesResolved: () => location.reload(),
        onDependencyResolved: (dependencyData: any) => console.log('Dependency installed', dependencyData), // name, displayName, version
        onDependencyFailed: (dependencyData: any) => console.log('Failed to install dependency', dependencyData),
        onDependenciesFailed: () => console.log('There are some errors'),
      }
      renderMboCdcDependencyResolver(context, '#cdc-container')
    }
  }, [renderMboCdcDependencyResolver])

  return (
    <>
      <script src="https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js"></script>
      <div id="cdc-container"></div>
      {React.createElement('prestashop-accounts')}
    </>
  )
}

// Skeleton loading components
const SkeletonModeToggle = () => (
  <div>
    <div className="h-4 bg-gray-200 rounded w-12 mb-2 animate-pulse"></div>
    <div className="flex rounded-lg overflow-hidden border border-gray-200">
      <div className="flex-1 px-6 py-3 bg-gray-100 animate-pulse"></div>
      <div className="flex-1 px-6 py-3 bg-gray-50 animate-pulse"></div>
    </div>
    <div className="h-3 bg-gray-200 rounded w-48 mt-2 animate-pulse"></div>
  </div>
)

const SkeletonApiKeyInput = () => (
  <div>
    <div className="flex items-center gap-2 mb-2">
      <div className="h-4 bg-gray-200 rounded w-24 animate-pulse"></div>
    </div>
    <div className="relative">
      <div className="h-12 bg-gray-100 border border-gray-200 rounded animate-pulse"></div>
    </div>
    <div className="h-3 bg-gray-200 rounded w-56 mt-2 animate-pulse"></div>
  </div>
)

const SkeletonConnectButton = () => (
  <div className="w-full h-12 bg-gray-100 rounded animate-pulse mb-4"></div>
)

export default function AuthorizationForm() {
  const { t } = useTranslations()
  const [mode, setMode] = useState<"live" | "test">("live")
  const [apiKey, setApiKey] = useState("")
  const [isConnected, setIsConnected] = useState(false)
  const [showApiKey, setShowApiKey] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [testApiKey, setTestApiKey] = useState("")
  const [liveApiKey, setLiveApiKey] = useState("")
  const [errorMessage, setErrorMessage] = useState("")
  const [justConnected, setJustConnected] = useState(false)
  const [initialLoading, setInitialLoading] = useState(true)
  const [showConfirmDialog, setShowConfirmDialog] = useState(false)
  const [pendingMode, setPendingMode] = useState<"live" | "test" | null>(null)

  // Load current settings on component mount
  useEffect(() => {
    loadCurrentSettings()
  }, [])

  const loadCurrentSettings = async () => {
    try {
      const response = await authApiService.getCurrentSettings()
      if (response.success) {
        setTestApiKey(response.data.test_api_key || "")
        setLiveApiKey(response.data.live_api_key || "")
        setMode(response.data.environment as "live" | "test")
        setApiKey(response.data.environment === "live" ? response.data.live_api_key : response.data.test_api_key)
        setIsConnected(response.data.is_connected || false)
        setErrorMessage("")
        setJustConnected(false) // Reset the "just connected" state on load
      }
    } catch (error) {
      console.error('Failed to load settings:', error)
      setErrorMessage("Failed to load current settings")
    } finally {
      setInitialLoading(false) // Stop initial loading regardless of success/failure
    }
  }

  const handleConnect = async () => {
    if (!apiKey.trim()) return

    setIsLoading(true)
    setErrorMessage("")

    try {
      const saveResponse = await authApiService.saveApiKey(apiKey, mode)
      if (saveResponse.success) {
        setIsConnected(true)
        setJustConnected(true) // Show success message only after successful connect
        if (mode === "live") {
          setLiveApiKey(apiKey)
        } else {
          setTestApiKey(apiKey)
        }
        setErrorMessage("")
      } else {
        setIsConnected(false)
        setJustConnected(false)
        setErrorMessage(saveResponse.message || t('connectionFailed'))
      }
    } catch (error) {
      console.error('Failed to connect:', error)
      setIsConnected(false)
      setJustConnected(false)
      setErrorMessage(t('connectionFailed'))
    } finally {
      setIsLoading(false)
    }
  }

  const handleModeChange = (newMode: "live" | "test") => {
    // If it's the same mode, do nothing
    if (newMode === mode) return

    // Show confirmation dialog
    setPendingMode(newMode)
    setShowConfirmDialog(true)
  }

  const confirmModeSwitch = async () => {
    if (!pendingMode) return

    setShowConfirmDialog(false)
    setErrorMessage("")
    setJustConnected(false) // Clear the success message when switching modes

    try {
      // Call backend to switch environment
      const switchResponse = await authApiService.switchEnvironment(pendingMode)
      if (switchResponse.success) {
        // Update mode and connection status based on backend response
        setMode(pendingMode)
        setIsConnected(switchResponse.data.is_connected || false)
        setApiKey(switchResponse.data.api_key || "")

        // Update the stored keys based on the response
        if (pendingMode === "live") {
          setLiveApiKey(switchResponse.data.api_key || liveApiKey)
        } else {
          setTestApiKey(switchResponse.data.api_key || testApiKey)
        }
      } else {
        console.error('Failed to switch environment:', switchResponse.message)
        setErrorMessage(switchResponse.message || t('failedToSwitchEnvironment'))
      }
    } catch (error) {
      console.error('Failed to switch environment:', error)
      setErrorMessage(t('failedToSwitchEnvironment'))
    } finally {
      setPendingMode(null)
    }
  }

  const cancelModeSwitch = () => {
    setShowConfirmDialog(false)
    setPendingMode(null)
  }

  return (
    <div className="bg-white font-inter">
      <PsAccounts />
      {/* Confirmation Dialog */}
      {showConfirmDialog && (
        <div className="fixed inset-0 z-[9999] flex items-center justify-center" style={{backgroundColor: 'rgba(0, 0, 0, 0.4)'}}>
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div className="p-6">
              <h3 className="text-lg font-semibold text-gray-900" style={{marginBottom: '1rem'}}>
                {t('switchEnvironment')}
              </h3>
              <p className="text-gray-600 text-sm" style={{marginBottom: '2rem'}}>
                {t('confirmSwitchEnvironment', pendingMode === "live" ? t('live') : t('test'))}
              </p>
              <div className="flex gap-3 justify-end">
                <button
                  onClick={cancelModeSwitch}
                  className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                >
                  {t('cancel')}
                </button>
                <button
                  onClick={confirmModeSwitch}
                  className="px-4 py-2 text-sm font-medium text-white rounded-md hover:opacity-90 transition-opacity"
                  style={{backgroundColor: 'rgba(0, 64, 255, 1)'}}
                >
                  {t('switchTo', pendingMode === "live" ? t('live') : t('test'))}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <div className="max-w-6xl mx-auto px-2 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
          {/* Left Column - Header */}
          <div className="flex flex-col">
            <div className="mb-16">
              <div className="mb-4">
                <MollieLogo />
              </div>
              <h2 className="text-4xl font-medium text-black mb-4">{t('apiConfiguration')}</h2>
              <p className="text-black text-lg font-medium">{t('selectModeDescription')}</p>
            </div>

            <div>
              <h3 className="text-lg font-medium text-black mb-2">{t('newToMollie')}</h3>
              <a href="https://my.mollie.com/dashboard/signup" target="_blank" rel="noopener noreferrer" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-base font-medium">
                {t('createAccount')}
              </a>
            </div>
          </div>

          {/* Right Column - Configuration */}
          <div className="space-y-6">
            {/* Mode Toggle */}
            {initialLoading ? (
              <SkeletonModeToggle />
            ) : (
              <div>
                <label className="block text-sm font-medium text-black mb-2">{t('mode')}</label>
                <div className="flex rounded-lg overflow-hidden border border-gray-200">
                  <button
                    onClick={() => handleModeChange("live")}
                    className={`flex-1 px-6 py-3 text-base font-bold transition-colors ${
                      mode === "live" ? "text-white" : "bg-white text-black hover:bg-gray-50"
                    }`}
                    style={mode === "live" ? {backgroundColor: 'rgba(0, 64, 255, 1)'} : {}}
                  >
                    {t('live')}
                  </button>
                  <button
                    onClick={() => handleModeChange("test")}
                    className={`flex-1 px-6 py-3 text-base font-bold transition-colors ${
                      mode === "test" ? "text-white" : "bg-white text-black hover:bg-gray-50"
                    }`}
                    style={mode === "test" ? {backgroundColor: 'rgba(0, 64, 255, 1)'} : {}}
                  >
                    {t('test')}
                  </button>
                </div>
                <p className="text-sm text-black mt-2">{t('modeDescription')}</p>
              </div>
            )}

            {/* API Key Input */}
            {initialLoading ? (
              <SkeletonApiKeyInput />
            ) : (
              <div>
                <div className="flex items-center gap-2 mb-2">
                  <label className="text-sm font-medium text-black">
                    {mode === "live" ? t('liveApiKey') : t('testApiKey')}
                  </label>
                  {isConnected && (
                    <div className="flex items-center gap-1 text-green-600">
                      <CheckCircle className="h-4 w-4" />
                      <span className="text-sm font-medium">{t('connected')}</span>
                    </div>
                  )}
                </div>

                <div className="relative">
                  <Input
                    type={showApiKey ? "text" : "password"}
                    value={apiKey}
                    onChange={(e: React.ChangeEvent<HTMLInputElement>) => setApiKey(e.target.value)}
                    placeholder={t('apiKeyPlaceholder')}
                    className="pr-20 h-12 text-base border-gray-300"
                  />
                  {apiKey && (
                    <button
                      type="button"
                      onClick={() => setShowApiKey(!showApiKey)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 hover:text-gray-700"
                    >
                      {showApiKey ? t('hide') : t('show')}
                    </button>
                  )}
                </div>

                {!isConnected && (
                  <p className="text-sm text-black mt-2">{t('apiKeyDescription', mode)}</p>
                )}
              </div>
            )}

            {/* Connect Button and Actions */}
            {initialLoading ? (
              <>
                <SkeletonConnectButton />
                <div className="flex items-center gap-2">
                  <div className="h-4 w-4 bg-gray-200 rounded animate-pulse"></div>
                  <div className="h-4 bg-gray-200 rounded w-48 animate-pulse"></div>
                </div>
              </>
            ) : (
              <>
                <Button
                  onClick={handleConnect}
                  disabled={!apiKey.trim() || isLoading}
                  className="w-full h-12 text-base font-medium text-white mb-4 hover:opacity-90"
                  style={{backgroundColor: 'rgba(0, 64, 255, 1)'}}
                >
                  {isLoading ? t('connecting') : t('connect')}
                </Button>

                {/* Connection Status - only show after successful connect action */}
                {justConnected && (
                  <div className="flex items-center gap-2 mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                    <CheckCircle className="h-5 w-5 text-green-600" />
                    <span className="text-green-800 font-medium">{t('connectedSuccessfully')}</span>
                  </div>
                )}

                {/* Error Message */}
                {errorMessage && (
                  <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                    <span className="text-red-800 text-sm">{errorMessage}</span>
                  </div>
                )}

                {/* API Key Help Link */}
                <div className="flex items-center gap-2">
                  <ExternalLink className="h-4 w-4" style={{color: 'rgba(0, 64, 255, 1)'}} />
                  <a href="https://my.mollie.com/dashboard" target="_blank" rel="noopener noreferrer" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                    {t('whereApiKey')}
                  </a>
                </div>
              </>
            )}
          </div>
        </div>

        <div className="pt-8">
          {/* Need Help Section */}
          <div>
            <div className="border-t border-gray-200 mb-4"></div>
            <h3 className="text-xl font-medium text-black mb-12 text-center">{t('needHelp')}</h3>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center p-6 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-shadow">
                <h4 className="font-medium text-black mb-3">{t('getStarted')}</h4>
                <a href="https://docs.mollie.com" target="_blank" rel="noopener noreferrer" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  {t('mollieDocumentation')}
                </a>
              </div>

              <div className="text-center p-6 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-shadow">
                <h4 className="font-medium text-black mb-3">{t('paymentsQuestions')}</h4>
                <a href="https://help.mollie.com" target="_blank" rel="noopener noreferrer" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  {t('contactMollieSupport')}
                </a>
              </div>

              <div className="text-center p-6 border border-gray-200 rounded-lg bg-white hover:shadow-sm transition-shadow">
                <h4 className="font-medium text-black mb-3">{t('integrationQuestions')}</h4>
                <a href="https://www.invertus.eu/contact" target="_blank" rel="noopener noreferrer" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  {t('contactModuleDeveloper')}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}