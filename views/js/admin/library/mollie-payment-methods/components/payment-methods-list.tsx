"use client"

import type React from "react"

import { useState } from "react"
import { PaymentMethodCard } from "./payment-method-card"
import type { PaymentMethod } from "./types/payment-method"

interface PaymentMethodsListProps {
  methods: PaymentMethod[]
  onToggleExpanded: (id: string) => void
  onUpdateSettings: (id: string, settings: Partial<PaymentMethod["settings"]>) => void
  onReorder: (methods: PaymentMethod[]) => void
}

export function PaymentMethodsList({
  methods,
  onToggleExpanded,
  onUpdateSettings,
  onReorder,
}: PaymentMethodsListProps) {
  const [draggedItem, setDraggedItem] = useState<string | null>(null)
  const [dragOverIndex, setDragOverIndex] = useState<number | null>(null)
  const [isReordering, setIsReordering] = useState(false)

  const handleDragStart = (e: React.DragEvent, methodId: string) => {
    setDraggedItem(methodId)
    e.dataTransfer.effectAllowed = "move"
    e.dataTransfer.setData("text/html", methodId)
  }

  const handleDragOver = (e: React.DragEvent, index: number) => {
    e.preventDefault()
    e.dataTransfer.dropEffect = "move"
    setDragOverIndex(index)
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
  }

  return (
    <div className="space-y-4">
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
            onToggleExpanded={() => onToggleExpanded(method.id)}
            onUpdateSettings={(settings) => onUpdateSettings(method.id, settings)}
            onDragStart={(e) => handleDragStart(e, method.id)}
            onDragOver={(e) => handleDragOver(e, index)}
            onDragLeave={handleDragLeave}
            onDrop={(e) => handleDrop(e, index)}
            onDragEnd={handleDragEnd}
            isDragging={draggedItem === method.id}
            isDragOver={dragOverIndex === index}
          />
        </div>
      ))}
    </div>
  )
}
