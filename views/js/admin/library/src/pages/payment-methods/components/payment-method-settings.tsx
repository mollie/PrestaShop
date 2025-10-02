"use client"

import { useState, useRef, useEffect } from "react"
import { Input } from "../../../shared/components/ui/input"
import { Label } from "../../../shared/components/ui/label"
import { Switch } from "../../../shared/components/ui/switch"
import { CustomLogoUpload } from "../../../shared/components/ui/custom-logo-upload"
import { ApplePaySettings } from "../../../shared/components/ui/apple-pay-settings"
import { ChevronDown } from "lucide-react"
import { cn } from "../../../shared/lib/utils"
import type { PaymentMethod, Country, CustomerGroup } from "../../../services/PaymentMethodsApiService"
import { paymentMethodsApiService } from "../../../services/PaymentMethodsApiService"
import { usePaymentMethodsTranslations } from "../../../shared/hooks/use-payment-methods-translations"

interface PaymentMethodSettingsProps {
  method: PaymentMethod
  countries: Country[]
  customerGroups: CustomerGroup[]
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
  const dropdownRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside)
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isOpen])

  return (
    <div ref={dropdownRef} className={cn("relative", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="w-full flex items-center justify-between px-4 py-3 text-sm border border-input bg-background hover:bg-gray-100 hover:text-foreground cursor-pointer rounded-md min-h-[44px]"
      >
        <span className={cn(selectedOption ? "text-foreground" : "text-muted-foreground")}>
          {selectedOption?.label || placeholder}
        </span>
        <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
        <div className="absolute z-[99999] w-full mt-1 bg-popover border border-border rounded-md shadow-lg animate-in fade-in slide-in-from-top-1 duration-150 ease-out">
          <div className="p-1">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => {
                  onValueChange(option.value)
                  setIsOpen(false)
                }}
                className="w-full flex items-center gap-3 px-3 py-3 text-sm hover:bg-gray-100 hover:text-foreground cursor-pointer rounded-sm"
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
  const dropdownRef = useRef<HTMLDivElement>(null)

  const toggleOption = (optionValue: string) => {
    if (value.includes(optionValue)) {
      onValueChange(value.filter((v) => v !== optionValue))
    } else {
      onValueChange([...value, optionValue])
    }
  }

  const removeOption = (optionValue: string, e: React.MouseEvent) => {
    e.stopPropagation()
    onValueChange(value.filter((v) => v !== optionValue))
  }

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside)
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isOpen])

  return (
    <div ref={dropdownRef} className={cn("relative", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm border border-input bg-background hover:bg-gray-100 hover:text-foreground cursor-pointer rounded-md min-h-[44px]"
      >
        <div className="flex-1 flex items-center min-h-5">
          {selectedOptions.length > 0 ? (
            <div className="flex flex-wrap gap-1.5 items-center">
              {selectedOptions.map((option) => (
                <span key={option.value} className="inline-flex items-center gap-1.5 px-2 py-1 bg-slate-100 border border-slate-200 rounded text-xs font-medium text-foreground">
                  {option.label}
                  <button
                    type="button"
                    onClick={(e) => removeOption(option.value, e)}
                    className="inline-flex items-center justify-center w-4 h-4 rounded hover:bg-slate-200 text-muted-foreground hover:text-foreground transition-colors"
                  >
                    ×
                  </button>
                </span>
              ))}
            </div>
          ) : (
            <span className="text-muted-foreground">{placeholder}</span>
          )}
        </div>
        <ChevronDown className={cn("h-4 w-4 transition-transform shrink-0", isOpen && "rotate-180")} />
      </button>

      {isOpen && (
        <div className="absolute z-[99999] w-full mt-1 bg-popover border border-border rounded-md shadow-lg animate-in fade-in slide-in-from-top-1 duration-150 ease-out">
          <div className="p-1">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => toggleOption(option.value)}
                className="w-full flex items-center gap-3 px-3 py-3 text-sm hover:bg-gray-100 hover:text-foreground cursor-pointer rounded-sm"
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

export function PaymentMethodSettings({ method, countries, customerGroups, onUpdateSettings, onSaveSettings, isSaving = false }: PaymentMethodSettingsProps) {
  const { t } = usePaymentMethodsTranslations()
  const [showRestrictions, setShowRestrictions] = useState(false)
  const [showFees, setShowFees] = useState(false)
  const [showOrderRestrictions, setShowOrderRestrictions] = useState(false)
  const [showApplePay, setShowApplePay] = useState(false)
  const [isCalculatingTax, setIsCalculatingTax] = useState(false)

  // Tax calculation function - mirrors legacy validation.js:109-142
  const calculateTax = async (changedField: 'incl' | 'excl' | 'taxGroup') => {
    const { fixedFeeTaxIncl, fixedFeeTaxExcl, taxGroup } = method.settings.paymentFees;

    // Only calculate if we have values to work with
    if (!taxGroup || taxGroup === '0') return;
    if (!fixedFeeTaxIncl && !fixedFeeTaxExcl) return;

    setIsCalculatingTax(true);

    try {
      const response = await paymentMethodsApiService.calculatePaymentFeeTax(
        changedField === 'incl' ? fixedFeeTaxIncl : '0',
        changedField === 'excl' ? fixedFeeTaxExcl : '0',
        taxGroup
      );

      if (!response.error && response.paymentFeeTaxIncl && response.paymentFeeTaxExcl) {
        onUpdateSettings({
          paymentFees: {
            ...method.settings.paymentFees,
            fixedFeeTaxIncl: response.paymentFeeTaxIncl,
            fixedFeeTaxExcl: response.paymentFeeTaxExcl
          }
        });
      }
    } catch (error) {
      console.error('Tax calculation failed:', error);
    } finally {
      setIsCalculatingTax(false);
    }
  }

  return (
    <div className="space-y-6">
      {/* Basic Settings */}
      <div className="space-y-6">
        <h3 className="text-sm font-medium">{t('basicSettings')}</h3>
        <div className="grid grid-cols-2 gap-6">
          <div className="space-y-4">
            <div className="space-y-1">
              <Label className="text-sm font-medium">{t('activateDeactivate')}</Label>
              <div className="flex items-center gap-3">
                <p className="text-sm text-muted-foreground flex items-center h-6">{t('enablePaymentMethod')}</p>
                <Switch
                  checked={method.settings.enabled}
                  onCheckedChange={(enabled: boolean) => onUpdateSettings({ enabled })}
                />
              </div>
            </div>

            {method.type === "card" && (
              <div className="space-y-1">
                <Label className="text-base font-semibold">{t('useEmbeddedCreditCardForm')}</Label>
                <div className="flex items-center gap-3">
                  <p className="text-sm text-muted-foreground flex items-center h-6">{t('enableMollieComponents')}</p>
                  <Switch
                    checked={method.settings.mollieComponents}
                    onCheckedChange={(mollieComponents: boolean) => onUpdateSettings({ mollieComponents })}
                  />
                </div>
              </div>
            )}
          </div>

          <div className="space-y-4">
            <div>
              <Label htmlFor="payment-title" className="text-sm font-medium">
                {t('paymentTitle')}
              </Label>
              <Input
                id="payment-title"
                placeholder={t('paymentTitlePlaceholder')}
                value={method.settings.title}
                onChange={(e) => onUpdateSettings({ title: e.target.value })}
                className="mt-1"
              />
            </div>

            {method.type === "card" && (
              <div className="space-y-1">
                <Label className="text-base font-semibold">
                  {t('letCustomerSaveCreditCard')}
                </Label>
                <div className="flex items-center gap-3">
                  <p className="text-sm text-muted-foreground flex items-center h-6">{t('useOneClickPayments')}</p>
                  <Switch
                    checked={method.settings.oneClickPayments}
                    onCheckedChange={(oneClickPayments: boolean) => onUpdateSettings({ oneClickPayments })}
                  />
                </div>
              </div>
            )}

            {method.type === "card" && (
              <div className="space-y-1">
                <CustomLogoUpload
                  value={method.settings.useCustomLogo}
                  logoUrl={method.settings.customLogoUrl}
                  onValueChange={(useCustomLogo: boolean) => onUpdateSettings({ useCustomLogo })}
                  onLogoChange={(customLogoUrl: string | null) => onUpdateSettings({ customLogoUrl })}
                />
              </div>
            )}
          </div>
        </div>
      </div>

      {/* API Selection and Transaction Description */}
      <div className="grid grid-cols-2 gap-6">
        <div>
          <div className="text-base font-semibold mb-0">{t('apiSelection')}</div>
          <div className="flex border border-input rounded-md w-full mt-1 overflow-hidden">
            <button
              onClick={() => onUpdateSettings({ apiSelection: "payments" })}
              className={cn(
                "flex-1 px-4 h-9 text-sm font-medium transition-colors cursor-pointer border-r border-input last:border-r-0 flex items-center justify-center",
                method.settings.apiSelection === "payments"
                  ? "text-white bg-blue-600"
                  : "text-muted-foreground hover:text-foreground bg-background hover:bg-accent",
              )}
            >
              {t('payments')}
            </button>
            <button
              onClick={() => onUpdateSettings({ apiSelection: "orders" })}
              className={cn(
                "flex-1 px-4 h-9 text-sm font-medium transition-colors cursor-pointer flex items-center justify-center",
                method.settings.apiSelection === "orders"
                  ? "text-white bg-blue-600"
                  : "text-muted-foreground hover:text-foreground bg-background hover:bg-accent",
              )}
            >
              {t('orders')}
            </button>
          </div>
          <p className="text-xs text-muted-foreground mt-2">
            <a
              href="https://docs.mollie.com/payments/overview"
              target="_blank"
              rel="noopener noreferrer"
              className="text-muted-foreground underline decoration-1 underline-offset-2 cursor-pointer hover:text-muted-foreground/80"
            >
              {t('readMore')}
            </a>
            <span className="text-muted-foreground"> {t('aboutDifferences')}</span>
          </p>
        </div>

        <div>
          <Label htmlFor="transaction-description" className="text-base font-semibold">
            {t('transactionDescription')}
          </Label>
          <Input
            id="transaction-description"
            placeholder={t('transactionDescriptionPlaceholder')}
            value={method.settings.transactionDescription}
            onChange={(e) => onUpdateSettings({ transactionDescription: e.target.value })}
            className="mt-1"
          />
          <p className="text-xs text-muted-foreground mt-2">
            {t('transactionDescriptionHelp')}
          </p>
          <p className="text-xs text-muted-foreground mt-1 font-mono">
            {t('transactionDescriptionVariables')}
          </p>
        </div>
      </div>

      {/* Collapsible Sections */}
      <div className="space-y-4">
        {/* Apple Pay Settings - Only for Apple Pay */}
        {method.id === "applepay" && method.settings.applePaySettings && (
          <div className="border rounded-lg overflow-hidden">
            <button
              onClick={() => setShowApplePay(!showApplePay)}
              className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
            >
              <span className="font-medium">{t('applePayDirectSettings')}</span>
              <ChevronDown
                className={cn("h-4 w-4 transition-transform duration-200", showApplePay && "rotate-180")}
              />
            </button>
            {showApplePay && method.settings.applePaySettings && (
              <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
                <ApplePaySettings
                  settings={method.settings.applePaySettings}
                  onUpdateSettings={(applePaySettings) => onUpdateSettings({ 
                    applePaySettings: {
                      ...method.settings.applePaySettings,
                      ...applePaySettings
                    }
                  })}
                />
              </div>
            )}
          </div>
        )}

        {/* Payment Restrictions */}
        <div className="border rounded-lg overflow-hidden">
          <button
            onClick={() => setShowRestrictions(!showRestrictions)}
            className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <span className="font-medium">{t('paymentRestrictions')}</span>
            <ChevronDown
              className={cn("h-4 w-4 transition-transform duration-200", showRestrictions && "rotate-180")}
            />
          </button>
          {showRestrictions && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              {/* Accept payments from dropdown */}
              <div>
                <Label className="text-sm font-medium">{t('acceptPaymentsFrom')}</Label>
                <RadioSelect
                  value={method.settings.paymentRestrictions.acceptFrom}
                  onValueChange={(value: string) => onUpdateSettings({
                    paymentRestrictions: {
                      ...method.settings.paymentRestrictions,
                      acceptFrom: value
                    }
                  })}
                  options={[
                    { value: "all", label: t('allCountries') },
                    { value: "selected", label: t('selectedCountries') },
                  ]}
                  placeholder={t('allCountries')}
                  className="mt-1"
                />
              </div>

              {/* Accept payments from specific countries - only show when "selected" is chosen */}
              {method.settings.paymentRestrictions.acceptFrom === 'selected' && (
                <div>
                  <Label className="text-sm font-medium">{t('acceptPaymentsFromSpecificCountries')}</Label>
                  <MultiSelect
                    value={method.settings.paymentRestrictions.selectedCountries || []}
                    onValueChange={(value: string[]) => onUpdateSettings({
                      paymentRestrictions: {
                        ...method.settings.paymentRestrictions,
                        selectedCountries: value
                      }
                    })}
                    options={countries.map(country => ({
                      value: country.id.toString(),
                      label: country.name
                    }))}
                    placeholder={t('selectCountriesAccept')}
                    className="mt-1"
                  />
                </div>
              )}

              {/* Exclude payments from specific countries */}
              <div>
                <Label className="text-sm font-medium">{t('excludePaymentsFromCountries')}</Label>
                <MultiSelect
                  value={method.settings.paymentRestrictions.excludeCountries}
                  onValueChange={(value: string[]) => onUpdateSettings({
                    paymentRestrictions: {
                      ...method.settings.paymentRestrictions,
                      excludeCountries: value
                    }
                  })}
                  options={countries.map(country => ({
                    value: country.id.toString(),
                    label: country.name
                  }))}
                  placeholder={t('selectCountriesToExclude')}
                  className="mt-1"
                />
              </div>

              {/* Exclude customer groups */}
              <div>
                <Label className="text-sm font-medium">{t('excludeCustomerGroups')}</Label>
                <MultiSelect
                  value={method.settings.paymentRestrictions.excludeCustomerGroups}
                  onValueChange={(value: string[]) => onUpdateSettings({
                    paymentRestrictions: {
                      ...method.settings.paymentRestrictions,
                      excludeCustomerGroups: value
                    }
                  })}
                    options={customerGroups.map(group => ({
                      value: group.value,
                      label: group.label
                    }))}
                  placeholder={t('selectCustomerGroups')}
                  className="mt-1"
                />
                <p className="text-xs text-muted-foreground mt-1">{t('customerGroupsHelp')}</p>
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
            <span className="font-medium">{t('paymentFees')}</span>
            <ChevronDown className={cn("h-4 w-4 transition-transform duration-200", showFees && "rotate-180")} />
          </button>
          {showFees && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              {/* Payment Fee Type */}
              <div>
                <Label className="text-sm font-medium">{t('paymentFeeType')}</Label>
                <RadioSelect
                  value={method.settings.paymentFees.type}
                  onValueChange={(type: string) =>
                    onUpdateSettings({
                      paymentFees: {
                        ...method.settings.paymentFees,
                        type: type as "none" | "fixed" | "percentage" | "combined",
                        enabled: type !== 'none'
                      },
                    })
                  }
                  options={[
                    { value: "none", label: t('noFee') },
                    { value: "fixed", label: t('fixedFee') },
                    { value: "percentage", label: t('percentageFee') },
                    { value: "combined", label: t('combinedFee') },
                  ]}
                  placeholder={t('noFee')}
                  className="mt-1"
                />
                <p className="text-xs text-muted-foreground mt-1">
                  {t('paymentFeeEmailHelp')}
                </p>
              </div>

              {/* Fixed Fee Fields - Show for: fixed, combined */}
              {(method.settings.paymentFees.type === 'fixed' || method.settings.paymentFees.type === 'combined') && (
                <>
                  <div>
                    <Label className="text-sm font-medium">{t('fixedFeeTaxIncl')}</Label>
                    <Input
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      className="mt-1"
                      value={method.settings.paymentFees.fixedFeeTaxIncl}
                      onChange={(e) =>
                        onUpdateSettings({
                          paymentFees: { ...method.settings.paymentFees, fixedFeeTaxIncl: e.target.value },
                        })
                      }
                      onBlur={() => calculateTax('incl')}
                      disabled={isCalculatingTax}
                    />
                  </div>
                  <div>
                    <Label className="text-sm font-medium">{t('fixedFeeTaxExcl')}</Label>
                    <Input
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      className="mt-1"
                      value={method.settings.paymentFees.fixedFeeTaxExcl}
                      onChange={(e) =>
                        onUpdateSettings({
                          paymentFees: { ...method.settings.paymentFees, fixedFeeTaxExcl: e.target.value },
                        })
                      }
                      onBlur={() => calculateTax('excl')}
                      disabled={isCalculatingTax}
                    />
                  </div>
                </>
              )}

              {/* Tax Rules Group - Show for: fixed, percentage, combined (hide only for 'none') */}
              {method.settings.paymentFees.type !== 'none' && (
                <div>
                  <Label className="text-sm font-medium">{t('taxRulesGroupForFixedFee')}</Label>
                  <RadioSelect
                    value={method.settings.paymentFees.taxGroup}
                    onValueChange={(taxGroup: string) => {
                      onUpdateSettings({
                        paymentFees: { ...method.settings.paymentFees, taxGroup },
                      });
                      // Recalculate tax when tax group changes (mirrors legacy payment_methods.js:81-107)
                      setTimeout(() => calculateTax('taxGroup'), 100);
                    }}
                    options={window.molliePaymentMethodsConfig?.taxRulesGroups?.map((group: { value: string; label: string }) => ({
                      value: group.value,
                      label: group.label
                    })) || []}
                    placeholder={t('paymentFeeTaxGroup')}
                    className="mt-1"
                  />
                </div>
              )}

              {/* Percentage Fields - Show for: percentage, combined */}
              {(method.settings.paymentFees.type === 'percentage' || method.settings.paymentFees.type === 'combined') && (
                <>
                  <div>
                    <Label className="text-sm font-medium">{t('percentageFeeLabel')}</Label>
                    <Input
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      className="mt-1"
                      value={method.settings.paymentFees.percentageFee}
                      onChange={(e) =>
                        onUpdateSettings({
                          paymentFees: { ...method.settings.paymentFees, percentageFee: e.target.value },
                        })
                      }
                    />
                  </div>
                  <div>
                    <Label className="text-sm font-medium">{t('maximumFee')}</Label>
                    <Input
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      className="mt-1"
                      value={method.settings.paymentFees.maxFeeCap}
                      onChange={(e) =>
                        onUpdateSettings({
                          paymentFees: { ...method.settings.paymentFees, maxFeeCap: e.target.value },
                        })
                      }
                    />
                  </div>
                </>
              )}
            </div>
          )}
        </div>

        {/* Order Restrictions */}
        <div className="border rounded-lg overflow-hidden">
          <button
            onClick={() => setShowOrderRestrictions(!showOrderRestrictions)}
            className="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <span className="font-medium">{t('orderRestrictions')}</span>
            <ChevronDown
              className={cn("h-4 w-4 transition-transform duration-200", showOrderRestrictions && "rotate-180")}
            />
          </button>
          {showOrderRestrictions && (
            <div className="p-4 border-t space-y-4 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label className="text-sm font-medium">{t('minimumAmount')}</Label>
                  <Input
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    className="mt-1"
                    value={method.settings.orderRestrictions.minAmount}
                    onChange={(e) =>
                      onUpdateSettings({
                        orderRestrictions: { ...method.settings.orderRestrictions, minAmount: e.target.value },
                      })
                    }
                  />
                  <p className="text-xs text-muted-foreground mt-1">
                    Default min amount in Mollie is: {method.settings.orderRestrictions.minAmount || '0.00'}
                  </p>
                </div>
                <div>
                  <Label className="text-sm font-medium">{t('maximumAmount')}</Label>
                  <Input
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    className="mt-1"
                    value={method.settings.orderRestrictions.maxAmount}
                    onChange={(e) =>
                      onUpdateSettings({
                        orderRestrictions: { ...method.settings.orderRestrictions, maxAmount: e.target.value },
                      })
                    }
                  />
                  <p className="text-xs text-muted-foreground mt-1">
                    {method.settings.orderRestrictions.maxAmount && method.settings.orderRestrictions.maxAmount !== '0.00'
                      ? `Default max amount in Mollie is: ${method.settings.orderRestrictions.maxAmount}`
                      : 'Default max amount has no limitation'
                    }
                  </p>
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
          {isSaving ? t('saving') : t('save')}
        </button>
      </div>
    </div>
  )
}
