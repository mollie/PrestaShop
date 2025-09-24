"use client"

import { GripVertical, Info } from "lucide-react"
import { cn } from "../../../shared/lib/utils"

interface PaymentMethodTabsProps {
  activeTab: "enabled" | "disabled"
  onTabChange: (tab: "enabled" | "disabled") => void
}

export function PaymentMethodTabs({ activeTab, onTabChange }: PaymentMethodTabsProps) {
  return (
    <div className="flex items-center justify-between">
      <div className="flex">
        <button
          onClick={() => onTabChange("enabled")}
          className={cn(
            "px-4 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer",
            activeTab === "enabled"
              ? "border-blue-600 text-blue-600"
              : "border-transparent text-muted-foreground hover:text-foreground",
          )}
        >
          Enabled payment methods
        </button>
        <button
          onClick={() => onTabChange("disabled")}
          className={cn(
            "px-4 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer",
            activeTab === "disabled"
              ? "border-blue-600 text-blue-600"
              : "border-transparent text-muted-foreground hover:text-foreground",
          )}
        >
          Disabled payment methods
        </button>
      </div>

      <div className="flex items-center gap-2 text-sm text-blue-600">
        <GripVertical className="h-4 w-4" />
        <span>Drag payment options to reorder</span>
        <Info className="h-4 w-4 cursor-pointer hover:text-blue-700" />
      </div>
    </div>
  )
}
