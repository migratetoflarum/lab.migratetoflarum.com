import m from 'mithril';

const tools = [
    {
        title: 'SSL Server Test',
        href: 'https://www.ssllabs.com/ssltest/',
    },
    {
        title: 'Mozilla Observatory',
        href: 'https://observatory.mozilla.org/',
    },
    {
        title: 'Security Headers',
        href: 'https://securityheaders.com/',
    }
];

export default {
    view() {
        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', 'Also check out these (not affiliated, but great) tools'),
                m('.list-group', tools.map(
                    tool => m('a.list-group-item.list-group-item-action', {
                        href: tool.href,
                        target: '_blank',
                        rel: 'noopener',
                    }, [
                        m('', tool.title),
                        m('em.text-muted', tool.href),
                    ])
                )),
            ]),
        ]);
    },
}
