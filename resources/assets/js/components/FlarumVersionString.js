import m from 'mithril';

export default {
    view(vnode) {
        let versions = vnode.attrs.versions || [];

        let labelVersions = [];
        let titleVersions = [];

        versions.forEach(version => {
            if (version === 'dev-master') {
                labelVersions.push('dev-master');
                titleVersions.push('(development version)');
            }

            const matches = /^0\.1\.0-beta\.([0-9]+)$/.exec(version);

            if (matches) {
                labelVersions.push('beta ' + matches[1]);
                titleVersions.push('0.1.0-beta.' + matches[1] + '.*');
            }
        });

        if (!labelVersions.length) {
            labelVersions.push('(version unknown)');
        }

        if (!titleVersions.length) {
            titleVersions.push('Could not detect Flarum version');
        }

        return m('span', {
            title: titleVersions.join(' or '),
        }, labelVersions.join(' or '));
    }
}
