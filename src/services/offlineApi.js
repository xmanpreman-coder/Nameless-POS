import { useOfflineStore } from '@/stores/offlineStore'

/**
 * Offline-aware API client
 * Automatically queues requests when offline and syncs when online
 */
export class OfflineApi {
  constructor(baseUrl = 'http://localhost:8000') {
    this.baseUrl = baseUrl
    this.offlineStore = useOfflineStore()
  }

  /**
   * Make API request with offline support
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`
    const method = options.method || 'GET'

    // If online, try to make request immediately
    if (this.offlineStore.isOnline) {
      try {
        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers
          },
          body: options.data ? JSON.stringify(options.data) : undefined
        })

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`)
        }

        return await response.json()
      } catch (error) {
        // If offline or request fails, queue for later
        if (!this.offlineStore.isOnline || error.message === 'Failed to fetch') {
          return this.queueRequest(endpoint, options)
        }
        throw error
      }
    } else {
      // Offline - queue request
      return this.queueRequest(endpoint, options)
    }
  }

  /**
   * Queue request for later sync
   */
  queueRequest(endpoint, options = {}) {
    const requestId = this.offlineStore.addPendingRequest({
      url: `${this.baseUrl}${endpoint}`,
      method: options.method || 'POST',
      data: options.data,
      type: options.type || 'unknown'
    })

    return {
      status: 'offline',
      queued: true,
      requestId,
      message: 'Request queued - will sync when online'
    }
  }

  // Convenience methods
  async get(endpoint) {
    return this.request(endpoint, { method: 'GET' })
  }

  async post(endpoint, data) {
    return this.request(endpoint, { 
      method: 'POST', 
      data 
    })
  }

  async put(endpoint, data) {
    return this.request(endpoint, { 
      method: 'PUT', 
      data 
    })
  }

  async delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' })
  }

  /**
   * Download data for offline use
   */
  async downloadForOffline() {
    try {
      const [products, customers, suppliers] = await Promise.all([
        this.get('/api/products?limit=1000'),
        this.get('/api/customers?limit=1000'),
        this.get('/api/suppliers?limit=1000')
      ])

      this.offlineStore.saveOfflineData('products', products.data || [])
      this.offlineStore.saveOfflineData('customers', customers.data || [])
      this.offlineStore.saveOfflineData('suppliers', suppliers.data || [])

      return {
        success: true,
        message: 'Data downloaded for offline use'
      }
    } catch (error) {
      console.error('Failed to download offline data:', error)
      return {
        success: false,
        message: 'Failed to download offline data'
      }
    }
  }
}

export const offlineApi = new OfflineApi()
