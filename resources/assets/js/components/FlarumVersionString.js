import m from 'mithril';

export default {
    view(vnode) {
        const version = vnode.attrs.version;

        let title = version ? version : 'Could not detect Flarum version';

        let abbr = '(version unknown)';

        switch(version) {
            case 'dev-master':
                abbr = 'dev-master';

                title += ' (development version)';

                break;
            case '0.1.0-beta.8':
                abbr = 'beta 8';

                break;
            case '0.1.0-beta.7':
                abbr = 'beta 7';

                title += ' or 0.1.0-beta.7.1 or 0.1.0-beta.7.2';

                break;
        }

        return m('span', {
            title,
        }, abbr);
    }
}
