import { ref } from 'vue';

export function useFlutterwave() {
  const loading = ref(false);

  function loadScript() {
    return new Promise((resolve, reject) => {
      if (window.FlutterwaveCheckout) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://checkout.flutterwave.com/v3.js';
      script.async = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error('Failed to load Flutterwave script'));
      document.head.appendChild(script);
    });
  }

  async function initiatePayment({ amount, email, name, txRef, publicKey, onSuccess, onClose }) {
    loading.value = true;

    try {
      await loadScript();

      window.FlutterwaveCheckout({
        public_key: publicKey || import.meta.env.VITE_FLUTTERWAVE_PUBLIC_KEY,
        tx_ref: txRef,
        amount,
        currency: 'NGN',
        customer: {
          email,
          name,
        },
        customizations: {
          title: "Redeemer's University",
          description: 'Payment',
        },
        callback: (response) => {
          loading.value = false;
          onSuccess?.(response);
        },
        onclose: () => {
          loading.value = false;
          onClose?.();
        },
      });
    } catch (error) {
      loading.value = false;
      throw error;
    }
  }

  return { loading, initiatePayment };
}
