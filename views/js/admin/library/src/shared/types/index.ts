export interface MollieConfig {
  mode: 'live' | 'test'
  apiKey: string
  isConnected: boolean
}

export interface AuthorizationState {
  config: MollieConfig
  loading: boolean
  error: string | null
}