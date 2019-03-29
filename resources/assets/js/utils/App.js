import Store from './Store';

export default {
    csrfToken: null,
    sponsoring: {},
    flarumServices: 'https://clarkwinkelmann.com/flarum',
    githubRepo: 'https://github.com/migratetoflarum/lab.migratetoflarum.com',
    githubIssues: 'https://github.com/migratetoflarum/lab.migratetoflarum.com/issues',
    discuss: 'https://discuss.flarum.org/d/10056-migratetoflarum-lab-the-health-scanner-for-flarum',
    supportEmail: 'lab@migratetoflarum.com',
    sponsoringEmail: 'sponsoring@migratetoflarum.com',
    secretExtensionProbability: 0,
    init(root) {
        this.csrfToken = root.dataset.csrf;
        this.secretExtensionProbability = root.dataset.hasOwnProperty('secretExtensionProbability') ? parseInt(root.dataset.secretExtensionProbability) : 0;
        this.sponsoring = JSON.parse(root.dataset.sponsoring);

        const preload = JSON.parse(root.dataset.preload);
        Store.load(preload);
    },
}
