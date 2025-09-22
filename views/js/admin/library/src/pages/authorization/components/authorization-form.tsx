"use client"

import { useState, useEffect } from "react"
import { ExternalLink, CheckCircle } from "lucide-react"
import { Button } from "../../../shared/components/ui/button"
import { Input } from "../../../shared/components/ui/input"
import { authApiService } from "../../../services/AuthenticationApiService"

export default function AuthorizationForm() {
  const [mode, setMode] = useState<"live" | "test">("live")
  const [apiKey, setApiKey] = useState("")
  const [isConnected, setIsConnected] = useState(false)
  const [showApiKey, setShowApiKey] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const [testApiKey, setTestApiKey] = useState("")
  const [liveApiKey, setLiveApiKey] = useState("")
  const [errorMessage, setErrorMessage] = useState("")
  const [justConnected, setJustConnected] = useState(false)

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
        setErrorMessage(saveResponse.message || "Failed to save API key")
      }
    } catch (error) {
      console.error('Failed to connect:', error)
      setIsConnected(false)
      setJustConnected(false)
      setErrorMessage("Connection failed. Please check your API key.")
    } finally {
      setIsLoading(false)
    }
  }

  const handleModeChange = async (newMode: "live" | "test") => {
    setMode(newMode)
    setApiKey(newMode === "live" ? liveApiKey : testApiKey)
    setErrorMessage("")
    setJustConnected(false) // Clear the success message when switching modes
    
    try {
      // Call backend to switch environment
      const switchResponse = await authApiService.switchEnvironment(newMode)
      if (switchResponse.success) {
        // Update connection status based on backend response
        setIsConnected(switchResponse.data.is_connected || false)
        setApiKey(switchResponse.data.api_key || "")
        
        // Update the stored keys based on the response
        if (newMode === "live") {
          setLiveApiKey(switchResponse.data.api_key || liveApiKey)
        } else {
          setTestApiKey(switchResponse.data.api_key || testApiKey)
        }
      } else {
        console.error('Failed to switch environment:', switchResponse.message)
        setErrorMessage(switchResponse.message || "Failed to switch environment")
      }
    } catch (error) {
      console.error('Failed to switch environment:', error)
      setErrorMessage("Failed to switch environment")
    }
  }

  return (
    <div className="bg-white font-inter">
      <div className="max-w-6xl mx-auto px-2 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start mb-8">
          {/* Left Column - Header */}
          <div className="flex flex-col">
            <div className="mb-16">
              <h1 className="text-4xl font-medium text-black mb-2">mollie</h1>
              <h2 className="text-4xl font-medium text-black mb-4">API Configuration</h2>
              <p className="text-black text-lg font-medium">Select your operational mode and input API keys below.</p>
            </div>

            <div>
              <h3 className="text-lg font-medium text-black mb-2">New to Mollie?</h3>
              <a href="#" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-base font-medium">
                Create a Mollie account
              </a>
            </div>
          </div>

          {/* Right Column - Configuration */}
          <div className="space-y-6">
            {/* Mode Toggle */}
            <div>
              <label className="block text-sm font-medium text-black mb-2">Mode</label>
              <div className="flex rounded-lg overflow-hidden border border-gray-200">
                <button
                  onClick={() => handleModeChange("live")}
                  className={`flex-1 px-6 py-3 text-sm font-medium transition-colors ${
                    mode === "live" ? "text-white" : "bg-white text-black hover:bg-gray-50"
                  }`}
                  style={mode === "live" ? {backgroundColor: 'rgba(0, 64, 255, 1)'} : {}}
                >
                  Live
                </button>
                <button
                  onClick={() => handleModeChange("test")}
                  className={`flex-1 px-6 py-3 text-sm font-medium transition-colors ${
                    mode === "test" ? "text-white" : "bg-white text-black hover:bg-gray-50"
                  }`}
                  style={mode === "test" ? {backgroundColor: 'rgba(0, 64, 255, 1)'} : {}}
                >
                  Test
                </button>
              </div>
              <p className="text-sm text-black mt-2">Choose operational mode for API.</p>
            </div>

            {/* API Key Input */}
            <div>
              <div className="flex items-center gap-2 mb-2">
                <label className="text-sm font-medium text-black">
                  {mode === "live" ? "Live API Key" : "Test API Key"}
                </label>
                {isConnected && (
                  <div className="flex items-center gap-1 text-green-600">
                    <CheckCircle className="h-4 w-4" />
                    <span className="text-sm font-medium">Connected</span>
                  </div>
                )}
              </div>

              <div className="relative">
                <Input
                  type={showApiKey ? "text" : "password"}
                  value={apiKey}
                  onChange={(e: React.ChangeEvent<HTMLInputElement>) => setApiKey(e.target.value)}
                  placeholder="Enter your API key here"
                  className="pr-20 h-12 text-base border-gray-300"
                />
                {apiKey && (
                  <button
                    type="button"
                    onClick={() => setShowApiKey(!showApiKey)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 hover:text-gray-700"
                  >
                    {showApiKey ? "Hide" : "Show"}
                  </button>
                )}
              </div>

              <p className="text-sm text-black mt-2">Required for connecting to the {mode} mode.</p>
            </div>

            {/* Connect Button */}
            <Button
              onClick={handleConnect}
              disabled={!apiKey.trim() || isLoading}
              className="w-full h-12 text-base font-medium text-white mb-4 hover:opacity-90"
              style={{backgroundColor: 'rgba(0, 64, 255, 1)'}}
            >
              {isLoading ? "Connecting..." : "Connect"}
            </Button>

            {/* Connection Status - only show after successful connect action */}
            {justConnected && (
              <div className="flex items-center gap-2 mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
                <CheckCircle className="h-5 w-5 text-green-600" />
                <span className="text-green-800 font-medium">Connected successfully!</span>
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
              <a href="#" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                Where can I find my API key?
              </a>
            </div>
          </div>
        </div>

        <div className="pt-8">
          {/* Need Help Section */}
          <div>
            <div className="border-t border-gray-200 mb-4"></div>
            <h3 className="text-xl font-medium text-black mb-12 text-center">Need Help?</h3>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center">
                <h4 className="font-medium text-black mb-2">Get started</h4>
                <a href="#" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  Mollie documentation
                </a>
              </div>

              <div className="text-center">
                <h4 className="font-medium text-black mb-2">Payments related questions</h4>
                <a href="#" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  Contact Mollie Support
                </a>
              </div>

              <div className="text-center">
                <h4 className="font-medium text-black mb-2">Integration questions</h4>
                <a href="#" style={{color: 'rgba(0, 64, 255, 1)'}} className="hover:opacity-80 underline text-sm font-medium">
                  Contact module developer
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}