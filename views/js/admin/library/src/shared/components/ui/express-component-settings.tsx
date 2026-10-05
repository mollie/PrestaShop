"use client"

import { AlertCircle } from "lucide-react"
import { Card, CardContent, CardHeader, CardTitle } from "./card"
import { Label } from "./label"
import { Switch } from "./switch"
import { Button } from "./button"
import { cn } from "../../lib/utils"
import { usePaymentMethodsTranslations } from "../../hooks/use-payment-methods-translations"
import type { ExpressComponentSettings as ExpressComponentSettingsValues } from "../../../services/PaymentMethodsApiService"

type ExpressButtonKey = "applepay" | "googlepay" | "paypal"

interface ExpressComponentSettingsProps {
  settings: ExpressComponentSettingsValues
  showLiveKeyWarning?: boolean
  isSaving?: boolean
  onUpdateSettings: (settings: Partial<ExpressComponentSettingsValues>) => void
  onSave: () => void
  className?: string
}

export function ExpressComponentSettings({
  settings,
  showLiveKeyWarning = false,
  isSaving = false,
  onUpdateSettings,
  onSave,
  className
}: ExpressComponentSettingsProps) {
  const { t } = usePaymentMethodsTranslations()

  const buttonsDisabled = !settings.enabled

  const buttons: { key: ExpressButtonKey; label: string }[] = [
    { key: "applepay", label: t('expressComponentApplePay') },
    { key: "googlepay", label: t('expressComponentGooglePay') },
    { key: "paypal", label: t('expressComponentPayPal') },
  ]

  return (
    <Card data-testid="express-component-card" className={cn("border border-gray-200", className)}>
      <CardHeader>
        <CardTitle className="text-base">{t('expressComponentSettings')}</CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        {showLiveKeyWarning && (
          <div className="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
            <AlertCircle className="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" />
            <div className="flex-1 text-sm text-amber-800">
              {t('expressComponentLiveOnly')}
            </div>
          </div>
        )}

        <div className="space-y-1">
          <Label className="text-sm font-medium">{t('expressComponentEnable')}</Label>
          <div className="flex items-center gap-3">
            <p className="text-sm text-muted-foreground flex items-center h-6">{t('expressComponentEnableInfo')}</p>
            <Switch
              data-testid="express-component-enabled-switch"
              checked={settings.enabled}
              onCheckedChange={(enabled: boolean) => onUpdateSettings({ enabled })}
            />
          </div>
        </div>

        <div className="space-y-3">
          <Label className={cn("text-sm font-medium", buttonsDisabled && "opacity-50")}>{t('expressComponentButtons')}</Label>
          <div className="space-y-2">
            {buttons.map((button) => (
              <div key={button.key} className="flex items-center gap-3">
                <p className={cn("text-sm text-muted-foreground flex items-center h-6 min-w-[8rem]", buttonsDisabled && "opacity-50")}>{button.label}</p>
                <Switch
                  data-testid={`express-component-${button.key}-switch`}
                  checked={settings[button.key]}
                  disabled={buttonsDisabled}
                  onCheckedChange={(checked: boolean) => onUpdateSettings({ [button.key]: checked })}
                />
              </div>
            ))}
          </div>
        </div>

        <div className="space-y-2">
          <p className="text-xs text-gray-400 leading-relaxed">{t('expressComponentApplePayDomainFile')}</p>
          <p className="text-xs text-gray-400 leading-relaxed">{t('expressComponentDisablesDirect')}</p>
        </div>

        <div className="flex justify-end pt-4 border-t">
          <Button
            data-testid="express-component-save"
            onClick={onSave}
            disabled={isSaving}
            className={cn(
              "px-6 bg-blue-600 text-white hover:bg-blue-700",
              isSaving && "bg-blue-500"
            )}
          >
            {isSaving ? t('saving') : t('expressComponentSave')}
          </Button>
        </div>
      </CardContent>
    </Card>
  )
}
