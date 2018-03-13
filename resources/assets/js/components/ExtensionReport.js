import m from 'mithril';
import icon from '../helpers/icon';

// Matches hex, rgb and rgba colors
const colorRegex = /^(#[0-9a-f]{3,6})|(rgba?\([0-9]{1,3},[0-9]{1,3},[0-9]{1,3}(,[0-9]{1,3})?\))$/i;

// Matches fontawesome names after fa-
const iconNameRegex = /^[a-z0-9-]+$/;

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;
        const iconData = extension.attributes.icon;
        let iconStyle = {};
        let iconName = null;

        if (iconData) {
            if (iconData.color && colorRegex.test(iconData.color)) {
                iconStyle.color = iconData.color;
            }

            if (iconData.backgroundColor && colorRegex.test(iconData.backgroundColor)) {
                iconStyle.backgroundColor = iconData.backgroundColor;
            }

            if (iconData.name && iconNameRegex.test(iconData.name)) {
                iconName = iconData.name;
            }

            if (iconData.image) {
                const iconExt = iconData.image.match(/\.(png|jpg|jpeg|svg)$/);

                if (iconExt) {
                    iconStyle.backgroundImage = 'url(https://flagrow.io/storage/icons/' + extension.attributes.package.replace('/', '$') + '-icon.' + iconExt[1] + ')';
                }
            }
        }

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

        return m('.list-group-item.py-2', m('.row', [
            m('.col-2', m('.extension-icon', {
                style: iconStyle,
            }, iconName ? icon(iconName) : null)),
            m('.col-10', [
                m('h6.mb-0', [
                    extension.attributes.title,
                    ' ',
                    m('small.text-muted', [
                        m('em', extension.attributes.package),
                        (extension.attributes.package.indexOf('flarum/') === 0 ? ' (core extension)' : null),
                    ]),
                ]),
                m('p.my-1', extension.attributes.description),
                m('div', links),
                (extension.attributes.abandoned ? m('.alert.alert-warning', [
                    m('p', 'This extension is abandonned'),
                    (extension.attributes.abandoned !== '1' ? m('p', 'Note from Packagist: ' + extension.attributes.abandoned) : null),
                ]) : null),
            ]),
        ]));
    },
}
