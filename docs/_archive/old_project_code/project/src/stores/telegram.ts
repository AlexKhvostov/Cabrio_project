import { defineStore } from 'pinia'
import { ref, computed, markRaw } from 'vue'

declare global {
  interface Window {
    Telegram: {
      WebApp: {
        initData: string
        initDataUnsafe: any
        version: string
        platform: string
        colorScheme: string
        themeParams: any
        isExpanded: boolean
        viewportHeight: number
        viewportStableHeight: number
        headerColor: string
        backgroundColor: string
        isClosingConfirmationEnabled: boolean
        BackButton: any
        MainButton: any
        HapticFeedback: any
        ready: () => void
        expand: () => void
        close: () => void
        setHeaderColor: (color: string) => void
        setBackgroundColor: (color: string) => void
        enableClosingConfirmation: () => void
        disableClosingConfirmation: () => void
        onEvent: (eventType: string, eventHandler: () => void) => void
        offEvent: (eventType: string, eventHandler: () => void) => void
        sendData: (data: string) => void
        openLink: (url: string) => void
        openTelegramLink: (url: string) => void
        openInvoice: (url: string, callback?: (status: string) => void) => void
        showPopup: (params: any, callback?: (buttonId: string) => void) => void
        showAlert: (message: string, callback?: () => void) => void
        showConfirm: (message: string, callback?: (confirmed: boolean) => void) => void
        showScanQrPopup: (params: any, callback?: (text: string) => void) => void
        closeScanQrPopup: () => void
        readTextFromClipboard: (callback?: (text: string) => void) => void
        requestWriteAccess: (callback?: (granted: boolean) => void) => void
        requestContact: (callback?: (granted: boolean) => void) => void
      }
    }
  }
}

export const useTelegramStore = defineStore('telegram', () => {
  const isInitialized = ref(false)
  const user = ref<any>(null)
  const webApp = ref<any>(null)

  const isAuthenticated = computed(() => {
    return isInitialized.value && user.value !== null
  })

  const initTelegram = () => {
    if (typeof window !== 'undefined' && window.Telegram?.WebApp) {
      webApp.value = markRaw(window.Telegram.WebApp)
      
      // Initialize WebApp
      webApp.value.ready()
      webApp.value.expand()
      
      // Set theme
      webApp.value.setHeaderColor('#1a1a1a')
      webApp.value.setBackgroundColor('#1a1a1a')
      
      // Get user data
      if (webApp.value.initDataUnsafe?.user) {
        user.value = webApp.value.initDataUnsafe.user
      } else {
        // Mock user for development
        user.value = {
          id: 287536885,
          first_name: 'Александр',
          last_name: 'Петров',
          username: 'alex_petrov',
          language_code: 'ru'
        }
      }
      
      isInitialized.value = true
    } else {
      // Fallback for development
      setTimeout(() => {
        user.value = {
          id: 287536885,
          first_name: 'Александр',
          last_name: 'Петров',
          username: 'alex_petrov',
          language_code: 'ru'
        }
        isInitialized.value = true
      }, 1000)
    }
  }

  const showAlert = (message: string) => {
    if (webApp.value?.showAlert) {
      webApp.value.showAlert(message)
    } else {
      alert(message)
    }
  }

  const showConfirm = (message: string): Promise<boolean> => {
    return new Promise((resolve) => {
      if (webApp.value?.showConfirm) {
        webApp.value.showConfirm(message, resolve)
      } else {
        resolve(confirm(message))
      }
    })
  }

  const hapticFeedback = (type: 'impact' | 'notification' | 'selection' = 'impact') => {
    if (webApp.value?.HapticFeedback) {
      switch (type) {
        case 'impact':
          webApp.value.HapticFeedback.impactOccurred('medium')
          break
        case 'notification':
          webApp.value.HapticFeedback.notificationOccurred('success')
          break
        case 'selection':
          webApp.value.HapticFeedback.selectionChanged()
          break
      }
    }
  }

  return {
    isInitialized,
    user,
    webApp,
    isAuthenticated,
    initTelegram,
    showAlert,
    showConfirm,
    hapticFeedback
  }
})