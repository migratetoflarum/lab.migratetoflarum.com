import Store from './Store';

export default {
    csrfToken: null,
    sponsoring: {},
    flarumServices: 'https://clarkwinkelmann.com/flarum',
    githubRepo: 'https://github.com/migratetoflarum/lab.migratetoflarum.com',
    githubIssues: 'https://github.com/migratetoflarum/lab.migratetoflarum.com/issues',
    discuss: 'https://discuss.flarum.org/d/10056-migratetoflarum-lab-the-health-scanner-for-flarum',
    showcaseDiscuss: 'https://discuss.flarum.org/d/19566-builtwithflarum-com-the-ultimate-flarum-showcase',
    supportEmail: 'lab@migratetoflarum.com',
    sponsoringEmail: 'sponsoring@migratetoflarum.com',
    secretExtensionProbability: 0,
    baseDomain: null,
    showcaseDomain: null,
    stats: null,
    init(root) {
        this.csrfToken = root.dataset.csrf;

        if (root.dataset.hasOwnProperty('secretExtensionProbability')) {
            this.secretExtensionProbability = parseInt(root.dataset.secretExtensionProbability);
        }

        if (root.dataset.hasOwnProperty('sponsoring')) {
            this.sponsoring = JSON.parse(root.dataset.sponsoring);
        }

        if (root.dataset.hasOwnProperty('baseDomain')) {
            this.baseDomain = root.dataset.baseDomain || null;
        }

        if (root.dataset.hasOwnProperty('showcaseDomain')) {
            this.showcaseDomain = root.dataset.showcaseDomain || null;
        }

        if (root.dataset.hasOwnProperty('stats')) {
            this.stats = JSON.parse(root.dataset.stats);
        }

        if (root.dataset.hasOwnProperty('preload')) {
            const preload = JSON.parse(root.dataset.preload);
            Store.load(preload);
        }
    },
}
