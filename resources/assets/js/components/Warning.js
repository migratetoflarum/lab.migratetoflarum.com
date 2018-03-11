import m from 'mithril';

export default {
    oninit(vnode) {
        vnode.state.showSuggestion = false;
        vnode.state.showLog = false;
    },
    view(vnode) {
        return m('.alert.alert-warning', [
            m('p', [
                vnode.attrs.description,
                (vnode.attrs.suggestion ? [' ', m('a[href=javascript:;]', {
                    onclick() {
                        vnode.state.showSuggestion = !vnode.state.showSuggestion;
                    },
                }, 'Show suggestions')] : null),
                (vnode.attrs.log ? [' ', m('a[href=javascript:;].text-muted', {
                    onclick() {
                        vnode.state.showLog = !vnode.state.showLog;
                    },
                }, 'Show log')] : null),
            ]),
            (vnode.state.showSuggestion ? [
                m('h5', 'Suggestion:'),
                m('p', vnode.attrs.suggestion),
            ] : null),
            (vnode.state.showLog ? [
                m('h5', 'Log message from our backend:'),
                m('pre', vnode.attrs.log),
            ] : null),
        ]);
    },
}
