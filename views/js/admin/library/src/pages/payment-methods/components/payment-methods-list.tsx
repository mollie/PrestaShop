"use client"

import { useState, useEffect, useCallback, useRef } from "react"
import { CheckCircle, XCircle } from "lucide-react"
import { PaymentMethodTabs } from "./payment-method-tabs"
import { PaymentMethodsList } from "./payment-methods-list-component"
import { PaymentMethodsSkeleton } from "./payment-methods-skeleton"
import { paymentMethodsApiService, type PaymentMethod, type Country, type CustomerGroup, type Language } from "../../../services/PaymentMethodsApiService"
import { usePaymentMethodsTranslations } from "../../../shared/hooks/use-payment-methods-translations"

export default function PaymentMethodsPage() {
  const { t } = usePaymentMethodsTranslations()
  const [activeTab, setActiveTab] = useState<"enabled" | "disabled">("enabled")

  // Collapse all methods by default when switching tabs
  const handleTabChange = (tab: "enabled" | "disabled") => {
    setActiveTab(tab);
    setEnabledMethods((prev) => prev.map((m) => ({ ...m, isExpanded: false })));
    setDisabledMethods((prev) => prev.map((m) => ({ ...m, isExpanded: false })));
  }

  const [notification, setNotification] = useState<{ message: string; type: 'success' | 'error' } | null>(null)
  const [enabledMethods, setEnabledMethods] = useState<PaymentMethod[]>([])
  const [disabledMethods, setDisabledMethods] = useState<PaymentMethod[]>([])
  const [countries, setCountries] = useState<Country[]>([])
  const [customerGroups, setCustomerGroups] = useState<CustomerGroup[]>([])
  const [languages, setLanguages] = useState<Language[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [errorMessage, setErrorMessage] = useState("")
  const [savingMethodId, setSavingMethodId] = useState<string | null>(null)
  const [isReorderingSaving, setIsReorderingSaving] = useState(false)

  const loadPaymentMethods = useCallback(async () => {
    try {
      setIsLoading(true)
      setErrorMessage("")

      const response = await paymentMethodsApiService.getPaymentMethods()
      if (response.success && response.data) {
        // Convert backend data to frontend format
        const methods = response.data.methods
        const enabled = methods.filter(m => m.status === 'active')
        const disabled = methods.filter(m => m.status === 'inactive')

        setEnabledMethods(enabled)
        setDisabledMethods(disabled)
        setCountries(response.data.countries || [])
        setCustomerGroups(response.data.customerGroups || [])
        setLanguages(response.data.languages || [])
      } else {
        setErrorMessage(response.message || t('loadingError'))
      }
    } catch (error) {
      console.error('Failed to load payment methods:', error)
      setErrorMessage(t('loadingError'))
    } finally {
      setIsLoading(false)
    }
  }, [t])

  // Load payment methods on component mount - using ref to break dependency cycle
  const loadPaymentMethodsRef = useRef(loadPaymentMethods)
  loadPaymentMethodsRef.current = loadPaymentMethods

  useEffect(() => {
    loadPaymentMethodsRef.current()
  }, []) // Empty dependency array - only run once on mount

  const toggleExpanded = (id: string) => {
    const updateMethods = (methods: PaymentMethod[]) =>
      methods.map((method) => (method.id === id ? { ...method, isExpanded: !method.isExpanded } : method))

    if (activeTab === "enabled") {
      setEnabledMethods(updateMethods(enabledMethods))
    } else {
      setDisabledMethods(updateMethods(disabledMethods))
    }
  }

  const updateMethodSettings = (id: string, settingsUpdate: Partial<PaymentMethod["settings"]>) => {
    const updateMethods = (methods: PaymentMethod[]) =>
      methods.map((method) =>
        method.id === id ? { ...method, settings: { ...method.settings, ...settingsUpdate } } : method,
      )

    if (activeTab === "enabled") {
      setEnabledMethods(updateMethods(enabledMethods))
    } else {
      setDisabledMethods(updateMethods(disabledMethods))
    }
  }

  const handleReorder = async (newMethods: PaymentMethod[]) => {
    // Update UI immediately for better UX
    if (activeTab === "enabled") {
      setEnabledMethods(newMethods)
    } else {
      setDisabledMethods(newMethods)
    }

    // Save the new order to the backend
    setIsReorderingSaving(true)
    try {
      const methodIds = newMethods.map(method => method.id)
      const response = await paymentMethodsApiService.updateMethodsOrder(methodIds)

      if (response.success) {
        // Show success notification
        setNotification({
          message: response.message || t('paymentMethodsOrderUpdated'),
          type: 'success'
        })
      } else {
        // Show error and revert to original order
        setNotification({
          message: response.message || t('failedToUpdateOrder'),
          type: 'error'
        })
        // Reload methods to restore original order
        await loadPaymentMethods()
      }
    } catch (error) {
      console.error('Failed to update payment methods order:', error)
      setNotification({
        message: t('failedToUpdateOrder'),
        type: 'error'
      })
      // Reload methods to restore original order
      await loadPaymentMethods()
    } finally {
      setIsReorderingSaving(false)
    }
  }

  const saveMethodSettings = async (methodId: string) => {
    try {
      setSavingMethodId(methodId)

      // Find the method to save
      const method = [...enabledMethods, ...disabledMethods].find(m => m.id === methodId)
      if (!method) {
        console.error('Method not found:', methodId)
        setNotification({ message: t('paymentMethodNotFound'), type: 'error' })
        setSavingMethodId(null)
        return
      }

      // Call the API to save settings
      const response = await paymentMethodsApiService.savePaymentMethodSettings(methodId, method.settings)

      if (response.success) {
        // Show success notification FIRST (before any updates)
        setNotification({ message: response.message || t('settingsSavedSuccessfully'), type: 'success' })

        // Reload ONLY the saved payment method data from server to ensure fresh state
        // This is more efficient than reloading all methods
        const freshDataResponse = await paymentMethodsApiService.getPaymentMethods()

        if (freshDataResponse.success && freshDataResponse.data) {
          const freshMethod = freshDataResponse.data.methods.find((m: PaymentMethod) => m.id === methodId)

          if (freshMethod) {
            // Preserve the expanded state after save
            freshMethod.isExpanded = true

            // Update method and move between tabs based on enabled status
            if (freshMethod.settings.enabled) {
              // Method is enabled - move to or update in enabled array
              setEnabledMethods(prev => {
                const exists = prev.some(m => m.id === methodId)
                if (exists) {
                  // Update existing
                  return prev.map(m => m.id === methodId ? freshMethod : m)
                } else {
                  // Add new (moved from disabled)
                  return [...prev, freshMethod]
                }
              })
              // Remove from disabled array
              setDisabledMethods(prev => prev.filter(m => m.id !== methodId))
            } else {
              // Method is disabled - move to or update in disabled array
              setDisabledMethods(prev => {
                const exists = prev.some(m => m.id === methodId)
                if (exists) {
                  // Update existing
                  return prev.map(m => m.id === methodId ? freshMethod : m)
                } else {
                  // Add new (moved from enabled)
                  return [...prev, freshMethod]
                }
              })
              // Remove from enabled array
              setEnabledMethods(prev => prev.filter(m => m.id !== methodId))
            }
          }
        }
      } else {
        // Show error but DON'T clear all methods
        setNotification({ message: response.message || t('failedToSaveSettings'), type: 'error' })
      }
    } catch (error) {
      console.error('Failed to save payment method settings:', error)
      // Show error but DON'T clear all methods
      setNotification({ message: t('failedToSaveSettings'), type: 'error' })
    } finally {
      setSavingMethodId(null)
    }
  }

  // Auto-hide notification after 5 seconds
  useEffect(() => {
    if (notification) {
      const timer = setTimeout(() => {
        setNotification(null)
      }, 5000)
      return () => clearTimeout(timer)
    }
  }, [notification])

  const currentMethods = activeTab === "enabled" ? enabledMethods : disabledMethods

  if (isLoading) {
    return <PaymentMethodsSkeleton />
  }

  if (errorMessage) {
    return (
      <div className="max-w-6xl mx-auto p-6 space-y-6">
        <div className="space-y-2">
          <h1 className="text-2xl font-semibold text-foreground">{t('paymentMethods')}</h1>
          <p className="text-sm text-muted-foreground">{t('configurePaymentMethods')}</p>
        </div>
        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 flex items-start gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="text-yellow-600 mt-0.5 flex-shrink-0">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 16v-4"></path>
            <path d="M12 8h.01"></path>
          </svg>
          <div className="text-sm text-yellow-800">
            <p className="font-medium mb-2">API not configured</p>
            <p>Please configure your Mollie API keys in the <strong>API Configuration</strong> tab before managing payment methods.</p>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Notification Banner - Fixed position in right corner */}
      {notification && (
        <div
          className={`fixed right-6 top-6 z-[9999] border rounded-lg p-4 flex items-center gap-3 shadow-lg min-w-[320px] max-w-[500px] ${
            notification.type === 'success'
              ? 'bg-green-50 border-green-200'
              : 'bg-red-50 border-red-200'
          }`}
          style={{ animation: 'slideInRight 0.3s ease-out' }}
        >
          {notification.type === 'success' ? (
            <CheckCircle className="h-5 w-5 text-green-600 flex-shrink-0" />
          ) : (
            <XCircle className="h-5 w-5 text-red-600 flex-shrink-0" />
          )}
          <div className={`text-sm font-medium ${notification.type === 'success' ? 'text-green-800' : 'text-red-800'}`}>
            {notification.message}
          </div>
          <button
            onClick={() => setNotification(null)}
            className="ml-auto text-gray-500 hover:text-gray-700"
          >
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      )}

      {/* Header */}
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold text-foreground">{t('paymentMethods')}</h1>
        <p className="text-sm text-muted-foreground">{t('configurePaymentMethods')}</p>
      </div>

      {/* Info Banner */}
      <div className="bg-cyan-50 border border-cyan-200 rounded-lg p-4 flex items-start gap-3">
        <div className="text-sm text-cyan-800">
          Here you can see all of the {activeTab === "enabled" ? t('enabled') : t('disabled')} payment options. To include new
          payment methods go to{" "}
          <a
            href="https://www.mollie.com/dashboard/developers/api-keys"
            target="_blank"
            rel="noopener noreferrer"
            className="font-medium cursor-pointer hover:underline text-cyan-700 hover:text-cyan-900"
          >
            Mollie dashboard
          </a>
        </div>
      </div>

      {/* Tabs */}
  <PaymentMethodTabs activeTab={activeTab} onTabChange={handleTabChange} />

      {/* Reordering indicator */}
      {isReorderingSaving && (
        <div className="fixed bottom-6 right-6 z-[9999] bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center gap-3 shadow-lg">
          <svg className="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span className="text-sm font-medium text-blue-800">{t('savingNewOrder')}</span>
        </div>
      )}

      {/* Payment Methods List */}
      {currentMethods.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-gray-500 text-lg mb-2">{t('noPaymentMethods')}</div>
          <div className="text-gray-400 text-sm">{t('paymentMethodsWillAppear')}</div>
        </div>
      ) : (
        <PaymentMethodsList
          methods={currentMethods}
          countries={countries}
          customerGroups={customerGroups}
          languages={languages}
          onToggleExpanded={toggleExpanded}
          onUpdateSettings={updateMethodSettings}
          onSaveSettings={saveMethodSettings}
          onReorder={handleReorder}
          savingMethodId={savingMethodId || undefined}
          isDragEnabled={activeTab === "enabled" && !isReorderingSaving}
        />
      )}
    </div>
  )
}