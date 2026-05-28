import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global response interceptor to show a friendly popup for unexpected errors
window.axios.interceptors.response.use(
  response => response,
  error => {
    try {
      let message = 'Something went wrong. Please try again.';

      if (error.response && error.response.data && error.response.data.message) {
        message = error.response.data.message;
      } else if (error.message) {
        // network errors (e.g. timeout) or other axios errors
        message = 'Network error. Please check your connection and try again.';
      }

      // Use a simple popup to avoid exposing technical details
      // Frontend layout will also display server-set flash messages where available
      window.alert(message);
    } catch (e) {
      // swallow any errors from the interceptor to avoid recursive failures
    }

    return Promise.reject(error);
  }
);
