import { ref } from 'vue';

export function useRemita() {
  const loading = ref(false);
  const scriptLoaded = ref(false);

  function loadScript() {
    return new Promise((resolve, reject) => {
      if (scriptLoaded.value || window.RmPaymentEngine) {
        scriptLoaded.value = true;
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = 'https://login.remita.net/payment/v1/remita-pay-inline.bundle.js';
      script.async = true;
      script.onload = () => {
        scriptLoaded.value = true;
        resolve();
      };
      script.onerror = () => reject(new Error('Failed to load Remita script'));
      document.head.appendChild(script);
    });
  }

  async function initiatePayment({ rrr, transactionId, amount, email, firstName, lastName, onSuccess, onError, onClose }) {
    loading.value = true;

    try {
      await loadScript();

      window.RmPaymentEngine.init({
        key: import.meta.env.VITE_REMITA_PUBLIC_KEY,
        processRrr: true,
        transactionId,
        channel: '',
        extendedData: {
          customFields: [
            { name: 'rrr', value: rrr },
          ],
        },
        onSuccess: (response) => {
          loading.value = false;
          onSuccess?.(response);
        },
        onError: (response) => {
          loading.value = false;
          onError?.(response);
        },
        onClose: () => {
          loading.value = false;
          onClose?.();
        },
      });
    } catch (error) {
      loading.value = false;
      onError?.(error);
    }
  }

  return { loading, scriptLoaded, loadScript, initiatePayment };
}
