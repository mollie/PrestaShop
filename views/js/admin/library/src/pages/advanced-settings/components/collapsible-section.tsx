import type React from "react"
import { useId } from "react"
import { ChevronDown } from "lucide-react"
import { cn } from "../../../shared/lib/utils"

interface CollapsibleSectionProps {
  title: string
  summary: string
  open: boolean
  onToggle: () => void
  testId: string
  children: React.ReactNode
}

export function CollapsibleSection({ title, summary, open, onToggle, testId, children }: CollapsibleSectionProps) {
  const contentId = useId()

  return (
    <section className={cn("settings-section", !open && "is-collapsed")}>
      <h2 className="section-title section-title-collapsible">
        <button type="button" className="section-toggle" aria-expanded={open} aria-controls={contentId} data-testid={testId} onClick={onToggle}>
          <span>{title}</span>
          <span className="section-summary">{summary}</span>
          <ChevronDown className="section-toggle-icon" />
        </button>
      </h2>
      {open && <div id={contentId}>{children}</div>}
    </section>
  )
}
