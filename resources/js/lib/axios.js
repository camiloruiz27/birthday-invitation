import Axios from 'axios';

const axios = Axios.create();

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && error.response.data?.redirect) {
            window.location.href = error.response.data.redirect;
        }

        return Promise.reject(error);
    }
);

export default axios;
