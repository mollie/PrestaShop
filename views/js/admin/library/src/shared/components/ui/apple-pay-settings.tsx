"use client"

import { Label } from "./label"
import { Switch } from "./switch"
import { cn } from "../../lib/utils"
import { usePaymentMethodsTranslations } from "../../hooks/use-payment-methods-translations"

interface ApplePaySettingsProps {
  settings: {
    directProduct?: boolean
    directCart?: boolean
    buttonStyle?: 0 | 1 | 2
  }
  onUpdateSettings: (settings: {
    directProduct?: boolean
    directCart?: boolean
    buttonStyle?: 0 | 1 | 2
  }) => void
  className?: string
}

// Button styles will be defined inside the component to use translations

export function ApplePaySettings({
  settings,
  onUpdateSettings,
  className
}: ApplePaySettingsProps) {
  const { t } = usePaymentMethodsTranslations()
  // Provide default values for optional properties
  const directProduct = settings.directProduct ?? false
  const directCart = settings.directCart ?? false
  const buttonStyle = settings.buttonStyle ?? 0

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
      {/* Direct Product Page */}
      <div className="space-y-1">
        <Label className="text-sm font-medium">{t('applePayDirectProductPage')}</Label>
        <div className="flex items-center gap-3">
          <p className="text-sm text-muted-foreground flex items-center h-6">{t('enableApplePayProductPages')}</p>
          <Switch
            checked={directProduct}
            onCheckedChange={(directProduct: boolean) => onUpdateSettings({ directProduct })}
          />
        </div>
      </div>

      {/* Direct Cart Page */}
      <div className="space-y-1">
        <Label className="text-sm font-medium">{t('applePayDirectCartPage')}</Label>
        <div className="flex items-center gap-3">
          <p className="text-sm text-muted-foreground flex items-center h-6">{t('enableApplePayCartPages')}</p>
          <Switch
            checked={directCart}
            onCheckedChange={(directCart: boolean) => onUpdateSettings({ directCart })}
          />
        </div>
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
                        // Fallback if image doesn't load
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
