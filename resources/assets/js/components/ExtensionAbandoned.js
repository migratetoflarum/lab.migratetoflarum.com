import m from 'mithril';

export default {
    view(vnode) {
        const extension = vnode.attrs.extension;

        if (!extension.attributes.abandoned) {
            return null;
        }

        return m('.alert.alert-warning', [
            m('p', 'This extension is abandonned'),
            (extension.attributes.abandoned !== '1' ? m('p', 'Note from Packagist: ' + extension.attributes.abandoned) : null),
        ]);
    },
}
