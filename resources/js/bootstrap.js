import axios from 'axios';
import { toast } from './toast';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

window.axios.interceptors.response.use(
  response => response,
  error => {
    try {
      let message = 'Something went wrong. Please try again.';

      if (error.response && error.response.data && error.response.data.message) {
        message = error.response.data.message;
      } else if (error.message) {
        message = 'Network error. Please check your connection and try again.';
      }

      toast.error(message);
    } catch (e) {
      // swallow any errors from the interceptor to avoid recursive failures
    }

    return Promise.reject(error);
  }
);

