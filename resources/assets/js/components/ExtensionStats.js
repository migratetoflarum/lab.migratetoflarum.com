import m from 'mithril';
import ExtensionIcon from './ExtensionIcon';
import ExtensionLinks from './ExtensionLinks';
import ExtensionAbandoned from './ExtensionAbandoned';
import ExtensionTitle from './ExtensionTitle';
import ExtensionVersionLocales from './ExtensionVersionLocales';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        return m('.list-group-item.py-2', m('.row', [
            m('.col-3', [
                m(ExtensionIcon, {
                    extension,
                }),
                m('h4.mt-3', m(ExtensionTitle, {
                    extension,
                })),
                m('p.my-1', extension.attributes.description),
                m('div', m(ExtensionLinks, {
                    extension,
                })),
                m(ExtensionAbandoned, {
                    extension,
                }),
            ]),
            m('.col-9', [
                m(ExtensionVersionLocales, {
                    extension,
                    version: extension.relationships.last_version.data,
                }),
            ]),
        ]));
    },
}
