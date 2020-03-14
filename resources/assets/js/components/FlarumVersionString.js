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

        let label = labelVersions.join(' or ');

        // If all titles start with "beta", use syntax beta 1/2/3 instead
        if (labelVersions.length > 1 && labelVersions.every(t => t.indexOf('beta ') === 0)) {
            label = 'beta ' + labelVersions.map(t => t.replace('beta ', '')).join('/');
        }

        return m('span', {
            title: titleVersions.join(' or '),
        }, label);
    }
}
