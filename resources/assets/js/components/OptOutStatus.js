import m from 'mithril';

export default {
    view(vnode) {
        const {status} = vnode.attrs;

        const normalizedUrl = status.website ? status.website.attributes.normalized_url : status.check.attributes.normalized_url;

        const optedIn = status.check && status.check.attributes.checked_at ? status.check.attributes.ignore === false : (status.website && status.website.attributes.ignore === false);
        const optedOut = status.check && status.check.attributes.checked_at ? status.check.attributes.ignore === true : (status.website && status.website.attributes.ignore === true);

        const checkInProgress = status.check && !status.check.attributes.checked_at;
        const justChecked = status.check && status.check.attributes.checked_at;

        return m('.card', m('.card-body', [
            m('h3', 'Opt out status for ' + normalizedUrl),
            justChecked ? m('p', 'Checked just now:') : null,
            m('p', optedOut ? 'Opted out - this website will never appear in public results or in the showcase' : (optedIn ? 'Opted in - this website can appear in public results and showcase' : 'Never checked - a check should be running as we speak')),
            m('p', m('button[type=button].btn.btn-primary', {
                onclick(event) {
                    event.preventDefault();

                    vnode.attrs.checkNow();
                },
                disabled: checkInProgress,
            }, checkInProgress ? 'Check in progress...' : (justChecked ? 'Check again (you need to wait a few minutes between checks)' : 'Check now'))),
        ]));
    },
}
