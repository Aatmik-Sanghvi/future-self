import { toast } from 'vue3-toastify'

/**
 * SecurityShield: Front-end deterrence against inspecting, devtools tampering,
 * right-click inspection, and network snooping.
 */

let isDevToolsOpen = false
let lockdownElement = null
let debugInterval = null
let lastToastTime = 0
const TOAST_COOLDOWN_MS = 2500

export function isDevToolsActive() {
  return isDevToolsOpen
}

/**
 * Trigger the full-screen lockdown modal when DevTools is opened.
 * Completely blocks interaction, hides page content, and freezes UI.
 */
function activateLockdown() {
  if (lockdownElement) return

  lockdownElement = document.createElement('div')
  lockdownElement.id = 'futureself-devtools-lockdown'
  lockdownElement.style.cssText = `
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: radial-gradient(circle at center, #1b1335 0%, #0a0715 100%) !important;
    z-index: 2147483647 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    padding: 24px !important;
    box-sizing: border-box !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    color: #ffffff !important;
    user-select: none !important;
  `

  lockdownElement.innerHTML = `
    <div style="max-width: 540px; background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(244, 63, 94, 0.35); border-radius: 20px; padding: 40px 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 40px rgba(244, 63, 94, 0.15); backdrop-filter: blur(12px);">
      <div style="font-size: 56px; margin-bottom: 16px; animation: pulse 2s infinite;">🛡️</div>
      <h2 style="font-size: 24px; font-weight: 800; color: #f43f5e; margin: 0 0 12px 0; letter-spacing: -0.5px;">
        Developer Tools Restricted
      </h2>
      <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1; margin: 0 0 20px 0;">
        Our Future Self traveled back from <strong>2035</strong> to let us know you opened DevTools! 😉
      </p>
      <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; text-align: left;">
        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 6px;">🔒 <strong>Active Defense Measures:</strong></div>
        <ul style="margin: 0; padding-left: 20px; font-size: 12.5px; color: #cbd5e1; line-height: 1.6;">
          <li>All Network traffic & API requests are suspended</li>
          <li>DOM tree & element inspector access are shielded</li>
          <li>Session tokens and state are protected in memory</li>
        </ul>
      </div>
      <p style="font-size: 13.5px; color: #a5b4fc; margin: 0 0 24px 0; font-style: italic;">
        "The best way to predict the future is to create it, not inspect element."
      </p>
      <div style="display: inline-block; background: linear-gradient(135deg, #6366f1, #a855f7); color: white; padding: 10px 22px; border-radius: 9999px; font-size: 13px; font-weight: 600; letter-spacing: 0.3px;">
        Close Developer Tools to Resume
      </div>
    </div>
  `

  document.documentElement.appendChild(lockdownElement)

  // Start aggressive debugger trap while DevTools remains open
  if (!debugInterval) {
    debugInterval = setInterval(() => {
      if (isDevToolsOpen) {
        ;(function () {}.constructor('debugger')())
      }
    }, 150)
  }
}

/**
 * Remove lockdown overlay when DevTools is closed.
 */
function deactivateLockdown() {
  if (lockdownElement && lockdownElement.parentNode) {
    lockdownElement.parentNode.removeChild(lockdownElement)
    lockdownElement = null
  }
  if (debugInterval) {
    clearInterval(debugInterval)
    debugInterval = null
  }
}

/**
 * Display toast notification when right-click or shortcut is prevented.
 */
function triggerFunnyWarning(customMsg) {
  const now = Date.now()
  if (now - lastToastTime < TOAST_COOLDOWN_MS) return
  lastToastTime = now

  try {
    toast(customMsg, {
      theme: 'dark',
      type: 'warning',
      autoClose: 3000,
      position: 'bottom-center',
      transition: 'slide',
    })
  } catch {
    console.warn(customMsg)
  }
}

/**
 * Hook browser network APIs (Fetch & XMLHttpRequest) so that
 * NO network requests can ever be initiated while DevTools is open.
 */
function hookNetworkApis() {
  if (typeof window === 'undefined') return

  // 1. Hook window.fetch
  const originalFetch = window.fetch
  window.fetch = function (...args) {
    if (isDevToolsOpen) {
      console.warn('Network request blocked by SecurityShield: Developer Tools active.')
      return Promise.reject(new Error('Network request blocked: Developer Tools are restricted.'))
    }
    return originalFetch.apply(this, args)
  }

  // 2. Hook XMLHttpRequest.prototype.send
  if (window.XMLHttpRequest) {
    const originalSend = window.XMLHttpRequest.prototype.send
    window.XMLHttpRequest.prototype.send = function (...args) {
      if (isDevToolsOpen) {
        console.warn('XHR blocked by SecurityShield: Developer Tools active.')
        try {
          this.abort()
        } catch {
          // ignore
        }
        return
      }
      return originalSend.apply(this, args)
    }
  }
}

/**
 * Continuous detection of Developer Tools (docked, undocked, or floating).
 */
function setupDevToolsDetector() {
  // Method A: Timing benchmark with debugger
  function detectByTiming() {
    const startTime = performance.now()
    ;(function () {}.constructor('debugger')())
    const endTime = performance.now()
    return endTime - startTime > 100
  }

  // Method B: Window dimensions benchmark (docked DevTools)
  function detectByDimensions() {
    const threshold = 160
    const widthDiff = window.outerWidth - window.innerWidth > threshold
    const heightDiff = window.outerHeight - window.innerHeight > threshold
    return widthDiff || heightDiff
  }

  function runCheck() {
    const timingOpen = detectByTiming()
    const dimensionsOpen = detectByDimensions()

    const detected = timingOpen || dimensionsOpen

    if (detected) {
      if (!isDevToolsOpen) {
        isDevToolsOpen = true
        activateLockdown()
      }
    } else {
      if (isDevToolsOpen) {
        isDevToolsOpen = false
        deactivateLockdown()
      }
    }
  }

  // Run periodic inspection checks
  setInterval(runCheck, 800)
  window.addEventListener('resize', runCheck)
}

/**
 * Initialize all security deterrents.
 */
export function initSecurityShield() {
  if (typeof window === 'undefined') return

  // 1. Block Network APIs when DevTools is active
  hookNetworkApis()

  // 2. Disable Right Click Context Menu
  window.addEventListener(
    'contextmenu',
    (e) => {
      const target = e.target
      if (
        target &&
        (target.tagName === 'INPUT' ||
          target.tagName === 'TEXTAREA' ||
          target.isContentEditable)
      ) {
        return
      }

      e.preventDefault()
      triggerFunnyWarning('🕵️ Inspect menu is closed! Our future self warned us you would try that! 😉')
    },
    { capture: true }
  )

  // 3. Block DevTools & Source View Keyboard Shortcuts
  window.addEventListener(
    'keydown',
    (e) => {
      const isMac = navigator.platform && navigator.platform.toUpperCase().indexOf('MAC') >= 0
      const cmdOrCtrl = isMac ? e.metaKey : e.ctrlKey

      const isF12 = e.key === 'F12' || e.keyCode === 123
      const isInspect = cmdOrCtrl && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.keyCode === 73)
      const isConsole = cmdOrCtrl && e.shiftKey && (e.key === 'J' || e.key === 'j' || e.keyCode === 74)
      const isElementPicker = cmdOrCtrl && e.shiftKey && (e.key === 'C' || e.key === 'c' || e.keyCode === 67)
      const isViewSource = cmdOrCtrl && (e.key === 'U' || e.key === 'u' || e.keyCode === 85)
      const isSavePage = cmdOrCtrl && (e.key === 'S' || e.key === 's' || e.keyCode === 83)

      if (isF12 || isInspect || isConsole || isElementPicker || isViewSource || isSavePage) {
        e.preventDefault()
        e.stopPropagation()
        triggerFunnyWarning('🚀 Shortcut blocked! All dev doors are secured. Go focus on your future goals! 🌟')
        return false
      }
    },
    { capture: true }
  )

  // 4. Print console Easter Egg banner
  try {
    console.log(
      '%c🛑 STOP RIGHT THERE, TIME TRAVELER! 🛑',
      'color: #f43f5e; font-size: 26px; font-weight: 900; text-shadow: 2px 2px 0 #000; padding: 10px 0;'
    )
    console.log(
      '%c╔════════════════════════════════════════════════════════════════════════════════════╗\n' +
      '║  Looking for secrets? Our Future Self already traveled back from 2035 to patch it! ║\n' +
      '║                                                                                    ║\n' +
      '║  "The best way to predict the future is to create it, not inspect element."        ║\n' +
      '║                                                                                    ║\n' +
      '║  🛡️ All API requests are strictly authenticated and cryptographically secured.     ║\n' +
      '║  💼 Passionate about cybersecurity & AI? Join us at support@mail.futureself.in     ║\n' +
      '╚════════════════════════════════════════════════════════════════════════════════════╝',
      'color: #38bdf8; font-size: 13px; font-family: monospace; line-height: 1.5;'
    )
  } catch {
    // ignore
  }

  // 5. Setup detection and lockdown
  setupDevToolsDetector()
}
