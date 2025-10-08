import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import AdvancedSettingsPage from '../pages/advanced-settings'
import '../shared/styles/globals.css'

// Mount the advanced settings component to PrestaShop
function AdvancedSettingsApp() {
  return (
    <div id="mollie-advanced-settings-app" className="mollie-advanced-settings-app">
      <AdvancedSettingsPage />
    </div>
  )
}

// Mount the React app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('mollie-advanced-settings-root')
  if (container) {
    const root = ReactDOM.createRoot(container)
    root.render(
      <StrictMode>
        <AdvancedSettingsApp />
      </StrictMode>
    )
  }
})
