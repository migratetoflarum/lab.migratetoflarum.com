import m from 'mithril';
import getObjectKey from '../helpers/getObjectKey';
import SizeReport from './SizeReport';
import formatBytes from '../helpers/formatBytes';
import icon from '../helpers/icon';
import Warning from './Warning';

function compressionReport(frontend) {
    if (frontend.compressed) {
        let sizeText = '';

        if (frontend.actualCompressedSize) {
            const ratio = frontend.actualCompressedSize / frontend.total;
            const saving = Math.round((1 - ratio) * 100);

            sizeText = ' and you are saving ' + saving + '% on asset size (' + formatBytes(frontend.actualCompressedSize) + ' compressed)';
        }

        return m('.alert.alert-success', [
            icon('fas fa-rocket'),
            ' Your assets are compressed with gzip' + sizeText + '. The report below shows uncompressed sizes',
        ]);
    }

    if (frontend.expectedGzipSize && frontend.total) {
        const ratio = frontend.expectedGzipSize / frontend.total;
        const saving = Math.round((1 - ratio) * 100);

        return m(Warning, {
            description: [
                icon('fas fa-compress-alt'),
                ' You could save ' + saving + '% on asset size (' + formatBytes(frontend.expectedGzipSize) + ' compressed) using gzip!',
            ],
            suggestion: 'It appears you are not serving compressed files. Flarum\'s included Apache and Nginx configuration takes care of enabling them, so make sure these files are used and the corresponding modules are enabled (mod_deflate in Apache).',
        });
    }

    return null;
}

export default {
    oninit(vnode) {
        vnode.state.tab = 'forum';
    },
    view(vnode) {
        const {javascriptSize} = vnode.attrs;

        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', 'Assets size'),
                javascriptSize ? [
                    compressionReport(javascriptSize[vnode.state.tab]),
                    m('ul.nav.nav-tabs', [
                        m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            onclick(event) {
                                event.preventDefault();
                                vnode.state.tab = 'forum';
                            },
                            className: vnode.state.tab === 'forum' ? 'active' : '',
                        }, [
                            'Forum JS ',
                            m('span.badge.badge-light', formatBytes(getObjectKey(javascriptSize, 'forum.total', '?'))),
                        ])),
                        javascriptSize.admin ? m('li.nav-item', m('a.nav-link', {
                            href: '#',
                            onclick(event) {
                                event.preventDefault();
                                vnode.state.tab = 'admin';
                            },
                            className: vnode.state.tab === 'admin' ? 'active' : '',
                        }, [
                            'Admin JS ',
                            m('span.badge.badge-light', formatBytes(getObjectKey(javascriptSize, 'admin.total', '?'))),
                        ])) : null,
                    ]),
                    m(SizeReport, {
                        total: getObjectKey(javascriptSize, vnode.state.tab + '.total', 1),
                        modules: getObjectKey(javascriptSize, vnode.state.tab + '.modules', []),
                    }),
                ] : m('p', 'No assets size report available.'),
            ]),
        ]);
    },
}
