"use client"

import type React from "react"

import { Card } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { ChevronDown, ChevronUp, GripVertical, CreditCard } from "lucide-react"
import { cn } from "@/lib/utils"
import { PaymentMethodSettings } from "./payment-method-settings"
import type { PaymentMethod } from "./types/payment-method"

interface PaymentMethodCardProps {
  method: PaymentMethod
  index: number
  onToggleExpanded: () => void
  onUpdateSettings: (settings: Partial<PaymentMethod["settings"]>) => void
  onDragStart: (e: React.DragEvent) => void
  onDragOver: (e: React.DragEvent) => void
  onDragLeave: () => void
  onDrop: (e: React.DragEvent) => void
  onDragEnd: () => void
  isDragging: boolean
  isDragOver: boolean
}

export function PaymentMethodCard({
  method,
  index,
  onToggleExpanded,
  onUpdateSettings,
  onDragStart,
  onDragOver,
  onDragLeave,
  onDrop,
  onDragEnd,
  isDragging,
  isDragOver,
}: PaymentMethodCardProps) {
  return (
    <Card
      className={cn(
        "border border-gray-200 transition-all duration-300 ease-in-out transform-gpu",
        "hover:shadow-md hover:-translate-y-0.5",
        isDragging && "opacity-60 scale-105 shadow-2xl rotate-1 z-50",
        isDragOver && "border-blue-400 bg-blue-50 scale-102 shadow-lg",
        !isDragging && !isDragOver && "hover:border-gray-300",
      )}
      draggable
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
              <div className="w-8 h-6 bg-gray-800 rounded flex items-center justify-center">
                <CreditCard className="h-3 w-3 text-white" />
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
                {method.status === "active" ? "Active" : "Inactive"}
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
                  Hide settings
                </>
              ) : (
                <>
                  <ChevronDown className="h-4 w-4 transition-transform duration-200" />
                  Show settings
                </>
              )}
            </button>
            <GripVertical
              className={cn(
                "h-5 w-5 cursor-move transition-all duration-200",
                isDragging
                  ? "text-blue-600 animate-pulse scale-110"
                  : "text-gray-400 hover:text-gray-600 hover:scale-110",
              )}
            />
          </div>
        </div>

        {/* Expanded Settings */}
        {method.isExpanded && (
          <div className="mt-6 space-y-6 border-t pt-6 animate-in slide-in-from-top-1 fade-in duration-200 ease-out">
            <PaymentMethodSettings method={method} onUpdateSettings={onUpdateSettings} />
          </div>
        )}
      </div>
    </Card>
  )
}
