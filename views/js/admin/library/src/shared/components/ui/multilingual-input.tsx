"use client"

import { useState, useRef, useEffect } from "react"
import { Input } from "./input"
import { cn } from "../../lib/utils"
import { ChevronDown } from "lucide-react"
import type { Language } from "../../../services/PaymentMethodsApiService"

interface MultilingualInputProps {
  id?: string
  placeholder?: string
  value: Record<number, string>
  languages: Language[]
  onChange: (value: Record<number, string>) => void
  className?: string
}

export function MultilingualInput({
  id,
  placeholder,
  value,
  languages,
  onChange,
  className
}: MultilingualInputProps) {
  const defaultLanguage = languages.find(lang => lang.is_default) || languages[0]
  const [activeLanguageId, setActiveLanguageId] = useState<number>(defaultLanguage?.id || 1)
  const [isDropdownOpen, setIsDropdownOpen] = useState(false)
  const dropdownRef = useRef<HTMLDivElement>(null)

  const activeLanguage = languages.find(lang => lang.id === activeLanguageId)

  const handleInputChange = (langId: number, newValue: string) => {
    onChange({
      ...value,
      [langId]: newValue
    })
  }

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsDropdownOpen(false)
      }
    }

    if (isDropdownOpen) {
      document.addEventListener('mousedown', handleClickOutside)
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isDropdownOpen])

  if (languages.length === 0) {
    return null
  }

  return (
    <div className={cn(className)}>
      <div className="flex gap-2 items-center">
        <div className="flex-1">
          <Input
            id={id ? `${id}-${activeLanguageId}` : undefined}
            placeholder={placeholder}
            value={value[activeLanguageId] || ""}
            onChange={(e) => handleInputChange(activeLanguageId, e.target.value)}
          />
        </div>

        <div ref={dropdownRef} className="relative flex-shrink-0">
          <button
            type="button"
            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
            className="flex items-center gap-1.5 px-3 py-2 text-sm border border-input bg-background hover:bg-gray-100 rounded-md whitespace-nowrap"
          >
            <span className="font-medium text-sm">{activeLanguage?.iso_code.toUpperCase() || 'EN'}</span>
            <ChevronDown className={cn("h-4 w-4 transition-transform", isDropdownOpen && "rotate-180")} />
          </button>

          {isDropdownOpen && (
            <div className="absolute right-0 z-[99999] mt-1 bg-popover border border-border rounded-md shadow-lg animate-in fade-in slide-in-from-top-1 duration-150 ease-out min-w-[120px]">
              <div className="p-1">
                {languages.map((language) => (
                  <button
                    key={language.id}
                    type="button"
                    onClick={() => {
                      setActiveLanguageId(language.id)
                      setIsDropdownOpen(false)
                    }}
                    className={cn(
                      "w-full flex items-center justify-between px-3 py-2 text-sm hover:bg-gray-100 rounded-sm text-gray-900",
                      activeLanguageId === language.id && "bg-blue-50 text-blue-600 hover:bg-blue-50"
                    )}
                  >
                    <span className={cn("font-medium", activeLanguageId === language.id && "text-blue-600")}>{language.iso_code.toUpperCase()}</span>
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
