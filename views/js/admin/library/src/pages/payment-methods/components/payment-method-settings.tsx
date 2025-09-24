"use client"

import { useState } from "react"
import { Input } from "../../../shared/components/ui/input"
import { Label } from "../../../shared/components/ui/label"
import { Switch } from "../../../shared/components/ui/switch"
import { ChevronDown } from "lucide-react"
import { cn } from "../../../shared/lib/utils"
import type { PaymentMethod } from "../../../services/PaymentMethodsApiService"

interface PaymentMethodSettingsProps {
  method: PaymentMethod
  onUpdateSettings: (settings: Partial<PaymentMethod["settings"]>) => void
  onSaveSettings: () => void
  isSaving?: boolean
}

interface RadioSelectProps {
  value: string
  onValueChange: (value: string) => void
  options: { value: string; label: string }[]
  placeholder?: string
  className?: string
}

interface MultiSelectProps {
  value: string[]
  onValueChange: (value: string[]) => void
  options: { value: string; label: string }[]
  placeholder?: string
  className?: string
}

function RadioSelect({ value, onValueChange, options, placeholder, className }: RadioSelectProps) {
  const [isOpen, setIsOpen] = useState(false)
  const selectedOption = options.find((opt) => opt.value === value)

  return (
    <div className={cn("relative", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="w-full flex items-center justify-between px-4 py-3 text-sm border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-md min-h-[44px]"
      >
        <span className={cn(selectedOption ? "text-foreground" : "text-muted-foreground")}>
          {selectedOption?.label || placeholder || "Select option"}
        </span>
        <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
        <div className="absolute z-[9999] w-full mt-1 bg-popover border border-border rounded-md shadow-lg animate-in fade-in slide-in-from-top-1 duration-150 ease-out">
          <div className="p-1">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => {
                  onValueChange(option.value)
                  setIsOpen(false)
                }}
                className="w-full flex items-center gap-3 px-3 py-3 text-sm hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-sm"
              >
                <div className="flex items-center justify-center w-4 h-4 shrink-0">
                  <div
                    className={cn(
                      "w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors",
                      value === option.value ? "border-blue-600 bg-blue-600" : "border-input",
                    )}
                  >
                    {value === option.value && <div className="w-2 h-2 rounded-full bg-white" />}
                  </div>
                </div>
                <span className="flex-1 text-left text-sm">{option.label}</span>
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}

function MultiSelect({ value, onValueChange, options, placeholder, className }: MultiSelectProps) {
  const [isOpen, setIsOpen] = useState(false)
  const selectedOptions = options.filter((opt) => value.includes(opt.value))

  const toggleOption = (optionValue: string) => {
    if (value.includes(optionValue)) {
      onValueChange(value.filter((v) => v !== optionValue))
    } else {
      onValueChange([...value, optionValue])
    }
  }

  return (
    <div className={cn("relative", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="w-full flex items-center justify-between px-4 py-3 text-sm border border-input bg-background hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-md min-h-[44px]"
      >
        <span className={cn(selectedOptions.length > 0 ? "text-foreground" : "text-muted-foreground")}>
          {selectedOptions.length > 0 ? `${selectedOptions.length} selected` : placeholder || "Select options"}
        </span>
        <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
        <div className="absolute z-[9999] w-full mt-1 bg-popover border border-border rounded-md shadow-lg animate-in fade-in slide-in-from-top-1 duration-150 ease-out">
          <div className="p-1">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => toggleOption(option.value)}
                className="w-full flex items-center gap-3 px-3 py-3 text-sm hover:bg-accent hover:text-accent-foreground cursor-pointer rounded-sm"
              >
                <div className="flex items-center justify-center w-4 h-4 shrink-0">
                  <div
                    className={cn(
                      "w-4 h-4 rounded-sm border-2 flex items-center justify-center transition-colors",
                      value.includes(option.value) ? "border-blue-600 bg-blue-600" : "border-input",
                    )}
                  >
                    {value.includes(option.value) && (
                      <svg className="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path
                          fillRule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clipRule="evenodd"
                        />
                      </svg>
                    )}
                  </div>
                </div>
                <span className="flex-1 text-left text-sm">{option.label}</span>
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}

export function PaymentMethodSettings({ method, onUpdateSettings, onSaveSettings, isSaving = false }: PaymentMethodSettingsProps) {
  const [showRestrictions, setShowRestrictions] = useState(false)
  const [showFees, setShowFees] = useState(false)
  const [showOrderRestrictions, setShowOrderRestrictions] = useState(false)
  const [selectedCustomerGroups, setSelectedCustomerGroups] = useState<string[]>([])

  return (
    <div className="space-y-6">
      {/* Basic Settings */}
      <div>
        <h3 className="text-sm font-medium mb-4">Basic settings</h3>
        <div className="grid grid-cols-2 gap-6">
          <div className="space-y-4">
            <div className="space-y-1">
              <div className="flex items-center gap-3">
                <Label className="text-sm font-medium">Activate/Deactivate</Label>
                <Switch
                  checked={method.settings.enabled}
                  onCheckedChange={(enabled: boolean) => onUpdateSettings({ enabled })}
                />
              </div>
              <p className="text-sm text-muted-foreground">Enable payment method</p>
            </div>

            <div className="space-y-1">
              <div className="flex items-center gap-3">
                <Label className="text-sm font-medium">Use embedded credit card form in the checkout</Label>
                <Switch
                  checked={method.settings.mollieComponents}
                  onCheckedChange={(mollieComponents: boolean) => onUpdateSettings({ mollieComponents })}
                />
              </div>
              <p className="text-sm text-muted-foreground">Enable Mollie Components</p>
            </div>
          </div>

          <div className="space-y-4">
            <div>
              <Label htmlFor="payment-title" className="text-sm font-medium">
                Payment Title
              </Label>
              <Input
                id="payment-title"
                placeholder="Payment Method #1"
                value={method.settings.title}
                onChange={(e) => onUpdateSettings({ title: e.target.value })}
                className="mt-1"
              />
            </div>

            <div className="space-y-1">
              <div className="flex items-center gap-3">
                <Label className="text-sm font-medium">
                  Let customer save their credit card data for future orders
                </Label>
                <Switch
                  checked={method.settings.oneClickPayments}
                  onCheckedChange={(oneClickPayments: boolean) => onUpdateSettings({ oneClickPayments })}
                />
              </div>
              <p className="text-sm text-muted-foreground">Use one-click payments</p>
            </div>
          </div>
        </div>
      </div>

      {/* API Selection and Transaction Description */}
      <div className="grid grid-cols-2 gap-6">
        <div>
          <h3 className="text-sm font-medium mb-0">API Selection</h3>
          <div className="flex border border-input rounded-md w-full mt-1 overflow-hidden">
            <button
              onClick={() => onUpdateSettings({ apiSelection: "payments" })}
              className={cn(
                "flex-1 px-4 py-2 text-sm font-medium transition-colors cursor-pointer border-r border-input last:border-r-0",
                method.settings.apiSelection === "payments"
                  ? "text-white bg-blue-600"
                  : "text-muted-foreground hover:text-foreground bg-background hover:bg-accent",
              )}
            >
              Payments
            </button>
            <button
              onClick={() => onUpdateSettings({ apiSelection: "orders" })}
              className={cn(
                "flex-1 px-4 py-2 text-sm font-medium transition-colors cursor-pointer",
                method.settings.apiSelection === "orders"
                  ? "text-white bg-blue-600"
                  : "text-muted-foreground hover:text-foreground bg-background hover:bg-accent",
              )}
            >
              Orders
            </button>
          </div>
          <p className="text-xs text-muted-foreground mt-2">
            <a
              href="https://docs.mollie.com/payments/overview"
              target="_blank"
              rel="noopener noreferrer"
              className="text-muted-foreground underline cursor-pointer hover:text-muted-foreground/80"
            >
              Read more
            </a>
            <span className="text-muted-foreground"> about the differences between Payments and Orders API</span>
          </p>
        </div>

        <div>
          <Label htmlFor="transaction-description" className="text-sm font-medium">
            Transaction Description
          </Label>
          <Input
            id="transaction-description"
            placeholder="Enter transaction description"
            value={method.settings.transactionDescription}
            onChange={(e) => onUpdateSettings({ transactionDescription: e.target.value })}
            className="mt-1"
          />
        </div>
      </div>

      {/* Collapsible Sections */}
      <div className="space-y-4">
        {/* Payment Restrictions */}
        <div className="border rounded-lg overflow-hidden">
          <button
            onClick={() => setShowRestrictions(!showRestrictions)}
            className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <span className="font-medium">Payment restrictions</span>
            <ChevronDown
              className={cn("h-4 w-4 transition-transform duration-200", showRestrictions && "rotate-180")}
            />
          </button>
          {showRestrictions && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Accept payments from</Label>
                  <RadioSelect
                    value="all"
                    onValueChange={() => {}}
                    options={[
                      { value: "all", label: "All countries" },
                      { value: "specific", label: "Specific countries" },
                    ]}
                    placeholder="All countries"
                    className="mt-1"
                  />
                </div>
                <div>
                  <Label className="text-sm font-medium">Exclude payments from specific countries</Label>
                  <RadioSelect
                    value="belgium"
                    onValueChange={() => {}}
                    options={[
                      { value: "belgium", label: "Belgium" },
                      { value: "us", label: "United States" },
                      { value: "uk", label: "United Kingdom" },
                    ]}
                    placeholder="Belgium"
                    className="mt-1"
                  />
                </div>
              </div>
              <div>
                <Label className="text-sm font-medium">Exclude Customer Groups</Label>
                <MultiSelect
                  value={selectedCustomerGroups}
                  onValueChange={setSelectedCustomerGroups}
                  options={[
                    { value: "guest", label: "Guest" },
                    { value: "group1", label: "Customer Group 1" },
                    { value: "group2", label: "Customer Group 2" },
                    { value: "group3", label: "Customer Group 3" },
                  ]}
                  placeholder="Select customer groups"
                  className="mt-1"
                />
              </div>
            </div>
          )}
        </div>

        {/* Payment Fees */}
        <div className="border rounded-lg overflow-hidden">
          <button
            onClick={() => setShowFees(!showFees)}
            className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <span className="font-medium">Payment fees</span>
            <ChevronDown className={cn("h-4 w-4 transition-transform duration-200", showFees && "rotate-180")} />
          </button>
          {showFees && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              <div className="space-y-1">
                <div className="flex items-center gap-3">
                  <Label className="text-sm font-medium">Enable/Disable</Label>
                  <Switch
                    checked={method.settings.paymentFees.enabled}
                    onCheckedChange={(enabled: boolean) =>
                      onUpdateSettings({
                        paymentFees: { ...method.settings.paymentFees, enabled },
                      })
                    }
                  />
                </div>
                <p className="text-xs text-muted-foreground">Enable payment fee</p>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Payment fee type</Label>
                  <RadioSelect
                    value="fixed"
                    onValueChange={() => {}}
                    options={[
                      { value: "fixed", label: "Fixed fee" },
                      { value: "percentage", label: "Percentage" },
                    ]}
                    placeholder="Fixed fee"
                    className="mt-1"
                  />
                </div>
                <div>
                  <Label className="text-sm font-medium">Payment fee tax group</Label>
                  <RadioSelect
                    value="b2c"
                    onValueChange={() => {}}
                    options={[
                      { value: "b2c", label: "B2C" },
                      { value: "b2b", label: "B2B" },
                    ]}
                    placeholder="B2C"
                    className="mt-1"
                  />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Maximum fee</Label>
                  <Input type="number" placeholder="50000" className="mt-1" />
                </div>
                <div>
                  <Label className="text-sm font-medium">Minimum amount</Label>
                  <Input type="number" placeholder="1.5" className="mt-1" />
                </div>
              </div>
              <div>
                <Label className="text-sm font-medium">Maximum amount</Label>
                <Input type="number" placeholder="1.8" className="mt-1" />
              </div>
            </div>
          )}
        </div>

        {/* Order Restrictions */}
        <div className="border rounded-lg overflow-hidden">
          <button
            onClick={() => setShowOrderRestrictions(!showOrderRestrictions)}
            className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <span className="font-medium">Order restrictions</span>
            <ChevronDown
              className={cn("h-4 w-4 transition-transform duration-200", showOrderRestrictions && "rotate-180")}
            />
          </button>
          {showOrderRestrictions && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">Max Order Amount</Label>
                  <Input type="number" placeholder="Enter maximum order amount" className="mt-1" />
                </div>
                <div>
                  <Label className="text-sm font-medium">Min Order Amount</Label>
                  <Input type="number" placeholder="Enter minimum order amount" className="mt-1" />
                </div>
              </div>
            </div>
          )}
        </div>
      </div>

      {/* Save Button */}
      <div className="flex justify-end pt-6 border-t">
        <button
          onClick={onSaveSettings}
          disabled={isSaving}
          className={cn(
            "px-6 py-2 text-sm font-medium rounded-md transition-colors",
            "bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed",
            isSaving && "bg-blue-500"
          )}
        >
          {isSaving ? "Saving..." : "Save"}
        </button>
      </div>
    </div>
  )
}
