import Store from './Store';

export default {
    csrfToken: null,
    userId: null,
    flarumServices: 'https://clarkwinkelmann.com/flarum',
    githubRepo: 'https://github.com/migratetoflarum/lab.migratetoflarum.com',
    githubIssues: 'https://github.com/migratetoflarum/lab.migratetoflarum.com/issues',
    discuss: 'https://discuss.flarum.org/d/10056-migratetoflarum-lab-the-health-scanner-for-flarum',
    supportEmail: 'lab@migratetoflarum.com',
    init(root) {
        this.csrfToken = root.dataset.csrf;
        this.userId = root.dataset.user;

        const preload = JSON.parse(root.dataset.preload);
        Store.load(preload);
    },
    user() {
        return Store.get('users', this.userId);
    },
}
