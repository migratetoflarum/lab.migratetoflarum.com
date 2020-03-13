import m from 'mithril';
import ExtensionReport from './ExtensionReport';
import secretExtensions from "../helpers/secretExtensions";

function isCoreExtension(extension) {
    return extension.attributes.package.indexOf('flarum/') === 0;
}

export default {
    oninit(vnode) {
        vnode.state.showCoreExtensions = false;
    },
    view(vnode) {
        const scan = vnode.attrs.scan;
        const extensions = scan.relationships.extensions.data;

        const coreExtensionsCount = extensions.filter(isCoreExtension).length;

        const extensionsShown = vnode.state.showCoreExtensions ? extensions : extensions.filter(extension => !isCoreExtension(extension));

        secretExtensions(scan, extensionsShown);

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
                m('p.text-muted', 'The following extensions are active on this forum. Some admin-only or background-only extensions might not be visible to the scanner.'),
                m('.list-group.list-group-flush', extensionsShown.map(
                    extension => m(ExtensionReport, {
                        extension,
                    })
                )),
                (extensionsShown.length === 0 && !vnode.state.showCoreExtensions ? [
                    m('p.text-center.text-muted.py-4', 'No third-party extension installed on this forum'),
                ] : null),
            ]),
        ]);
    },
}
