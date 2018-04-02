import m from 'mithril';

export default {
    view(vnode) {
        const input = vnode.attrs.input;

        input.className = vnode.attrs.errors.length ? 'is-invalid' : '';

        return m('.form-group.row', [
            m('label.col-sm-4.col-form-label.text-md-right', {
                for: input.id,
            }, vnode.attrs.label),
            m('.col-md-6', [
                m('input.form-control', input),
                vnode.attrs.errors.map(
                    (error, index) => m('.invalid-tooltip.d-block', {
                        onclick() {
                            // Hide errors if you click on them
                            vnode.attrs.errors.splice(index, 1);
                        },
                    }, error)
                ),
            ]),
        ]);
    },
}
