<template>
  <div class="offline-container">
    <!-- Online/Offline Status Indicator -->
    <transition name="slide">
      <div 
        v-if="offlineStore.isOffline" 
        class="offline-banner"
      >
        <div class="offline-content">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M1 9l4 4c1.094-1.094 2.609-1.766 4.25-1.766s3.156.672 4.25 1.766l4-4M1 17l4 4c1.094-1.094 2.609-1.766 4.25-1.766s3.156.672 4.25 1.766l4-4"/>
            <line x1="2" y1="2" x2="22" y2="22" stroke="currentColor" stroke-width="2"/>
          </svg>
          <span class="text">Offline Mode - Data will sync when online</span>
        </div>
        <button 
          v-if="offlineStore.hasPendingRequests"
          @click="retrySync"
          class="retry-btn"
          :disabled="!offlineStore.canSync"
        >
          {{ offlineStore.syncInProgress ? 'Syncing...' : 'Retry Sync' }}
        </button>
      </div>

      <!-- Online Status -->
      <div 
        v-else
        class="online-banner"
      >
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M1 9l4 4c1.094-1.094 2.609-1.766 4.25-1.766s3.156.672 4.25 1.766l4-4M1 17l4 4c1.094-1.094 2.609-1.766 4.25-1.766s3.156.672 4.25 1.766l4-4"/>
        </svg>
        <span class="text">Online</span>
      </div>
    </transition>

    <!-- Pending Requests Queue -->
    <div v-if="offlineStore.hasPendingRequests" class="pending-queue">
      <div class="queue-header">
        <span class="queue-title">Pending Requests ({{ offlineStore.pendingRequests.length }})</span>
        <button @click="showQueue = !showQueue" class="toggle-btn">
          {{ showQueue ? '▼' : '▶' }}
        </button>
      </div>
      
      <transition name="expand">
        <div v-if="showQueue" class="queue-items">
          <div 
            v-for="request in offlineStore.pendingRequests"
            :key="request.id"
            class="queue-item"
            :class="`status-${request.status}`"
          >
            <div class="item-content">
              <span class="item-type">{{ request.type }}</span>
              <span class="item-status">{{ request.status }}</span>
              <span class="item-time">{{ formatTime(request.timestamp) }}</span>
            </div>
            <button 
              v-if="request.status === 'failed'"
              @click="retryRequest(request.id)"
              class="retry-item-btn"
            >
              Retry
            </button>
          </div>
        </div>
      </transition>
    </div>

    <!-- Last Sync Time -->
    <div v-if="offlineStore.lastSyncTime" class="sync-info">
      Last sync: {{ formatTime(offlineStore.lastSyncTime) }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useOfflineStore } from '@/stores/offlineStore'

const offlineStore = useOfflineStore()
const showQueue = ref(false)

const formatTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  return date.toLocaleTimeString()
}

const retrySync = async () => {
  await offlineStore.syncPendingRequests()
}

const retryRequest = async (requestId) => {
  offlineStore.updatePendingRequestStatus(requestId, 'pending')
  await offlineStore.syncPendingRequests()
}
</script>

<style scoped>
.offline-container {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

/* Banners */
.offline-banner,
.online-banner {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.offline-banner {
  background: #fee2e2;
  color: #991b1b;
  border-top: 2px solid #dc2626;
}

.online-banner {
  background: #dcfce7;
  color: #166534;
  border-top: 2px solid #22c55e;
}

.offline-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.text {
  margin: 0;
}

.retry-btn {
  padding: 6px 12px;
  background: white;
  border: 1px solid #dc2626;
  color: #dc2626;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  transition: all 0.2s;
}

.retry-btn:hover:not(:disabled) {
  background: #dc2626;
  color: white;
}

.retry-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Pending Queue */
.pending-queue {
  background: #fef3c7;
  border-top: 1px solid #fcd34d;
  padding: 12px 16px;
  margin-top: 2px;
}

.queue-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #b45309;
  font-size: 13px;
  font-weight: 600;
}

.queue-title {
  margin: 0;
}

.toggle-btn {
  background: none;
  border: none;
  color: #b45309;
  cursor: pointer;
  font-size: 12px;
}

.queue-items {
  margin-top: 8px;
  max-height: 200px;
  overflow-y: auto;
}

.queue-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px;
  background: white;
  border-radius: 3px;
  margin-bottom: 6px;
  font-size: 12px;
}

.queue-item.status-syncing {
  opacity: 0.7;
}

.queue-item.status-failed {
  background: #fee2e2;
  border: 1px solid #fca5a5;
}

.item-content {
  display: flex;
  gap: 8px;
  flex: 1;
}

.item-type {
  font-weight: 600;
  color: #374151;
  min-width: 60px;
}

.item-status {
  color: #6b7280;
  text-transform: capitalize;
  min-width: 70px;
}

.item-time {
  color: #9ca3af;
  font-size: 11px;
}

.retry-item-btn {
  padding: 4px 8px;
  background: #fca5a5;
  border: none;
  color: #991b1b;
  border-radius: 3px;
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  transition: all 0.2s;
}

.retry-item-btn:hover {
  background: #dc2626;
  color: white;
}

/* Sync Info */
.sync-info {
  padding: 6px 16px;
  background: #f3f4f6;
  color: #6b7280;
  font-size: 11px;
  text-align: right;
  border-top: 1px solid #e5e7eb;
}

/* Animations */
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

.expand-enter-active,
.expand-leave-active {
  transition: max-height 0.3s ease, opacity 0.3s ease;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
}

@media (max-width: 640px) {
  .offline-banner,
  .online-banner {
    flex-direction: column;
    align-items: flex-start;
  }

  .retry-btn {
    align-self: flex-end;
  }

  .item-content {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
