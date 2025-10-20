
import { StrictMode } from 'react';
import ReactDOM from 'react-dom/client';
import AuthorizationPage from '../pages/authorization';
import '../shared/styles/globals.css';

// Define the web component
class MollieAuthorizationElement extends HTMLElement {
  private root: ShadowRoot;
  constructor() {
    super();
    this.root = this.attachShadow({ mode: 'open' });
  }
  connectedCallback() {
    // Create a container for React to render into
    const mountPoint = document.createElement('div');
    mountPoint.setAttribute('id', 'mollie-authorization-react-root');
    this.root.appendChild(mountPoint);
    // Inject compiled CSS into shadow root
    fetch('https://marijus-dev.invertusdemo.com/modules/mollie/views/js/admin/library/dist/assets/globals.css?v=1760959843')
      .then(response => response.text())
      .then(css => {
        const styleTag = document.createElement('style');
        styleTag.textContent = css;
        this.root.appendChild(styleTag);
        // Render React app after CSS is loaded
        const reactRoot = ReactDOM.createRoot(mountPoint);
        reactRoot.render(
          <StrictMode>
            <div className="mollie-authorization-app">
              <AuthorizationPage />
            </div>
          </StrictMode>
        );
      });
  }
}

// Register the web component
if (!customElements.get('mollie-authorization')) {
  customElements.define('mollie-authorization', MollieAuthorizationElement);
}

// Mount the web component when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('mollie-authentication-root');
  if (container && !container.querySelector('mollie-authorization')) {
    const element = document.createElement('mollie-authorization');
    container.appendChild(element);
  }
});