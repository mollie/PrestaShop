"use client"

import type React from "react"
import { useState, useRef, useEffect } from "react"
import "./advanced-settings.css"
import { advancedSettingsApiService, type CarrierData, type SaveCarrierData } from "../../services/AdvancedSettingsApiService"
import { AdvancedSettingsSkeleton } from "./components/advanced-settings-skeleton"

// ChevronDown icon component
const ChevronDown = ({ className }: { className?: string }) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="16"
    height="16"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    strokeWidth="2"
    strokeLinecap="round"
    strokeLinejoin="round"
    className={className}
  >
    <polyline points="6 9 12 15 18 9"></polyline>
  </svg>
)

// Utility function for class names
function cn(...classes: (string | boolean | undefined)[]) {
  return classes.filter(Boolean).join(" ")
}

interface RadioSelectProps {
  value: string
  onValueChange: (value: string) => void
  options: { value: string; label: string }[]
  placeholder?: string
  className?: string
}

function RadioSelect({ value, onValueChange, options, placeholder, className }: RadioSelectProps) {
  const [isOpen, setIsOpen] = useState(false)
  const selectedOption = options.find((opt) => opt.value === value)
  const dropdownRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside)
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isOpen])

  return (
    <div ref={dropdownRef} className={cn("relative", isOpen && "dropdown-open", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="radio-select-button"
      >
        <span className={selectedOption ? "radio-select-selected" : "radio-select-placeholder"}>
          {selectedOption?.label || placeholder}
        </span>
        <ChevronDown className={cn("radio-select-icon", isOpen && "radio-select-icon-open")} />
      </button>

      {isOpen && (
        <div className="radio-select-dropdown">
          <div className="radio-select-options">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => {
                  onValueChange(option.value)
                  setIsOpen(false)
                }}
                className="radio-select-option"
              >
                <div className="radio-select-radio-container">
                  <div className={value === option.value ? "radio-select-radio-checked" : "radio-select-radio-unchecked"}>
                    {value === option.value && <div className="radio-select-radio-dot" />}
                  </div>
                </div>
                <span className="radio-select-option-label">{option.label}</span>
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}

interface MultiSelectProps {
  value: string[]
  onValueChange: (value: string[]) => void
  options: { value: string; label: string }[]
  placeholder?: string
  className?: string
}

function MultiSelect({ value, onValueChange, options, placeholder, className }: MultiSelectProps) {
  const [isOpen, setIsOpen] = useState(false)
  const selectedOptions = options.filter((opt) => value.includes(opt.value))
  const dropdownRef = useRef<HTMLDivElement>(null)

  const toggleOption = (optionValue: string) => {
    if (value.includes(optionValue)) {
      onValueChange(value.filter((v) => v !== optionValue))
    } else {
      onValueChange([...value, optionValue])
    }
  }

  const removeOption = (optionValue: string, e: React.MouseEvent) => {
    e.stopPropagation()
    onValueChange(value.filter((v) => v !== optionValue))
  }

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
        setIsOpen(false)
      }
    }

    if (isOpen) {
      document.addEventListener('mousedown', handleClickOutside)
    }

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [isOpen])

  return (
    <div ref={dropdownRef} className={cn("relative", isOpen && "dropdown-open", className)}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className="multi-select-button"
      >
        <div className="multi-select-content">
          {selectedOptions.length > 0 ? (
            <div className="multi-select-tags">
              {selectedOptions.map((option) => (
                <span key={option.value} className="multi-select-tag">
                  {option.label}
                  <button
                    type="button"
                    onClick={(e) => removeOption(option.value, e)}
                    className="multi-select-tag-remove"
                  >
                    ×
                  </button>
                </span>
              ))}
            </div>
          ) : (
            <span className="radio-select-placeholder">{placeholder}</span>
          )}
        </div>
        <ChevronDown className={cn("radio-select-icon", isOpen && "radio-select-icon-open")} />
      </button>

      {isOpen && (
        <div className="radio-select-dropdown">
          <div className="radio-select-options">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => toggleOption(option.value)}
                className="radio-select-option"
              >
                <div className="multi-select-checkbox-container">
                  <div className={value.includes(option.value) ? "multi-select-checkbox-checked" : "multi-select-checkbox-unchecked"}>
                    {value.includes(option.value) && (
                      <svg className="multi-select-checkbox-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path
                          fillRule="evenodd"
                          d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                          clipRule="evenodd"
                        />
                      </svg>
                    )}
                  </div>
                </div>
                <span className="radio-select-option-label">{option.label}</span>
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}

interface CarrierRow {
  id: string
  name: string
  carrierUrl: string
  emptyInput: string
}

interface StatusMapping {
  mollieStatus: string
  prestashopStatus: string
  configKey: string
}

interface EmailStatus {
  status: string
  enabled: boolean
  configKey: string
}

interface OrderStatus {
  id: string
  name: string
}

const AdvancedSettings: React.FC = () => {
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [notification, setNotification] = useState<{ message: string; type: 'success' | 'error' } | null>(null)
  const [invoiceOption, setInvoiceOption] = useState("default")
  const [confirmationEmail, setConfirmationEmail] = useState("paid")
  const [autoShip, setAutoShip] = useState(true)
  const [autoShipStatuses, setAutoShipStatuses] = useState<string[]>(["shipped"])
  const [debugMode, setDebugMode] = useState(true)
  const [logLevel, setLogLevel] = useState("errors")
  const [logoDisplay, setLogoDisplay] = useState("")
  const [translateMollie, setTranslateMollie] = useState("")
  const [cssPath, setCssPath] = useState("")

  const [carriers, setCarriers] = useState<CarrierRow[]>([])

  const [statusMappings, setStatusMappings] = useState<StatusMapping[]>([])
  const [emailStatuses, setEmailStatuses] = useState<EmailStatus[]>([])
  const [orderStatuses, setOrderStatuses] = useState<OrderStatus[]>([])

  // Dropdown options from backend
  const [invoiceOptions, setInvoiceOptions] = useState<{ id: string; name: string }[]>([])
  const [confirmationEmailOptions, setConfirmationEmailOptions] = useState<{ id: string; name: string }[]>([])
  const [logLevelOptions, setLogLevelOptions] = useState<{ id: string; name: string }[]>([])
  const [logoDisplayOptions, setLogoDisplayOptions] = useState<{ id: string; name: string }[]>([])
  const [translateMollieOptions, setTranslateMollieOptions] = useState<{ id: string; name: string }[]>([])

  const carrierUrlOptions = [
    { value: "do_not_auto_ship", label: "Do not automatically ship" },
    { value: "no_tracking_info", label: "No tracking information" },
    { value: "carrier_url", label: "Carrier URL" },
    { value: "custom_url", label: "Custom URL" },
    { value: "module", label: "Module" },
  ]

  // Load settings on mount
  useEffect(() => {
    loadSettings()
  }, [])

  // Auto-hide notification after 5 seconds
  useEffect(() => {
    if (notification) {
      const timer = setTimeout(() => {
        setNotification(null)
      }, 5000)
      return () => clearTimeout(timer)
    }
  }, [notification])

  const loadSettings = async () => {
    try {
      setLoading(true)
      const response = await advancedSettingsApiService.getSettings()

      if (response.success && response.data) {
        const data = response.data

        // Dropdown options (load first so we can use them for defaults)
        const invoiceOpts = data.options?.invoiceOptions || []
        const confirmEmailOpts = data.options?.confirmationEmailOptions || []
        const logLevelOpts = data.options?.logLevelOptions || []
        const logoDisplayOpts = data.options?.logoDisplayOptions || []
        const translateMollieOpts = data.options?.translateMollieOptions || []

        setInvoiceOptions(invoiceOpts)
        setConfirmationEmailOptions(confirmEmailOpts)
        setLogLevelOptions(logLevelOpts)
        setLogoDisplayOptions(logoDisplayOpts)
        setTranslateMollieOptions(translateMollieOpts)

        // Order Settings - backend already provides defaults and string types
        setInvoiceOption(data.invoiceOption || invoiceOpts[0]?.id || "")
        setConfirmationEmail(data.confirmationEmail || confirmEmailOpts[0]?.id || "")

        // Shipping Settings
        setAutoShip(data.autoShip || false)
        setAutoShipStatuses(data.autoShipStatuses || [])

        // Map carriers data to component format
        const mappedCarriers = data.carriers.map((carrier: CarrierData) => ({
          id: carrier.id,
          name: carrier.name,
          carrierUrl: carrier.urlSource || "",
          emptyInput: carrier.customUrl || "",
        }))
        setCarriers(mappedCarriers)

        // Error Debugging
        setDebugMode(data.debugMode || false)
        setLogLevel(data.logLevel || logLevelOpts[0]?.id || "")

        // Visual Settings
        setLogoDisplay(data.logoDisplay || logoDisplayOpts[0]?.id || "")
        setCssPath(data.cssPath || "")
        setTranslateMollie(data.translateMollie || translateMollieOpts[0]?.id || "")

        // Status Mappings
        setStatusMappings(data.statusMappings || [])

        // Email Statuses
        setEmailStatuses(data.emailStatuses || [])

        // Order Statuses for dropdowns
        setOrderStatuses(data.options?.orderStatuses || [])
      }
    } catch (error) {
      console.error('Failed to load settings:', error)
      setNotification({ message: 'Failed to load settings', type: 'error' })
    } finally {
      setLoading(false)
    }
  }

  const saveSettings = async () => {
    try {
      setSaving(true)

      // Map carriers back to API format
      const carriersData: SaveCarrierData[] = carriers.map(carrier => ({
        id: carrier.id,
        urlSource: carrier.carrierUrl,
        customUrl: carrier.emptyInput,
      }))

      const response = await advancedSettingsApiService.saveSettings({
        invoiceOption,
        confirmationEmail,
        autoShip,
        autoShipStatuses,
        carriers: carriersData,
        debugMode,
        logLevel,
        logoDisplay,
        cssPath,
        translateMollie,
        statusMappings,
        emailStatuses,
      })

      if (response.success) {
        setNotification({ message: 'Settings saved successfully', type: 'success' })
      } else {
        setNotification({ message: response.message || 'Failed to save settings', type: 'error' })
      }
    } catch (error) {
      console.error('Failed to save settings:', error)
      setNotification({ message: 'Failed to save settings', type: 'error' })
    } finally {
      setSaving(false)
    }
  }


  const toggleEmailStatus = (configKey: string) => {
    setEmailStatuses((prev) => prev.map((item) => (item.configKey === configKey ? { ...item, enabled: !item.enabled } : item)))
  }

  const handleStatusMappingChange = (configKey: string, prestashopStatus: string) => {
    setStatusMappings((prev) => prev.map((item) => (item.configKey === configKey ? { ...item, prestashopStatus } : item)))
  }

  const handleCarrierUrlChange = (id: string, value: string) => {
    setCarriers((prev) => prev.map((carrier) => (carrier.id === id ? { ...carrier, carrierUrl: value } : carrier)))
  }

  const handleEmptyInputChange = (id: string, value: string) => {
    setCarriers((prev) => prev.map((carrier) => (carrier.id === id ? { ...carrier, emptyInput: value } : carrier)))
  }

  if (loading) {
    return <AdvancedSettingsSkeleton />
  }

  return (
    <div className="advanced-settings">
      {/* Notification */}
      {notification && (
        <div
          className={`fixed right-6 top-6 z-[9999] border rounded-lg p-4 flex items-center gap-3 shadow-lg min-w-[320px] max-w-[500px] ${
            notification.type === 'success'
              ? 'bg-green-50 border-green-200'
              : 'bg-red-50 border-red-200'
          }`}
          style={{ animation: 'slideInRight 0.3s ease-out' }}
        >
          {notification.type === 'success' ? (
            <svg className="h-5 w-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          ) : (
            <svg className="h-5 w-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          )}
          <div className={`text-sm font-medium ${notification.type === 'success' ? 'text-green-800' : 'text-red-800'}`}>
            {notification.message}
          </div>
          <button
            onClick={() => setNotification(null)}
            className="ml-auto text-gray-500 hover:text-gray-700"
          >
            <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      )}

      <div className="settings-header">
        <h1>Advanced settings</h1>
        <p className="settings-subtitle">Manage your order settings, visual representation and error logging</p>
      </div>

      {/* Order Settings */}
      <section className="settings-section">
        <h2 className="section-title">Order settings</h2>

        <div className="form-group">
          <label className="form-label">Select when to create the order invoice</label>
          <RadioSelect
            value={invoiceOption}
            onValueChange={setInvoiceOption}
            options={invoiceOptions.map(opt => ({ value: opt.id, label: opt.name }))}
            placeholder="Select option"
          />
        </div>

        <div className="info-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="info-icon">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 16v-4"></path>
            <path d="M12 8h.01"></path>
          </svg>
          <div className="info-content">
            <p>
              <strong>Default:</strong> The invoice is created based on Order settings {">"} Statuses. There is no
              custom status created.
            </p>
            <p>
              <strong>Authorized:</strong> Create a full invoice when the order is authorized. Custom status is created.
            </p>
            <p>
              <strong>On Shipment:</strong> Create a full invoice when the order is shipped. Custom status is created.
            </p>
          </div>
        </div>

        <div className="form-group">
          <label className="form-label">Send order confirmation email</label>
          <RadioSelect
            value={confirmationEmail}
            onValueChange={setConfirmationEmail}
            options={confirmationEmailOptions.map(opt => ({ value: opt.id, label: opt.name }))}
            placeholder="Select option"
          />
        </div>
      </section>

      {/* Shipping Settings */}
      <section className="settings-section">
        <h2 className="section-title">Shipping Settings</h2>

        <div className="toggle-group">
          <div className="toggle-content">
            <div>
              <div className="toggle-label">Automatically Ship on Marked Statuses</div>
              <div className="toggle-description">Enable automatic shipping for selected statuses</div>
            </div>
            <label className="toggle-switch">
              <input type="checkbox" checked={autoShip} onChange={(e) => setAutoShip(e.target.checked)} />
              <span className="toggle-slider"></span>
            </label>
            <span className="toggle-status">{autoShip ? "Enabled" : "Disabled"}</span>
          </div>
        </div>

        <div className="form-group">
          <label className="form-label">Automatically ship when one of these statuses is reached</label>
          <MultiSelect
            value={autoShipStatuses}
            onValueChange={setAutoShipStatuses}
            options={orderStatuses.map(status => ({ value: status.id, label: status.name }))}
            placeholder="Select statuses"
          />
        </div>

        <div className="form-group">
          <label className="form-label">Send shipment information to Mollie</label>
        </div>

        <div className="info-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="info-icon">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 16v-4"></path>
            <path d="M12 8h.01"></path>
          </svg>
          <div className="info-content">
            <p>Configure the shipment information to send to Mollie.</p>
            <p>You can use the following variables for the carrier URLs:</p>
            <ul>
              <li>
                <strong>%shipping_number%</strong> - Shipping number
              </li>
              <li>
                <strong>%track.trace_code%</strong> - Tracking code
              </li>
              <li>
                <strong>%mollie.postal.code%</strong> - Billing postcode
              </li>
              <li>
                <strong>%delivery.country_iso%</strong> - Shipping country code
              </li>
              <li>
                <strong>%delivery.postal.code%</strong> - Shipping postcode
              </li>
              <li>
                <strong>%lang_iso%</strong> - 2-letter language code
              </li>
            </ul>
          </div>
        </div>

        <div className="carrier-table">
          {carriers.map((carrier) => (
            <div key={carrier.id} className="carrier-row">
              <div className="carrier-col carrier-name">{carrier.name}</div>
              <div className="carrier-col carrier-select">
                <RadioSelect
                  value={carrier.carrierUrl}
                  onValueChange={(value) => handleCarrierUrlChange(carrier.id, value)}
                  options={carrierUrlOptions}
                  placeholder="Select option"
                />
              </div>
              <div className="carrier-col carrier-input">
                <input
                  type="text"
                  className="form-input"
                  value={carrier.emptyInput}
                  onChange={(e) => handleEmptyInputChange(carrier.id, e.target.value)}
                  placeholder=""
                  disabled={carrier.carrierUrl !== "custom_url"}
                />
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Error Debugging */}
      <section className="settings-section">
        <h2 className="section-title">Error Debugging</h2>

        <div className="toggle-group">
          <div className="toggle-content">
            <div>
              <div className="toggle-label">Debug Mode</div>
              <div className="toggle-description">Enable detailed error logging</div>
            </div>
            <label className="toggle-switch">
              <input type="checkbox" checked={debugMode} onChange={(e) => setDebugMode(e.target.checked)} />
              <span className="toggle-slider"></span>
            </label>
            <span className="toggle-status">{debugMode ? "Enabled" : "Disabled"}</span>
          </div>
        </div>

        <div className="form-group">
          <label className="form-label">Log Level</label>
          <div className="button-group">
            {logLevelOptions.map((option) => (
              <button
                key={option.id}
                className={`btn-group-item ${logLevel === option.id ? "active" : ""}`}
                onClick={() => setLogLevel(option.id)}
              >
                {option.name}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Visual Settings */}
      <section className="settings-section">
        <h2 className="section-title">Visual Settings</h2>

        <div className="form-group">
          <label className="form-label">Payment Method Logo Display</label>
          <div className="button-group">
            {logoDisplayOptions.map((option) => (
              <button
                key={option.id}
                className={`btn-group-item ${logoDisplay === option.id ? "active" : ""}`}
                onClick={() => setLogoDisplay(option.id)}
              >
                {option.name}
              </button>
            ))}
          </div>
          <div className="checkout-preview">
            <span>Checkout preview:</span>
            <div className="checkout-preview-card">
              <img
                src={logoDisplay === "big"
                  ? "https://www.mollie.com/external/icons/payment-methods/creditcard%402x.png"
                  : "https://www.mollie.com/external/icons/payment-methods/creditcard.png"
                }
                alt="Card"
                className={`payment-card-icon ${logoDisplay === "hide" ? "hidden" : ""}`}
              />
              <span className="preview-label">Card</span>
            </div>
          </div>
        </div>

        <div className="form-group">
          <label className="form-label">Custom CSS File Path</label>
          <input
            type="text"
            className="form-input"
            placeholder="modules/mollie/custom.css"
            value={cssPath}
            onChange={(e) => setCssPath(e.target.value)}
          />
        </div>

        <div className="form-group">
          <label className="form-label">Use selected locale in webshop</label>
          <RadioSelect
            value={translateMollie}
            onValueChange={setTranslateMollie}
            options={translateMollieOptions.map(opt => ({ value: opt.id, label: opt.name }))}
            placeholder="Select option"
          />
        </div>
      </section>

      {/* Order Status Mapping */}
      <section className="settings-section">
        <h2 className="section-title">Order Status Mapping</h2>

        <div className="info-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="info-icon">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 16v-4"></path>
            <path d="M12 8h.01"></path>
          </svg>
          <div className="info-content">
            <p>
              From the dropdown select a PrestaShop order status that will be set when the respective Mollie payment
              status is triggered.
            </p>
          </div>
        </div>

        <div className="status-mapping-table">
          {statusMappings.map((mapping) => (
            <div key={mapping.configKey} className="status-mapping-row">
              <div className="status-mapping-col">
                <div className="status-label">{mapping.mollieStatus}</div>
                <div className="status-sublabel">Mollie Payment Status</div>
              </div>
              <div className="status-mapping-col">
                <div className="status-sublabel" style={{ textAlign: 'right' }}>PrestaShop order status</div>
              </div>
              <div className="status-mapping-col">
                <RadioSelect
                  value={mapping.prestashopStatus}
                  onValueChange={(value) => handleStatusMappingChange(mapping.configKey, value)}
                  options={orderStatuses.map(status => ({ value: status.id, label: status.name }))}
                  placeholder="Select status"
                />
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Order Status Emails */}
      <section className="settings-section">
        <h2 className="section-title">Order Status Emails</h2>

        <div className="info-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="info-icon">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 16v-4"></path>
            <path d="M12 8h.01"></path>
          </svg>
          <div className="info-content">
            <p>If enabled, customers will receive an email when the order status changes</p>
          </div>
        </div>

        <div className="email-status-list">
          {emailStatuses.map((email) => (
            <div key={email.configKey} className="email-status-row">
              <div className="email-status-info">
                <div className="status-label">{email.status}</div>
                <div className="status-sublabel">Prestashop order status</div>
              </div>
              <div className="email-status-toggle">
                <label className="toggle-switch">
                  <input type="checkbox" checked={email.enabled} onChange={() => toggleEmailStatus(email.configKey)} />
                  <span className="toggle-slider"></span>
                </label>
                <span className="toggle-label-text">Send email on status</span>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Save Button */}
      <div className="settings-footer">
        <button
          className="btn-save-settings"
          onClick={saveSettings}
          disabled={saving}
        >
          {saving ? 'Saving...' : 'Save Settings'}
        </button>
      </div>
    </div>
  )
}

export default AdvancedSettings
