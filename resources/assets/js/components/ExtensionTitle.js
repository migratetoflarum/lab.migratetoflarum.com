import m from 'mithril';
import ExtensionBadges from './ExtensionBadges';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        return [
            extension.attributes.title,
            ' ',
            m('small.text-muted', [
                m('em', extension.attributes.package),
            ]),
            m(ExtensionBadges, {
                extension,
            }),
        ];
    },
}
