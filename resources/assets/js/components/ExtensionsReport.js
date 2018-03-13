import m from 'mithril';
import ExtensionReport from './ExtensionReport';

function isCoreExtension(extension) {
    return extension.attributes.package.indexOf('flarum/') === 0;
}

export default {
    oninit(vnode) {
        vnode.state.showCoreExtensions = false;
        vnode.state.showModules = false;
    },
    view(vnode) {
        const scan = vnode.attrs.scan;
        const extensions = scan.relationships.extensions.data;
        const modules = scan.attributes.report && scan.attributes.report.homepage && scan.attributes.report.homepage.modules || [];

        const coreExtensionsCount = extensions.filter(isCoreExtension).length;

        const extensionsShown = vnode.state.showCoreExtensions ? extensions : extensions.filter(extension => !isCoreExtension(extension));

        if (!modules.length) {
            return m('.card.mt-3', [
                m('.card-body', [
                    m('h2.card-title', [
                        'Extensions',
                    ]),
                    m('.alert.alert-warning', [
                        m('p', 'Could not detect any loaded module/extension. Is this really a Flarum install ?'),
                    ]),
                ]),
            ]);
        }

        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', [
                    'Extensions',
                    m('.btn.btn-sm.btn-light.float-right', {
                        onclick() {
                            vnode.state.showCoreExtensions = !vnode.state.showCoreExtensions;
                        },
                    }, (vnode.state.showCoreExtensions ? 'Hide' : 'Show') + ' ' + coreExtensionsCount + ' core extensions'),
                ]),
                m('p.text-muted', 'The following extensions are active on this forum. Admin-only or background-only extensions are not listed.'),
                m('.list-group.list-group-flush', extensionsShown.map(
                    extension => m(ExtensionReport, {
                        extension,
                    })
                )),
                (extensionsShown.length === 0 && !vnode.state.showCoreExtensions ? [
                    m('p.text-center.text-muted.py-4', 'No third-party extension installed on this forum'),
                ] : null),
                (vnode.state.showModules ? [
                    m('.btn.btn-sm.btn-block.btn-light.mt-2', {
                        onclick() {
                            vnode.state.showModules = false;
                        },
                    }, 'Hide loaded modules'),
                    m('p', 'The following javascript modules are loaded on the forum:'),
                    m('ul', modules.map(
                        module => m('li', m('code', module))
                    )),
                ] : m('.btn.btn-sm.btn-block.btn-light.mt-2', {
                    onclick() {
                        vnode.state.showModules = true;
                    },
                }, 'Show ' + modules.length + ' loaded modules')),
            ]),
        ]);
    },
}
