/**
 * Hook for showing inline notifications within the Mollie payment methods container
 */
export function useNotification() {
  const showNotification = (message: string, type: 'success' | 'error') => {
    const container = document.getElementById('mollie-payment-methods-app')
    if (!container) {
      console.warn('Mollie container not found, falling back to console')
      if (type === 'success') {
        console.log('✅ Success:', message)
      } else {
        console.error('❌ Error:', message)
      }
      return
    }

    // Create notification element
    const notification = document.createElement('div')
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger'
    const icon = type === 'success' ? '✓' : '✕'

    notification.className = `alert ${alertClass} alert-dismissible fade show`
    notification.setAttribute('role', 'alert')
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      min-width: 300px;
      max-width: 500px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: slideInRight 0.3s ease-out;
    `

    notification.innerHTML = `
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
      <strong>${icon}</strong> ${message}
    `

    // Add CSS animations if not already present
    if (!document.getElementById('mollie-notification-animations')) {
      const style = document.createElement('style')
      style.id = 'mollie-notification-animations'
      style.textContent = `
        @keyframes slideInRight {
          from {
            opacity: 0;
            transform: translateX(100%);
          }
          to {
            opacity: 1;
            transform: translateX(0);
          }
        }
        @keyframes slideOutRight {
          from {
            opacity: 1;
            transform: translateX(0);
          }
          to {
            opacity: 0;
            transform: translateX(100%);
          }
        }
      `
      document.head.appendChild(style)
    }

    // Insert notification at the top of the container
    container.insertBefore(notification, container.firstChild)

    // Auto-remove after duration
    const duration = type === 'success' ? 5000 : 7000
    setTimeout(() => {
      notification.style.animation = 'slideOutRight 0.3s ease-out'
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove()
        }
      }, 300)
    }, duration)

    // Remove on close button click
    const closeBtn = notification.querySelector('.close')
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        notification.style.animation = 'slideOutRight 0.3s ease-out'
        setTimeout(() => {
          if (notification.parentNode) {
            notification.remove()
          }
        }, 300)
      })
    }
  }

  const showSuccess = (message: string) => {
    showNotification(message, 'success')
  }

  const showError = (message: string) => {
    showNotification(message, 'error')
  }

  return { showSuccess, showError }
}
