"use client"

import React, { useState, useRef } from "react"
import { Upload, X } from "lucide-react"
import { cn } from "../../lib/utils"
import { paymentMethodsApiService } from "../../../services/PaymentMethodsApiService"
import { usePaymentMethodsTranslations } from "../../hooks/use-payment-methods-translations"

interface CustomLogoUploadProps {
  value: boolean
  logoUrl?: string | null
  onValueChange: (useCustomLogo: boolean) => void
  onLogoChange: (logoUrl: string | null) => void
  className?: string
}

export function CustomLogoUpload({
  value,
  logoUrl,
  onValueChange,
  onLogoChange,
  className
}: CustomLogoUploadProps) {
  const { t } = usePaymentMethodsTranslations()
  const [isUploading, setIsUploading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const fileInputRef = useRef<HTMLInputElement>(null)

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) return

    // Validate file type
    if (!file.type.match(/^image\/(jpeg|jpg|png)$/)) {
      setError(t('pleaseUploadJpgOrPng'))
      return
    }

    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      setError(t('fileSizeTooLarge'))
      return
    }

    // Validate dimensions
    const img = new Image()
    img.onload = async () => {
      if (img.width > 256 || img.height > 64) {
        setError(t('imageDimensionsTooLarge'))
        return
      }

      // Upload the file
      setIsUploading(true)
      setError(null)

      try {
        const result = await paymentMethodsApiService.uploadCustomLogo(file)
        
        if (result.success) {
          onLogoChange(result.logoUrl || null)
          onValueChange(true)
        } else {
          setError(result.message)
        }
      } catch {
        setError(t('failedToUploadLogo'))
      } finally {
        setIsUploading(false)
      }
    }

    img.onerror = () => {
      setError(t('invalidImageFile'))
    }

    img.src = URL.createObjectURL(file)
  }

  const handleRemoveLogo = () => {
    onLogoChange(null)
    onValueChange(false)
    setError(null)
    if (fileInputRef.current) {
      fileInputRef.current.value = ""
    }
  }

  const handleToggleCustomLogo = (useCustom: boolean) => {
    onValueChange(useCustom)
    if (!useCustom) {
      handleRemoveLogo()
    }
  }

  return (
    <div className={cn("space-y-4", className)}>
      {/* Toggle Switch */}
      <div className="flex items-center gap-3">
        <label className="text-sm font-medium">{t('useCustomLogo')}</label>
        <button
          type="button"
          onClick={() => handleToggleCustomLogo(!value)}
          className={cn(
            "relative inline-flex h-6 w-11 items-center rounded-full transition-colors",
            "focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2",
            value ? "bg-blue-600" : "bg-gray-300"
          )}
        >
          <span
            className={cn(
              "inline-block h-5 w-5 transform rounded-full bg-white transition-transform",
              value ? "translate-x-5" : "translate-x-0"
            )}
          />
        </button>
      </div>

      {/* Upload Section */}
      {value && (
        <div className="space-y-3">
          {/* Current Logo Display */}
          {logoUrl && (
            <div className="flex items-center gap-3">
              <div className="w-16 h-8 border border-gray-200 rounded flex items-center justify-center overflow-hidden">
                <img
                  src={logoUrl}
                  alt={t('customLogoPreview')}
                  className="w-full h-full object-contain"
                />
              </div>
              <button
                type="button"
                onClick={handleRemoveLogo}
                className="text-red-600 hover:text-red-700 text-sm"
              >
                <X className="h-4 w-4" />
              </button>
            </div>
          )}

          {/* Upload Button */}
          <div>
            <input
              ref={fileInputRef}
              type="file"
              accept="image/jpeg,image/jpg,image/png"
              onChange={handleFileSelect}
              className="hidden"
            />
            <button
              type="button"
              onClick={() => fileInputRef.current?.click()}
              disabled={isUploading}
              className={cn(
                "flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-md",
                "hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500",
                "disabled:opacity-50 disabled:cursor-not-allowed",
                "text-sm font-medium"
              )}
            >
              {isUploading ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-2 border-gray-300 border-t-blue-600" />
                  {t('uploading')}
                </>
              ) : (
                <>
                  <Upload className="h-4 w-4" />
                  {logoUrl ? t('replaceLogo') : t('uploadLogo')}
                </>
              )}
            </button>
          </div>

          {/* Error Message */}
          {error && (
            <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded-md p-2">
              {error}
            </div>
          )}

          {/* Help Text */}
          <div className="text-xs text-gray-500">
            {t('logoUploadHelp')}
          </div>
        </div>
      )}
    </div>
  )
}
