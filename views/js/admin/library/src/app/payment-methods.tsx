import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import PaymentMethodsPage from '../pages/payment-methods'
import '../shared/styles/globals.css'

// Mount the payment methods component to PrestaShop
function PaymentMethodsApp() {
  return (
    <div className="mollie-payment-methods-app">
      <PaymentMethodsPage />
    </div>
  )
}

// Mount the React app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('mollie-payment-methods-root')
  if (container) {
    const root = ReactDOM.createRoot(container)
    root.render(
      <StrictMode>
        <PaymentMethodsApp />
      </StrictMode>
    )
  }
})