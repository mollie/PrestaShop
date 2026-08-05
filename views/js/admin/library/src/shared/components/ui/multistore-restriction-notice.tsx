import { Store } from "lucide-react"

interface MultistoreRestrictionNoticeProps {
  title: string
  message: string
}

/**
 * Shown on Mollie settings screens when the back office is set to "All stores"
 * or a shop group. Mollie settings are saved per shop, so editing them in a
 * multi-shop context would silently apply to the main shop only. The notice
 * asks the merchant to select a single shop first.
 */
export function MultistoreRestrictionNotice({ title, message }: MultistoreRestrictionNoticeProps) {
  return (
    <div className="max-w-6xl mx-auto p-6">
      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 flex items-start gap-3">
        <Store className="h-6 w-6 text-yellow-600 mt-0.5 flex-shrink-0" />
        <div className="text-sm text-yellow-800">
          <p className="font-medium mb-2">{title}</p>
          <p>{message}</p>
        </div>
      </div>
    </div>
  )
}
