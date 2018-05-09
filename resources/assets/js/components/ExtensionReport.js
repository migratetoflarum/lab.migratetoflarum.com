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
                (extension.relationships && extension.relationships.possible_versions && extension.relationships.possible_versions.data.length ? m('p.my-1', {
                    title: 'Possible versions: ' + extension.relationships.possible_versions.data.map(version => version.attributes.version).join(', '),
                }, [
                    m('small', [
                        'Version ',
                        extension.relationships.possible_versions.data[0].attributes.version,
                        (extension.relationships.possible_versions.data.length > 1 ? [
                            ' - ',
                            extension.relationships.possible_versions.data[extension.relationships.possible_versions.data.length - 1].attributes.version,
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
