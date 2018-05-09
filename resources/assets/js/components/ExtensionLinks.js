import m from 'mithril';
import icon from '../helpers/icon';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        let links = [
            m('a.btn.btn-sm.btn-light', {
                href: 'https://packagist.org/packages/' + extension.attributes.package,
                target: '_blank',
                rel: 'nofollow',
            }, [icon('download'), ' Packagist']),
            ' ',
            m('a.btn.btn-sm.btn-light', {
                href: 'https://flagrow.io/packages/' + extension.attributes.package,
                target: '_blank',
                rel: 'nofollow',
            }, [icon('globe'), ' Flagrow.io']),
        ];

        if (extension.attributes.repository) {
            links.push(' ');
            links.push(m('a.btn.btn-sm.btn-light', {
                href: extension.attributes.repository,
                target: '_blank',
                rel: 'nofollow',
            }, extension.attributes.repository.indexOf('https://github.com') === 0 ?
                [icon('github'), ' GitHub'] :
                [icon('code-fork'), ' Repository']));
        }

        if (extension.attributes.discuss_url) {
            links.push(' ');
            links.push(m('a.btn.btn-sm.btn-light', {
                href: extension.attributes.discuss_url,
                target: '_blank',
                rel: 'nofollow',
            }, [m('span.fa.icon-flarum'), ' Discuss']));
        }

        return links;
    },
}
