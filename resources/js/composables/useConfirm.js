import { ref, reactive } from 'vue';

export function useConfirm() {
  const show = ref(false);
  const resolvePromise = ref(null);

  const options = reactive({
    title: '',
    message: '',
    variant: 'warning',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
  });

  function confirm({ title = 'Confirm Action', message = '', variant = 'warning', confirmText = 'Confirm', cancelText = 'Cancel' } = {}) {
    options.title = title;
    options.message = message;
    options.variant = variant;
    options.confirmText = confirmText;
    options.cancelText = cancelText;
    show.value = true;

    return new Promise((resolve) => {
      resolvePromise.value = resolve;
    });
  }

  function onConfirm() {
    resolvePromise.value?.(true);
    show.value = false;
  }

  function onCancel() {
    resolvePromise.value?.(false);
    show.value = false;
  }

  return { show, options, confirm, onConfirm, onCancel };
}
