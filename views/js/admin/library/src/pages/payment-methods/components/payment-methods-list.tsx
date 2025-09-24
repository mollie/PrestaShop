"use client"

import { useState, useEffect } from "react"
import { Info } from "lucide-react"
import { PaymentMethodTabs } from "./payment-method-tabs"
import { PaymentMethodsList } from "./payment-methods-list-component"
import { paymentMethodsApiService, type PaymentMethod, type Country, type CustomerGroup } from "../../../services/PaymentMethodsApiService"
import { usePaymentMethodsTranslations } from "../../../shared/hooks/use-payment-methods-translations"

export default function PaymentMethodsPage() {
  const { t } = usePaymentMethodsTranslations()
  const [activeTab, setActiveTab] = useState<"enabled" | "disabled">("enabled")
  const [enabledMethods, setEnabledMethods] = useState<PaymentMethod[]>([])
  const [disabledMethods, setDisabledMethods] = useState<PaymentMethod[]>([])
  const [countries, setCountries] = useState<Country[]>([])
  const [customerGroups, setCustomerGroups] = useState<CustomerGroup[]>([])
  const [isLoading, setIsLoading] = useState(true)
  const [errorMessage, setErrorMessage] = useState("")
  const [savingMethodId, setSavingMethodId] = useState<string | null>(null)

  // Load payment methods on component mount
  useEffect(() => {
    loadPaymentMethods()
  }, [])

  const loadPaymentMethods = async () => {
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
      } else {
        setErrorMessage(response.message || t('loadingError'))
      }
    } catch (error) {
      console.error('Failed to load payment methods:', error)
      setErrorMessage(t('loadingError'))
    } finally {
      setIsLoading(false)
    }
  }

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

  const handleReorder = (newMethods: PaymentMethod[]) => {
    if (activeTab === "enabled") {
      setEnabledMethods(newMethods)
    } else {
      setDisabledMethods(newMethods)
    }
  }

  const saveMethodSettings = async (methodId: string) => {
    try {
      setSavingMethodId(methodId)

      // Find the method to save
      const method = [...enabledMethods, ...disabledMethods].find(m => m.id === methodId)
      if (!method) {
        console.error('Method not found:', methodId)
        return
      }

      // Call the API to save settings
      const response = await paymentMethodsApiService.savePaymentMethodSettings(methodId, method.settings)

      if (response.success) {
        // Reload to get fresh data
        await loadPaymentMethods()
      } else {
        setErrorMessage(response.message || 'Failed to save settings')
      }
    } catch (error) {
      console.error('Failed to save payment method settings:', error)
      setErrorMessage('Failed to save settings')
    } finally {
      setSavingMethodId(null)
    }
  }

  const currentMethods = activeTab === "enabled" ? enabledMethods : disabledMethods

  if (isLoading) {
    return (
      <div className="max-w-6xl mx-auto p-6 space-y-6">
        <div className="space-y-2">
          <h1 className="text-2xl font-semibold text-foreground">{t('paymentMethods')}</h1>
          <p className="text-sm text-muted-foreground">{t('loadingMethods')}</p>
        </div>
      </div>
    )
  }

  if (errorMessage) {
    return (
      <div className="max-w-6xl mx-auto p-6 space-y-6">
        <div className="space-y-2">
          <h1 className="text-2xl font-semibold text-foreground">{t('paymentMethods')}</h1>
          <p className="text-sm text-red-600">{errorMessage}</p>
        </div>
      </div>
    )
  }

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold text-foreground">{t('paymentMethods')}</h1>
        <p className="text-sm text-muted-foreground">{t('configurePaymentMethods')}</p>
      </div>

      {/* Info Banner */}
      <div className="bg-cyan-50 border border-cyan-200 rounded-lg p-4 flex items-start gap-3">
        <Info className="h-5 w-5 text-cyan-600 mt-0.5 flex-shrink-0" />
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
      <PaymentMethodTabs activeTab={activeTab} onTabChange={setActiveTab} />

      {/* Payment Methods List */}
      <PaymentMethodsList
        methods={currentMethods}
        countries={countries}
        customerGroups={customerGroups}
        onToggleExpanded={toggleExpanded}
        onUpdateSettings={updateMethodSettings}
        onSaveSettings={saveMethodSettings}
        onReorder={handleReorder}
        savingMethodId={savingMethodId || undefined}
      />
    </div>
  )
}