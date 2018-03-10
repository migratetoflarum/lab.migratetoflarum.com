import m from 'mithril';
import icon from '../helpers/icon';

export default {
    view(vnode) {
        return m('.text-muted.text-center.py-5.my-5', [
            icon('spinner', {className: 'fa-pulse fa-2x mb-5'}),
            m('p', vnode.attrs.text),
        ]);
    },
}
