/**
 * Dynamic script loader utility for PrestaShop CloudSync integration
 * This utility ensures scripts are loaded at runtime, avoiding Vite bundling issues
 */

export interface ScriptLoadOptions {
  id?: string
  async?: boolean
  defer?: boolean
  onLoad?: () => void
  onError?: (error: Event) => void
}

class ScriptLoader {
  private loadedScripts: Set<string> = new Set()
  private loadingScripts: Map<string, Promise<void>> = new Map()

  /**
   * Dynamically load a script and return a promise
   */
  async loadScript(src: string, options: ScriptLoadOptions = {}): Promise<void> {
    // If script is already loaded, resolve immediately
    if (this.loadedScripts.has(src)) {
      return Promise.resolve()
    }

    // If script is currently loading, return the existing promise
    if (this.loadingScripts.has(src)) {
      return this.loadingScripts.get(src)!
    }

    // Create new loading promise
    const loadPromise = new Promise<void>((resolve, reject) => {
      const script = document.createElement('script')
      
      script.src = src
      script.async = options.async !== false // Default to true
      script.defer = options.defer || false
      
      if (options.id) {
        script.id = options.id
      }

      script.onload = () => {
        this.loadedScripts.add(src)
        this.loadingScripts.delete(src)
        options.onLoad?.()
        resolve()
      }

      script.onerror = (error: Event | string) => {
        this.loadingScripts.delete(src)
        const errorEvent = typeof error === 'string' ? new Event(error) : error
        options.onError?.(errorEvent)
        reject(new Error(`Failed to load script: ${src}`))
      }

      // Check if script with same src already exists in DOM
      const existingScript = document.querySelector(`script[src="${src}"]`)
      if (!existingScript) {
        document.head.appendChild(script)
      } else {
        // Script exists, assume it's loaded
        this.loadedScripts.add(src)
        this.loadingScripts.delete(src)
        resolve()
      }
    })

    this.loadingScripts.set(src, loadPromise)
    return loadPromise
  }

  /**
   * Load multiple scripts in sequence
   */
  async loadScripts(scripts: Array<{ src: string; options?: ScriptLoadOptions }>): Promise<void> {
    for (const script of scripts) {
      await this.loadScript(script.src, script.options)
    }
  }

  /**
   * Load multiple scripts in parallel
   */
  async loadScriptsParallel(scripts: Array<{ src: string; options?: ScriptLoadOptions }>): Promise<void> {
    const promises = scripts.map(script => this.loadScript(script.src, script.options))
    await Promise.all(promises)
  }

  /**
   * Check if a script is already loaded
   */
  isScriptLoaded(src: string): boolean {
    return this.loadedScripts.has(src)
  }

  /**
   * Remove a script from the DOM and tracking
   */
  removeScript(src: string): void {
    const script = document.querySelector(`script[src="${src}"]`)
    if (script) {
      script.remove()
    }
    this.loadedScripts.delete(src)
    this.loadingScripts.delete(src)
  }
}

// Export singleton instance
export const scriptLoader = new ScriptLoader()

/**
 * CloudSync specific script loader with PrestaShop integration
 */
export class CloudSyncScriptLoader {
  private static readonly CLOUDSYNC_CDN_URL = 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js'
  private static readonly MBO_CDN_URL = 'https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js'

  /**
   * Load CloudSync dependencies in the correct order
   */
  static async loadCloudSyncDependencies(): Promise<void> {
    try {
      // Load MBO dependencies first if needed
      await scriptLoader.loadScript(this.MBO_CDN_URL, {
        id: 'mbo-cdc-dependencies',
        onLoad: () => {
          console.log('MBO CDC dependencies loaded')
        }
      })

      // Then load CloudSync
      await scriptLoader.loadScript(this.CLOUDSYNC_CDN_URL, {
        id: 'cloudsync-cdc',
        onLoad: () => {
          console.log('CloudSync CDC loaded')
        }
      })

      // Wait for cloudSyncSharingConsent to be available
      await this.waitForCloudSync()
      
    } catch (error) {
      console.error('Failed to load CloudSync dependencies:', error)
      throw error
    }
  }

  /**
   * Wait for cloudSyncSharingConsent to be available on window
   */
  private static waitForCloudSync(timeout = 10000): Promise<void> {
    return new Promise((resolve, reject) => {
      const w = window as any
      if (w.cloudSyncSharingConsent) {
        resolve()
        return
      }

      let attempts = 0
      const maxAttempts = timeout / 100

      const check = () => {
        if (w.cloudSyncSharingConsent) {
          resolve()
        } else if (attempts >= maxAttempts) {
          reject(new Error('CloudSync script loaded but cloudSyncSharingConsent not available'))
        } else {
          attempts++
          setTimeout(check, 100)
        }
      }

      check()
    })
  }

  /**
   * Check if CloudSync is ready for use
   */
  static isCloudSyncReady(): boolean {
    const w = window as any
    return typeof w.cloudSyncSharingConsent === 'object' && 
           typeof w.cloudSyncSharingConsent.init === 'function'
  }
}