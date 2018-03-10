import Store from './Store';

export default {
    csrfToken: null,
    init(root) {
        this.csrfToken = root.dataset.csrf;

        const preload = JSON.parse(root.dataset.preload);
        Store.load(preload);
    },
}
