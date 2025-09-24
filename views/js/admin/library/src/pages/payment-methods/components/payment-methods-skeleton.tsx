"use client"

import { Card } from "../../../shared/components/ui/card"

// Skeleton loading components for payment methods
const SkeletonPaymentMethodCard = () => (
  <Card className="border border-gray-200 animate-pulse">
    <div className="p-4">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="h-4 bg-gray-200 rounded w-6"></div>
          <div className="flex items-center gap-2">
            <div className="w-8 h-6 bg-gray-200 rounded"></div>
            <div className="h-4 bg-gray-200 rounded w-24"></div>
            <div className="h-5 bg-gray-200 rounded w-16"></div>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <div className="h-4 bg-gray-200 rounded w-20"></div>
          <div className="h-5 w-5 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>
  </Card>
)

const SkeletonPaymentMethodTabs = () => (
  <div className="flex items-center justify-between">
    <div className="flex">
      <div className="h-8 bg-gray-200 rounded w-32 mr-4 animate-pulse"></div>
      <div className="h-8 bg-gray-200 rounded w-32 animate-pulse"></div>
    </div>
    <div className="flex items-center gap-2">
      <div className="h-4 w-4 bg-gray-200 rounded animate-pulse"></div>
      <div className="h-4 bg-gray-200 rounded w-48 animate-pulse"></div>
      <div className="h-4 w-4 bg-gray-200 rounded animate-pulse"></div>
    </div>
  </div>
)

const SkeletonInfoBanner = () => (
  <div className="bg-cyan-50 border border-cyan-200 rounded-lg p-4 flex items-start gap-3">
    <div className="h-5 w-5 bg-gray-200 rounded animate-pulse"></div>
    <div className="flex-1">
      <div className="h-4 bg-gray-200 rounded w-full mb-2 animate-pulse"></div>
      <div className="h-4 bg-gray-200 rounded w-3/4 animate-pulse"></div>
    </div>
  </div>
)

export function PaymentMethodsSkeleton() {
  return (
    <div className="max-w-6xl mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="space-y-2">
        <div className="h-8 bg-gray-200 rounded w-48 animate-pulse"></div>
        <div className="h-4 bg-gray-200 rounded w-64 animate-pulse"></div>
      </div>

      {/* Info Banner */}
      <SkeletonInfoBanner />

      {/* Tabs */}
      <SkeletonPaymentMethodTabs />

      {/* Payment Methods List */}
      <div className="space-y-4">
        {Array.from({ length: 3 }).map((_, index) => (
          <SkeletonPaymentMethodCard key={index} />
        ))}
      </div>
    </div>
  )
}
