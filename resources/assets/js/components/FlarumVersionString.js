import m from 'mithril';

export default {
    view(vnode) {
        let versions = vnode.attrs.versions || [];

        // Backward compatibility
        // Earlier scans only had one version string
        if (vnode.attrs.version) {
            versions.push(vnode.attrs.version);
        }

        let labelVersions = [];
        let titleVersions = [];

        versions.forEach(version => {
            switch (version) {
                case 'dev-master':
                    labelVersions.push('dev-master');
                    titleVersions.push('(development version)');

                    break;
                case '0.1.0-beta.9':
                    labelVersions.push('beta 9');
                    titleVersions.push('0.1.0-beta.9.*');

                    break;
                case '0.1.0-beta.8':
                    labelVersions.push('beta 8');
                    titleVersions.push('0.1.0-beta.8.*');

                    break;
                case '0.1.0-beta.7':
                    labelVersions.push('beta 7');
                    titleVersions.push('0.1.0-beta.7.*');

                    break;
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
