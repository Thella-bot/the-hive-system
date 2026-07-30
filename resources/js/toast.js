import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

let toastInstance = null;

export function initToast(app) {
  app.use(Toast, {
    position: 'top-right',
    timeout: 5000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    maxToasts: 5,
    newestOnTop: true,
  });

  // Capture the toast instance from the plugin's global property
  toastInstance = app.config.globalProperties.$toast;
}

// Toast methods for use outside Vue components (e.g., axios interceptors)
function toast(message, options = {}) {
  if (toastInstance) {
    return toastInstance(message, options);
  }
}

toast.success = (message, options = {}) => {
  if (toastInstance) return toastInstance.success(message, options);
};

toast.error = (message, options = {}) => {
  if (toastInstance) return toastInstance.error(message, options);
};

toast.warning = (message, options = {}) => {
  if (toastInstance) return toastInstance.warning(message, options);
};

toast.info = (message, options = {}) => {
  if (toastInstance) return toastInstance.info(message, options);
};

export { toast };
export default toast;

