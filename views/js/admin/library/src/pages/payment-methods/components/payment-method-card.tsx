"use client"

import type React from "react"

import { Card } from "../../../shared/components/ui/card"
import { Badge } from "../../../shared/components/ui/badge"
import { ChevronDown, ChevronUp, GripVertical, CreditCard } from "lucide-react"
import { cn } from "../../../shared/lib/utils"
import { PaymentMethodSettings } from "./payment-method-settings"
import type { PaymentMethod, Country, CustomerGroup } from "../../../services/PaymentMethodsApiService"
import { usePaymentMethodsTranslations } from "../../../shared/hooks/use-payment-methods-translations"

interface PaymentMethodCardProps {
  method: PaymentMethod
  index: number
  countries: Country[]
  customerGroups: CustomerGroup[]
  onToggleExpanded: () => void
  onUpdateSettings: (settings: Partial<PaymentMethod["settings"]>) => void
  onSaveSettings: () => void
  onDragStart: (e: React.DragEvent) => void
  onDragOver: (e: React.DragEvent) => void
  onDragLeave: () => void
  onDrop: (e: React.DragEvent) => void
  onDragEnd: () => void
  isDragging: boolean
  isDragOver: boolean
  isSaving?: boolean
  isDragEnabled?: boolean
}

export function PaymentMethodCard({
  method,
  index,
  countries,
  customerGroups,
  onToggleExpanded,
  onUpdateSettings,
  onSaveSettings,
  onDragStart,
  onDragOver,
  onDragLeave,
  onDrop,
  onDragEnd,
  isDragging,
  isDragOver,
  isSaving = false,
  isDragEnabled = true,
}: PaymentMethodCardProps) {
  const { t } = usePaymentMethodsTranslations()
  return (
    <Card
      className={cn(
        "border border-gray-200 transition-all duration-300 ease-in-out transform-gpu",
        "hover:shadow-md hover:-translate-y-0.5",
        isDragging && "opacity-60 scale-105 shadow-2xl rotate-1 z-50",
        isDragOver && "border-blue-400 bg-blue-50 scale-102 shadow-lg",
        !isDragging && !isDragOver && "hover:border-gray-300",
        !isDragEnabled && "cursor-default",
      )}
      draggable={isDragEnabled && !method.isExpanded}
      onDragStart={onDragStart}
      onDragOver={onDragOver}
      onDragLeave={onDragLeave}
      onDrop={onDrop}
      onDragEnd={onDragEnd}
    >
      <div className="p-4">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <span className="text-sm text-muted-foreground font-medium">{index}</span>
            <div className="flex items-center gap-2">
              <div className="w-8 h-6 rounded flex items-center justify-center overflow-hidden">
                {/* Show custom logo if enabled for card payments */}
                {method.type === "card" && method.settings.useCustomLogo && method.settings.customLogoUrl ? (
                  <img 
                    src={method.settings.customLogoUrl} 
                    alt={`${method.name} custom logo`}
                    className="w-full h-full object-contain"
                    onError={(e) => {
                      // Fallback to default logo if custom logo fails to load
                      e.currentTarget.style.display = 'none';
                      e.currentTarget.nextElementSibling?.classList.remove('hidden');
                    }}
                  />
                ) : method.image?.size1x ? (
                  <img 
                    src={method.image.size1x} 
                    alt={`${method.name} logo`}
                    className="w-full h-full object-contain"
                    onError={(e) => {
                      // Fallback to icon if image fails to load
                      e.currentTarget.style.display = 'none';
                      e.currentTarget.nextElementSibling?.classList.remove('hidden');
                    }}
                  />
                ) : null}
                <div className={cn(
                  "w-full h-full bg-gray-800 rounded flex items-center justify-center",
                  (method.type === "card" && method.settings.useCustomLogo && method.settings.customLogoUrl) || 
                  (method.image?.size1x) ? "hidden" : ""
                )}>
                  <CreditCard className="h-3 w-3 text-white" />
                </div>
              </div>
              <span className="font-medium">{method.name}</span>
              <Badge
                variant={method.status === "active" ? "default" : "destructive"}
                className={cn(
                  "text-xs transition-all duration-200",
                  method.status === "active"
                    ? "bg-green-100 text-green-800 hover:bg-green-100"
                    : "bg-red-100 text-red-800 hover:bg-red-100",
                )}
              >
                {method.status === "active" ? t('active') : t('inactive')}
              </Badge>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={onToggleExpanded}
              className={cn(
                "flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 cursor-pointer",
                "transition-all duration-200 hover:scale-105 active:scale-95",
              )}
            >
              {method.isExpanded ? (
                <>
                  <ChevronUp className="h-4 w-4 transition-transform duration-200" />
                  {t('hideSettings')}
                </>
              ) : (
                <>
                  <ChevronDown className="h-4 w-4 transition-transform duration-200" />
                  {t('showSettings')}
                </>
              )}
            </button>
            {isDragEnabled && !method.isExpanded && (
              <GripVertical
                className={cn(
                  "h-5 w-5 cursor-move transition-all duration-200",
                  isDragging
                    ? "text-blue-600 animate-pulse scale-110"
                    : "text-gray-400 hover:text-gray-600 hover:scale-110",
                )}
              />
            )}
          </div>
        </div>

        {/* Expanded Settings */}
        {method.isExpanded && (
          <div className="mt-6 space-y-6 border-t pt-6 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
            {isSaving ? (
              <div className="space-y-4 animate-pulse">
                <div className="h-4 bg-gray-200 rounded w-32"></div>
                <div className="grid grid-cols-2 gap-6">
                  <div className="space-y-4">
                    <div className="h-4 bg-gray-200 rounded w-24"></div>
                    <div className="h-4 bg-gray-200 rounded w-48"></div>
                  </div>
                  <div className="space-y-4">
                    <div className="h-4 bg-gray-200 rounded w-32"></div>
                    <div className="h-4 bg-gray-200 rounded w-40"></div>
                  </div>
                </div>
                <div className="h-10 bg-gray-200 rounded w-20 ml-auto"></div>
              </div>
            ) : (
              <PaymentMethodSettings
                method={method}
                countries={countries}
                customerGroups={customerGroups}
                onUpdateSettings={onUpdateSettings}
                onSaveSettings={onSaveSettings}
                isSaving={isSaving}
              />
            )}
          </div>
        )}
      </div>
    </Card>
  )
}
