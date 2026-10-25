import api from '@/api/axios'

class PaymentService {
  /**
   * Dynamically loads the Razorpay checkout script if not already present.
   */
  loadRazorpayScript() {
    return new Promise((resolve, reject) => {
      if (typeof window !== 'undefined' && window.Razorpay) {
        return resolve(true)
      }

      const existingScript = document.getElementById('razorpay-checkout-js')
      if (existingScript) {
        existingScript.onload = () => resolve(true)
        existingScript.onerror = () => reject(new Error('Failed to load Razorpay SDK'))
        return
      }

      const script = document.createElement('script')
      script.id = 'razorpay-checkout-js'
      script.src = 'https://checkout.razorpay.com/v1/checkout.js'
      script.async = true
      script.onload = () => resolve(true)
      script.onerror = () => reject(new Error('Failed to load Razorpay SDK'))
      document.body.appendChild(script)
    })
  }

  /**
   * Create a new payment order for the specified plan.
   *
   * @param {string} plan Default: 'pro_monthly'
   */
  createOrder(plan = 'pro_monthly') {
    return api.post('/payments/create-order', { plan })
  }

  /**
   * Verify the payment signature with the backend.
   *
   * @param {Object} paymentData
   * @param {string} paymentData.razorpay_order_id
   * @param {string} paymentData.razorpay_payment_id
   * @param {string} paymentData.razorpay_signature
   */
  verifyPayment(paymentData) {
    return api.post('/payments/verify', paymentData)
  }

  /**
   * Create a Razorpay recurring subscription (with 7-day trial if eligible).
   */
  createSubscription() {
    return api.post('/payments/create-subscription')
  }

  /**
   * Verify subscription authorization signature with backend.
   *
   * @param {Object} data
   * @param {string} data.razorpay_subscription_id
   * @param {string} data.razorpay_payment_id
   * @param {string} data.razorpay_signature
   */
  verifySubscription(data) {
    return api.post('/payments/verify-subscription', data)
  }

  /**
   * Activate the 7-day free trial.
   */
  startTrial() {
    return api.post('/payments/start-trial')
  }

  /**
   * Cancel the active Pro subscription or 7-day trial.
   */
  cancelSubscription() {
    return api.post('/payments/cancel')
  }

  /**
   * Fetch current subscription & trial status.
   */
  getSubscriptionStatus() {
    return api.get('/payments/status')
  }
}

export default new PaymentService()
