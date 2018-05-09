import m from 'mithril';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        let badges = [];

        if (extension.attributes.package.indexOf('flarum/') === 0) {
            badges.push(' ');
            badges.push(m('span.badge.badge-secondary', 'Core extension'));
        }

        if (extension.attributes.language_pack) {
            badges.push(' ');
            badges.push(m('span.badge.badge-secondary', 'Language Pack'));
        }

        return badges;
    },
}
