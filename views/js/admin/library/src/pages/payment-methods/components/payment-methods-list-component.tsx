"use client"

import type React from "react"

import { useState, useRef, useEffect, useCallback } from "react"
import { PaymentMethodCard } from "./payment-method-card"
import type { PaymentMethod, Country, Carrier, CustomerGroup, Language } from "../../../services/PaymentMethodsApiService"

// Auto-scroll while dragging: speed ramps up the closer the cursor gets to the window edge
const AUTOSCROLL = { margin: 140, minSpeed: 4, maxSpeed: 22 }
// Chromium delivers the Escape keyup a few ms after dragend, so the recovery below waits for it
const CANCEL_GRACE_MS = 80
const SCROLLING_RECENTLY_MS = 200
const REORDER_ANIMATION_MS = 300

interface PaymentMethodsListProps {
  methods: PaymentMethod[]
  countries: Country[]
  carriers: Carrier[]
  customerGroups: CustomerGroup[]
  languages: Language[]
  onlyPaymentsMethods: string[]
  onlyOrderMethods: string[]
  onToggleExpanded: (id: string) => void
  onUpdateSettings: (id: string, settings: Partial<PaymentMethod["settings"]>) => void
  onSaveSettings: (id: string) => void
  onReorder: (methods: PaymentMethod[]) => void
  savingMethodId?: string
  isDragEnabled?: boolean
}

export function PaymentMethodsList({
  methods,
  countries,
  carriers,
  customerGroups,
  languages,
  onlyPaymentsMethods,
  onlyOrderMethods,
  onToggleExpanded,
  onUpdateSettings,
  onSaveSettings,
  onReorder,
  savingMethodId,
  isDragEnabled = true,
}: PaymentMethodsListProps) {
  const [draggedItem, setDraggedItem] = useState<string | null>(null)
  const [dragOverIndex, setDragOverIndex] = useState<number | null>(null)
  const [isReordering, setIsReordering] = useState(false)

  const isDragging = useRef(false)
  const pointerY = useRef(0)
  const containerRef = useRef<HTMLDivElement>(null)
  const rowBounds = useRef<{ top: number, bottom: number }[]>([])
  const rafId = useRef<number | null>(null)
  const didDrop = useRef(false)
  const dragCancelled = useRef(false)
  const scrolledAt = useRef(0)
  const draggedItemRef = useRef<string | null>(null)
  const dragOverIndexRef = useRef<number | null>(null)
  const methodsRef = useRef(methods)
  methodsRef.current = methods
  const onReorderRef = useRef(onReorder)
  onReorderRef.current = onReorder

  const stopAutoScroll = useCallback(() => {
    if (rafId.current !== null) {
      cancelAnimationFrame(rafId.current)
    }
    rafId.current = null
  }, [])

  // Row edges in document coordinates, measured once per drag: the list does not reflow while a
  // card is in flight, and reading 29 rects on every animation frame would force a layout right
  // after the frame's scroll write and cost frames.
  const measureRows = useCallback(() => {
    const container = containerRef.current
    const offset = window.scrollY

    rowBounds.current = container
      ? [...container.children].map((row) => {
        const rect = row.getBoundingClientRect()
        return { top: rect.top + offset, bottom: rect.bottom + offset }
      })
      : []
  }, [])

  // The slot the drop will land in, read from the pointer position rather than from the cards'
  // own dragover events: those fire far too rarely to keep up with an auto-scrolling list, which
  // left the highlight lagging the cursor by a few cards. Past either end of the list the target
  // clamps to the first or last slot, so scrolling to an edge and releasing means what it looks
  // like it means.
  const resolveTargetIndex = useCallback((y: number) => {
    const rows = rowBounds.current

    if (rows.length === 0) {
      return null
    }

    const documentY = y + window.scrollY

    if (documentY <= rows[0].top) {
      return 0
    }

    if (documentY >= rows[rows.length - 1].bottom) {
      return rows.length - 1
    }

    const hit = rows.findIndex((row) => documentY >= row.top && documentY <= row.bottom)

    if (hit !== -1) {
      return hit
    }

    // Between two rows: pick whichever centre is closer
    const distanceTo = (row: { top: number, bottom: number }) => Math.abs(documentY - (row.top + row.bottom) / 2)
    let nearest = 0
    rows.forEach((row, index) => {
      if (distanceTo(row) < distanceTo(rows[nearest])) {
        nearest = index
      }
    })

    return nearest
  }, [])

  const autoScrollTick = useCallback(() => {
    const scrollingElement = document.scrollingElement || document.documentElement
    const windowHeight = window.innerHeight
    const y = pointerY.current
    const { margin, minSpeed, maxSpeed } = AUTOSCROLL
    const speedAt = (proximity: number) => minSpeed + Math.min(proximity, 1) * (maxSpeed - minSpeed)

    if (y < margin) {
      scrollingElement.scrollTop -= speedAt((margin - y) / margin)
      scrolledAt.current = Date.now()
    } else if (y > windowHeight - margin) {
      scrollingElement.scrollTop += speedAt((y - (windowHeight - margin)) / margin)
      scrolledAt.current = Date.now()
    }

    const targetIndex = resolveTargetIndex(y)

    if (targetIndex !== null && targetIndex !== dragOverIndexRef.current) {
      dragOverIndexRef.current = targetIndex
      setDragOverIndex(targetIndex)
    }

    rafId.current = requestAnimationFrame(autoScrollTick)
  }, [resolveTargetIndex])

  const commitReorder = useCallback((methodId: string, dropIndex: number) => {
    const currentMethods = methodsRef.current
    const draggedIndex = currentMethods.findIndex((method) => method.id === methodId)

    if (draggedIndex === -1 || draggedIndex === dropIndex) {
      return
    }

    setIsReordering(true)

    const newMethods = [...currentMethods]
    const [draggedMethod] = newMethods.splice(draggedIndex, 1)
    newMethods.splice(dropIndex, 0, draggedMethod)

    onReorderRef.current(newMethods)
    window.setTimeout(() => setIsReordering(false), REORDER_ANIMATION_MS)
  }, [])

  const endDrag = useCallback(() => {
    stopAutoScroll()
    isDragging.current = false
    draggedItemRef.current = null
    dragOverIndexRef.current = null
    setDraggedItem(null)
    setDragOverIndex(null)
  }, [stopAutoScroll])

  // These listeners live on the document rather than on the list: the back office header is
  // position:fixed over the top of the viewport, so a list-scoped dragover never sees the cursor
  // in the upper scroll zone, and a drop can land anywhere once the page has scrolled.
  useEffect(() => {
    const handleDocumentDragOver = (e: DragEvent) => {
      if (!isDragging.current) {
        return
      }
      e.preventDefault()
      pointerY.current = e.clientY
    }

    const handleDocumentDrop = (e: DragEvent) => {
      if (!isDragging.current) {
        return
      }
      e.preventDefault()

      const methodId = draggedItemRef.current
      const dropIndex = dragOverIndexRef.current

      if (!didDrop.current && methodId !== null && dropIndex !== null) {
        didDrop.current = true
        commitReorder(methodId, dropIndex)
      }

      endDrag()
    }

    const handleEscape = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        dragCancelled.current = true
      }
    }

    document.addEventListener("dragover", handleDocumentDragOver)
    document.addEventListener("drop", handleDocumentDrop)
    document.addEventListener("keydown", handleEscape)
    document.addEventListener("keyup", handleEscape)

    return () => {
      document.removeEventListener("dragover", handleDocumentDragOver)
      document.removeEventListener("drop", handleDocumentDrop)
      document.removeEventListener("keydown", handleEscape)
      document.removeEventListener("keyup", handleEscape)
      stopAutoScroll()
    }
  }, [commitReorder, endDrag, stopAutoScroll])

  const handleDragStart = (e: React.DragEvent, methodId: string) => {
    const method = methods.find(m => m.id === methodId)

    // Prevent drag if disabled or if the method is expanded
    if (!isDragEnabled || method?.isExpanded) {
      e.preventDefault()
      return
    }
    setDraggedItem(methodId)
    e.dataTransfer.effectAllowed = "move"
    e.dataTransfer.setData("text/html", methodId)

    didDrop.current = false
    dragCancelled.current = false
    scrolledAt.current = 0
    draggedItemRef.current = methodId
    dragOverIndexRef.current = null
    pointerY.current = e.clientY
    isDragging.current = true
    measureRows()
    stopAutoScroll()
    rafId.current = requestAnimationFrame(autoScrollTick)
  }

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault()
    e.dataTransfer.dropEffect = "move"
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()

    const methodId = draggedItemRef.current
    const dropIndex = dragOverIndexRef.current

    if (methodId === null || dropIndex === null) {
      return
    }

    didDrop.current = true
    commitReorder(methodId, dropIndex)
    endDrag()
  }

  // A release while the page is auto-scrolling never produces a drop event: the cards move out
  // from under the pointer and the browser cancels the drag, so the reorder would be lost without
  // a word. Replay it from dragend instead, after a short wait for the Escape keyup that tells a
  // real cancel apart from this.
  const handleDragEnd = () => {
    const methodId = draggedItemRef.current
    const dropIndex = dragOverIndexRef.current
    const wasAutoScrolling = Date.now() - scrolledAt.current < SCROLLING_RECENTLY_MS
    const lostTheDrop = !didDrop.current && wasAutoScrolling && methodId !== null && dropIndex !== null

    endDrag()

    if (!lostTheDrop) {
      return
    }

    window.setTimeout(() => {
      if (dragCancelled.current) {
        return
      }
      commitReorder(methodId, dropIndex)
    }, CANCEL_GRACE_MS)
  }

  return (
    <div className="space-y-4" ref={containerRef}>
      {methods.map((method, index) => (
        <div
          key={method.id}
          className="animate-in fade-in slide-in-from-left-4 duration-300 ease-out"
          style={{
            animationDelay: `${index * 50}ms`,
            transition: isReordering ? "transform 0.3s ease-in-out" : undefined,
          }}
        >
          <PaymentMethodCard
            method={method}
            index={index + 1}
            countries={countries}
            carriers={carriers}
            customerGroups={customerGroups}
            languages={languages}
            onlyPaymentsMethods={onlyPaymentsMethods}
            onlyOrderMethods={onlyOrderMethods}
            onToggleExpanded={() => onToggleExpanded(method.id)}
            onUpdateSettings={(settings) => onUpdateSettings(method.id, settings)}
            onSaveSettings={() => onSaveSettings(method.id)}
            onDragStart={(e) => handleDragStart(e, method.id)}
            onDragOver={handleDragOver}
            onDrop={handleDrop}
            onDragEnd={handleDragEnd}
            isDragging={draggedItem === method.id}
            isDragEnabled={isDragEnabled}
            isDragOver={dragOverIndex === index}
            isSaving={savingMethodId === method.id}
          />
        </div>
      ))}
    </div>
  )
}
