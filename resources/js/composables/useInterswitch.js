import { ref } from 'vue';

export function useInterswitch() {
  const loading = ref(false);
  const scriptLoaded = ref(false);

  function loadScript(mode) {
    return new Promise((resolve, reject) => {
      if (scriptLoaded.value || typeof window.webpayCheckout === 'function') {
        scriptLoaded.value = true;
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = mode === 'TEST'
        ? 'https://newwebpay-sandbox.interswitchng.com/inline-checkout.js'
        : 'https://newwebpay.interswitchng.com/inline-checkout.js';
      script.async = true;
      script.onload = () => {
        scriptLoaded.value = true;
        resolve();
      };
      script.onerror = () => reject(new Error('Failed to load Interswitch script'));
      document.head.appendChild(script);
    });
  }

  async function initiatePayment({ merchantCode, payItemId, payItemName, txnRef, amount, currency, redirectUrl, custName, custEmail, custId, mode, onComplete, onClose }) {
    loading.value = true;
    const payMode = mode || 'LIVE';

    try {
      await loadScript(payMode);

      if (typeof window.webpayCheckout !== 'function') {
        throw new Error('Interswitch checkout not available. Please refresh the page.');
      }

      let completed = false;

      const paymentRequest = {
        merchant_code: merchantCode,
        pay_item_id: payItemId,
        pay_item_name: payItemName || 'Transcript Request Payment',
        txn_ref: txnRef,
        amount: Number(amount),
        currency: Number(currency) || 566,
        site_redirect_url: redirectUrl || window.location.origin + '/',
        cust_name: custName,
        cust_email: custEmail,
        cust_id: custId || txnRef,
        mode: payMode,
        onComplete: (response) => {
          completed = true;
          loading.value = false;
          onComplete?.(response);
        },
      };

      window.webpayCheckout(paymentRequest);

      const observer = new MutationObserver(() => {
        if (!document.getElementById('webpay-checkout-container') && !completed) {
          observer.disconnect();
          loading.value = false;
          onClose?.();
        }
      });
      observer.observe(document.body, { childList: true });
    } catch (err) {
      loading.value = false;
      throw err;
    }
  }

  return { loading, scriptLoaded, loadScript, initiatePayment };
}
