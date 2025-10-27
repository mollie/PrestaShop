import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import AuthorizationPage from '../pages/authorization'
import '../shared/styles/globals.css'

// Mount the authorization component to PrestaShop
function AuthorizationApp() {
  return (
    <div className="mollie-authorization-app">
      <AuthorizationPage />
    </div>
  )
}

// Mount the React app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('mollie-authentication-root')
  if (container) {
    const root = ReactDOM.createRoot(container)
    root.render(
      <StrictMode>
        <AuthorizationApp />
      </StrictMode>
    )
  }
})