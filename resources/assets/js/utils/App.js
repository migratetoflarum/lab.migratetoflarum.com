import Store from './Store';

export default {
    csrfToken: null,
    flarumServices: 'https://clarkwinkelmann.com/flarum',
    githubRepo: 'https://github.com/migratetoflarum/lab.migratetoflarum.com',
    githubIssues: 'https://github.com/migratetoflarum/lab.migratetoflarum.com/issues',
    discuss: 'https://discuss.flarum.org/d/10056-migratetoflarum-lab-the-health-scanner-for-flarum',
    supportEmail: 'lab@migratetoflarum.com',
    init(root) {
        this.csrfToken = root.dataset.csrf;

        const preload = JSON.parse(root.dataset.preload);
        Store.load(preload);
    },
}
