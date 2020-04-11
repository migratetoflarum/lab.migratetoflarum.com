import m from 'mithril';
import ExtensionIcon from './ExtensionIcon';
import ExtensionLinks from './ExtensionLinks';
import ExtensionAbandoned from './ExtensionAbandoned';
import ExtensionTitle from './ExtensionTitle';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        return m('.list-group-item.py-2', m('.row', [
            m('.col-2', m(ExtensionIcon, {
                extension,
            })),
            m('.col-10', [
                m('h6.mb-0', m(ExtensionTitle, {
                    extension,
                })),
                m('p.my-1', extension.attributes.description),
                (Array.isArray(extension.attributes.possible_versions) ? m('p.my-1', {
                    title: 'Possible versions: ' + extension.attributes.possible_versions.join(', '),
                }, [
                    m('small', [
                        'Version ',
                        extension.attributes.possible_versions[0],
                        (extension.attributes.possible_versions.length > 1 ? [
                            ' - ',
                            extension.attributes.possible_versions[extension.attributes.possible_versions.length - 1],
                        ] : null),
                    ]),
                    (extension.attributes.update_available ? [
                        ' ',
                        m('span.badge.badge-dark', 'Update available: ' + extension.attributes.last_version),
                    ] : null),
                ]) : m('p.my-1', m('small', 'Version unknown. Latest available: ' + extension.attributes.last_version))),
                m('div', m(ExtensionLinks, {
                    extension,
                })),
                m(ExtensionAbandoned, {
                    extension,
                }),
            ]),
        ]));
    },
}
