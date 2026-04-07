"use client"

import { useEffect, useState } from "react"
import { AlertCircle } from "lucide-react"
import { Label } from "./label"
import { Switch } from "./switch"
import { cn } from "../../lib/utils"
import { usePaymentMethodsTranslations } from "../../hooks/use-payment-methods-translations"
import { paymentMethodsApiService } from "../../../services/PaymentMethodsApiService"

interface ApplePayDirectSettings {
  directProduct?: boolean
  directCart?: boolean
  buttonStyle?: 0 | 1 | 2
}

interface ApplePaySettingsProps {
  settings: ApplePayDirectSettings
  onUpdateSettings: (settings: ApplePayDirectSettings) => void
  className?: string
}

export function ApplePaySettings({
  settings,
  onUpdateSettings,
  className
}: ApplePaySettingsProps) {
  const { t } = usePaymentMethodsTranslations()
  const [certificateConflict, setCertificateConflict] = useState<string | null>(null)
  const [isDismissed, setIsDismissed] = useState(false)

  const directProduct = settings.directProduct ?? false
  const directCart = settings.directCart ?? false
  const buttonStyle = settings.buttonStyle ?? 0

  useEffect(() => {
    paymentMethodsApiService.checkApplePayCertificate()
      .then((result) => {
        if (result.success && result.conflict) {
          setCertificateConflict(result.message || t('applePayDirectCertificateConflict'))
        }
      })
      .catch(() => {})
  }, [])

  const isDisabled = !!certificateConflict && !isDismissed

  const buttonStyles = [
    {
      value: 0 as const,
      label: t('applePayButtonBlack'),
      image: "/modules/mollie/views/img/applePayButtons/ApplePay_black_yes.png",
      description: t('applePayButtonBlackDesc')
    },
    {
      value: 1 as const,
      label: t('applePayButtonOutline'),
      image: "/modules/mollie/views/img/applePayButtons/ApplePay_outline_yes.png",
      description: t('applePayButtonOutlineDesc')
    },
    {
      value: 2 as const,
      label: t('applePayButtonWhite'),
      image: "/modules/mollie/views/img/applePayButtons/ApplePay_white_yes.png",
      description: t('applePayButtonWhiteDesc')
    }
  ]

  return (
    <div className={cn("space-y-6", className)}>
      {certificateConflict && !isDismissed && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
          <AlertCircle className="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" />
          <div className="flex-1 text-sm text-red-800">
            {certificateConflict}
          </div>
          <button
            onClick={() => setIsDismissed(true)}
            className="text-sm font-medium text-red-700 hover:text-red-900 whitespace-nowrap underline"
          >
            {t('ignore')}
          </button>
        </div>
      )}

      <div className="grid grid-cols-[auto_auto_1fr] items-center gap-x-3 gap-y-1">
        {/* Direct Product Page */}
        <Label className={cn("text-sm font-medium col-span-3", isDisabled && "opacity-50")}>{t('applePayDirectProductPage')}</Label>
        <p className={cn("text-sm text-muted-foreground", isDisabled && "opacity-50")}>{t('enableApplePayProductPages')}</p>
        <Switch
          checked={directProduct}
          onCheckedChange={(directProduct: boolean) => onUpdateSettings({ directProduct })}
          disabled={isDisabled}
        />
        <p className="text-xs text-gray-400 leading-relaxed">{t('applePayDirectProductPageInfo')}</p>

        {/* Direct Cart Page */}
        <Label className={cn("text-sm font-medium col-span-3 mt-4", isDisabled && "opacity-50")}>{t('applePayDirectCartPage')}</Label>
        <p className={cn("text-sm text-muted-foreground", isDisabled && "opacity-50")}>{t('enableApplePayCartPages')}</p>
        <Switch
          checked={directCart}
          onCheckedChange={(directCart: boolean) => onUpdateSettings({ directCart })}
          disabled={isDisabled}
        />
        <p className="text-xs text-gray-400 leading-relaxed">{t('applePayDirectCartPageInfo')}</p>
      </div>

      {/* Button Style - only show if at least one direct option is enabled */}
      {(directProduct || directCart) && (
        <div className="space-y-3">
          <Label className="text-sm font-medium">{t('applePayDirectButtonStyle')}</Label>
          <div className="grid grid-cols-3 gap-4">
            {buttonStyles.map((style) => (
              <div key={style.value} className="space-y-2">
                <button
                  type="button"
                  onClick={() => onUpdateSettings({ buttonStyle: style.value })}
                  className={cn(
                    "w-full p-3 border-2 rounded-lg transition-all duration-200",
                    "hover:border-blue-300 hover:bg-blue-50",
                    "focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2",
                    buttonStyle === style.value
                      ? "border-blue-600 bg-blue-50"
                      : "border-gray-200 bg-white"
                  )}
                >
                  <div className="aspect-[3/1] flex items-center justify-center mb-2">
                    <img
                      src={style.image}
                      alt={style.description}
                      className="max-w-full max-h-full object-contain"
                      onError={(e) => {
                        e.currentTarget.style.display = 'none';
                        e.currentTarget.nextElementSibling?.classList.remove('hidden');
                      }}
                    />
                    <div className="hidden w-full h-full bg-gray-200 rounded flex items-center justify-center text-xs text-gray-500">
                      {style.label}
                    </div>
                  </div>
                  <div className="text-center">
                    <div className="text-sm font-medium">{style.label}</div>
                    <div className="text-xs text-gray-500">{style.description}</div>
                  </div>
                </button>
                <div className="flex justify-center">
                  <input
                    type="radio"
                    name="applePayButtonStyle"
                    value={style.value}
                    checked={buttonStyle === style.value}
                    onChange={() => onUpdateSettings({ buttonStyle: style.value })}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                  />
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
