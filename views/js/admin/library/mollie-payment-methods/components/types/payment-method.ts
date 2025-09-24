export interface PaymentMethod {
  id: string
  name: string
  type: "card" | "other"
  status: "active" | "inactive"
  isExpanded: boolean
  settings: {
    enabled: boolean
    title: string
    mollieComponents: boolean
    oneClickPayments: boolean
    transactionDescription: string
    apiSelection: "payments" | "orders"
    paymentRestrictions: {
      acceptFrom: string
      excludeCountries: string[]
      excludeCustomerGroups: string[]
    }
    paymentFees: {
      enabled: boolean
      type: "fixed" | "percentage"
      taxGroup: string
      maxFee: string
      minAmount: string
      maxAmount: string
    }
    orderRestrictions: {
      minAmount: string
      maxAmount: string
    }
  }
}
