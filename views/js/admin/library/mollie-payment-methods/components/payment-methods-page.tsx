"use client"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Info } from "lucide-react"
import { PaymentMethodTabs } from "./payment-method-tabs"
import { PaymentMethodsList } from "./payment-methods-list"
import type { PaymentMethod } from "./types/payment-method"

const initialEnabledMethods: PaymentMethod[] = [
  {
    id: "1",
    name: "Card",
    type: "card",
    status: "active",
    isExpanded: false,
    settings: {
      enabled: true,
      title: "Payment Method #1",
      mollieComponents: false,
      oneClickPayments: true,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "2",
    name: "Card",
    type: "card",
    status: "active",
    isExpanded: true,
    settings: {
      enabled: true,
      title: "Payment Method #1",
      mollieComponents: false,
      oneClickPayments: true,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "3",
    name: "Card",
    type: "card",
    status: "active",
    isExpanded: false,
    settings: {
      enabled: true,
      title: "Payment Method #1",
      mollieComponents: false,
      oneClickPayments: true,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "4",
    name: "Card",
    type: "card",
    status: "active",
    isExpanded: false,
    settings: {
      enabled: true,
      title: "Payment Method #1",
      mollieComponents: false,
      oneClickPayments: true,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "5",
    name: "Card",
    type: "card",
    status: "active",
    isExpanded: false,
    settings: {
      enabled: true,
      title: "Payment Method #1",
      mollieComponents: false,
      oneClickPayments: true,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
]

const initialDisabledMethods: PaymentMethod[] = [
  {
    id: "d1",
    name: "Payment Method #5",
    type: "other",
    status: "inactive",
    isExpanded: false,
    settings: {
      enabled: false,
      title: "Payment Method #5",
      mollieComponents: false,
      oneClickPayments: false,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "d2",
    name: "Payment Method #5",
    type: "other",
    status: "inactive",
    isExpanded: false,
    settings: {
      enabled: false,
      title: "Payment Method #5",
      mollieComponents: false,
      oneClickPayments: false,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "d3",
    name: "Payment Method #5",
    type: "other",
    status: "inactive",
    isExpanded: false,
    settings: {
      enabled: false,
      title: "Payment Method #5",
      mollieComponents: false,
      oneClickPayments: false,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
  {
    id: "d4",
    name: "Payment Method #5",
    type: "other",
    status: "inactive",
    isExpanded: false,
    settings: {
      enabled: false,
      title: "Payment Method #5",
      mollieComponents: false,
      oneClickPayments: false,
      transactionDescription: "",
      apiSelection: "payments",
      paymentRestrictions: {
        acceptFrom: "all",
        excludeCountries: [],
        excludeCustomerGroups: [],
      },
      paymentFees: {
        enabled: false,
        type: "fixed",
        taxGroup: "",
        maxFee: "",
        minAmount: "",
        maxAmount: "",
      },
      orderRestrictions: {
        minAmount: "",
        maxAmount: "",
      },
    },
  },
]

export default function PaymentMethodsPage() {
  const [activeTab, setActiveTab] = useState<"enabled" | "disabled">("enabled")
  const [enabledMethods, setEnabledMethods] = useState<PaymentMethod[]>(initialEnabledMethods)
  const [disabledMethods, setDisabledMethods] = useState<PaymentMethod[]>(initialDisabledMethods)
  const [hasChanges, setHasChanges] = useState(false)

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
    setHasChanges(true)
  }

  const handleReorder = (newMethods: PaymentMethod[]) => {
    if (activeTab === "enabled") {
      setEnabledMethods(newMethods)
    } else {
      setDisabledMethods(newMethods)
    }
    setHasChanges(true)
  }

  const currentMethods = activeTab === "enabled" ? enabledMethods : disabledMethods

  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="space-y-2">
        <h1 className="text-2xl font-semibold text-foreground">Available Payment Methods</h1>
        <p className="text-sm text-muted-foreground">Expand each method to view and configure settings.</p>
      </div>

      {/* Info Banner */}
      <div className="bg-cyan-50 border border-cyan-200 rounded-lg p-4 flex items-start gap-3">
        <Info className="h-5 w-5 text-cyan-600 mt-0.5 flex-shrink-0" />
        <div className="text-sm text-cyan-800">
          Here you can see all of the {activeTab === "enabled" ? "enabled" : "disabled"} payment options. To include new
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
        onToggleExpanded={toggleExpanded}
        onUpdateSettings={updateMethodSettings}
        onReorder={handleReorder}
      />

      {/* Save Button */}
      <div className="flex justify-end pt-4">
        <Button className="bg-blue-600 hover:bg-blue-700 text-white px-8 cursor-pointer" disabled={!hasChanges}>
          Save
        </Button>
      </div>
    </div>
  )
}
