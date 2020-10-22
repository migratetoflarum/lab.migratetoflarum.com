import m from 'mithril';
import TaskReport from './TaskReport';

const tasksMeta = [
    {
        job: 'ScanResolveCanonical',
        title: 'Resolve canonical url',
    },
    {
        job: 'ScanHomePage',
        title: 'Scan homepage',
    },
    {
        job: 'ScanAlternateUrlsAndHeaders',
        title: 'Scan alternate urls and headers',
    },
    {
        job: 'ScanExposedFiles',
        title: 'Look for exposed files',
    },
    {
        job: 'ScanJavascript',
        title: 'Scan javascript files',
    },
    {
        job: 'ScanMapExtensions',
        title: 'Detecting installed extensions',
    },
    {
        job: 'ScanGuessVersion',
        title: 'Determine possible Flarum versions',
    },
    {
        job: 'ScanRate',
        title: 'Computing rating',
    },
    {
        job: 'ScanUpdateDatabase',
        title: 'Prepare report',
    },
];

export default {
    view(vnode) {
        const {
            tasks,
            showLiveLoading,
        } = vnode.attrs;

        return m('.card.mt-3', [
            m('.card-body', [
                m('h2.card-title', [
                    'Background jobs ',
                    m('span.badge.badge-light', tasks.length + '/' + tasksMeta.length),
                ]),
                m('.list-group', tasksMeta.map(
                    taskMeta => m(TaskReport, {
                        taskMeta,
                        task: tasks.find(t => t.attributes.job === taskMeta.job),
                        showLiveLoading,
                    })
                )),
            ]),
        ]);
    },
}
