"use client"

import type React from "react"

import { useState, useRef, useEffect } from "react"
import { PaymentMethodCard } from "./payment-method-card"
import type { PaymentMethod, Country, CustomerGroup } from "../../../services/PaymentMethodsApiService"

interface PaymentMethodsListProps {
  methods: PaymentMethod[]
  countries: Country[]
  customerGroups: CustomerGroup[]
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
  customerGroups,
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
  // For smooth auto-scroll
  const autoScrollInterval = useRef<NodeJS.Timeout | null>(null)
  const lastDirection = useRef<'up' | 'down' | null>(null)

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
  }

  // Only highlight drop target on item drag over
  const handleDragOver = (e: React.DragEvent, index: number) => {
    e.preventDefault()
    e.dataTransfer.dropEffect = "move"
    setDragOverIndex(index)
  }

  // Auto-scroll when dragging near top/bottom of viewport
  const handleContainerDragOver = (e: React.DragEvent) => {
  const SCROLL_MARGIN = 200;
    const SCROLL_SPEED = 32;
    const y = e.clientY;
    const windowHeight = window.innerHeight;
    const scrollingElement = document.scrollingElement || document.documentElement;

    let navUl = document.querySelector('.nav ul');
    if (!navUl) navUl = document.querySelector('.nav.nav-pills');
    if (!navUl) navUl = document.querySelector('.nav.nav-tabs');
    if (!navUl) navUl = document.querySelector('ul.nav');
    let shouldScrollUp = false;
    if (navUl) {
      const navRect = navUl.getBoundingClientRect();
      if (y < navRect.bottom + SCROLL_MARGIN) {
        shouldScrollUp = true;
      }
    }
    const shouldScrollDown = y > windowHeight - SCROLL_MARGIN;

    // Start/stop interval for smooth scroll
    if (shouldScrollUp) {
      if (lastDirection.current !== 'up') {
        if (autoScrollInterval.current) clearInterval(autoScrollInterval.current);
        autoScrollInterval.current = setInterval(() => {
          scrollingElement.scrollBy({ top: -SCROLL_SPEED, behavior: 'auto' });
        }, 16);
        lastDirection.current = 'up';
      }
    } else if (shouldScrollDown) {
      if (lastDirection.current !== 'down') {
        if (autoScrollInterval.current) clearInterval(autoScrollInterval.current);
        autoScrollInterval.current = setInterval(() => {
          scrollingElement.scrollBy({ top: SCROLL_SPEED, behavior: 'auto' });
        }, 16);
        lastDirection.current = 'down';
      }
    } else {
      if (autoScrollInterval.current) clearInterval(autoScrollInterval.current);
      autoScrollInterval.current = null;
      lastDirection.current = null;
    }
  }

  // Stop auto-scroll when drag leaves container
  const handleContainerDragLeave = () => {
    if (autoScrollInterval.current) {
      clearInterval(autoScrollInterval.current);
    }
    autoScrollInterval.current = null
    lastDirection.current = null
  }

  const handleDragLeave = () => {
    setDragOverIndex(null)
  }

  const handleDrop = (e: React.DragEvent, dropIndex: number) => {
    e.preventDefault()

    if (!draggedItem) return

    const draggedIndex = methods.findIndex((method) => method.id === draggedItem)

    if (draggedIndex === -1 || draggedIndex === dropIndex) {
      setDraggedItem(null)
      setDragOverIndex(null)
      // clear any running auto-scroll interval to prevent unexpected scrolling after drop
      if (autoScrollInterval.current) {
        clearInterval(autoScrollInterval.current)
      }
      autoScrollInterval.current = null
      lastDirection.current = null
      return
    }

    setIsReordering(true)

    const newMethods = [...methods]
    const [draggedMethod] = newMethods.splice(draggedIndex, 1)
    newMethods.splice(dropIndex, 0, draggedMethod)

    setTimeout(() => {
      onReorder(newMethods)
      setIsReordering(false)
    }, 150)

    setDraggedItem(null)
    setDragOverIndex(null)
  }

  const handleDragEnd = () => {
    setDraggedItem(null)
    setDragOverIndex(null)
    // ensure auto-scroll interval is cleared on drag end
    if (autoScrollInterval.current) {
      clearInterval(autoScrollInterval.current)
    }
    autoScrollInterval.current = null
    lastDirection.current = null
  }

  // cleanup on unmount: ensure no interval remains
  useEffect(() => {
    return () => {
      if (autoScrollInterval.current) {
        clearInterval(autoScrollInterval.current)
      }
      autoScrollInterval.current = null
      lastDirection.current = null
    }
  }, [])

  return (
    <div className="space-y-4" onDragOver={handleContainerDragOver} onDragLeave={handleContainerDragLeave}>
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
            customerGroups={customerGroups}
            onToggleExpanded={() => onToggleExpanded(method.id)}
            onUpdateSettings={(settings) => onUpdateSettings(method.id, settings)}
            onSaveSettings={() => onSaveSettings(method.id)}
            onDragStart={(e) => handleDragStart(e, method.id)}
            onDragOver={(e) => handleDragOver(e, index)}
            onDragLeave={handleDragLeave}
            onDrop={(e) => handleDrop(e, index)}
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
