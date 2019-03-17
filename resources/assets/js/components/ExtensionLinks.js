import m from 'mithril';
import icon from '../helpers/icon';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        let links = [
            m('a.btn.btn-sm.btn-light', {
                href: 'https://packagist.org/packages/' + extension.attributes.package,
                target: '_blank',
                rel: 'nofollow noopener',
            }, [icon('download'), ' Packagist']),
            ' ',
            m('a.btn.btn-sm.btn-light', {
                href: 'https://flagrow.io/extensions/' + extension.attributes.package,
                target: '_blank',
                rel: 'nofollow noopener',
            }, [m('span.fab.icon-flagrow'), ' Flagrow.io']),
        ];

        if (extension.attributes.repository) {
            links.push(' ');
            links.push(m('a.btn.btn-sm.btn-light', {
                href: extension.attributes.repository,
                target: '_blank',
                rel: 'nofollow noopener',
            }, extension.attributes.repository.indexOf('https://github.com') === 0 ?
                [icon('fab fa-github'), ' GitHub'] :
                [icon('code-branch'), ' Repository']));
        }

        if (extension.attributes.discuss_url) {
            links.push(' ');
            links.push(m('a.btn.btn-sm.btn-light', {
                href: extension.attributes.discuss_url,
                target: '_blank',
                rel: 'nofollow noopener',
            }, [m('span.fab.icon-flarum'), ' Discuss']));
        }

        return links;
    },
}
