import m from "mithril";
import App from "./utils/App";
import ShowcaseLayout from "./layouts/ShowcaseLayout";
import ShowcasePage from "./pages/ShowcasePage";

require('./bootstrap');

let root = document.getElementById('app');

App.init(root);

let isFirstMatch = true;

m.route.prefix(App.showcaseDomain ? '' : '/showcase');
m.route(root, '/', {
    '/': {
        onmatch(args, requestedPath) {
            if (!isFirstMatch && window._paq) {
                window._paq.push(['setCustomUrl', requestedPath]);
                window._paq.push(['trackPageView']);
            }

            isFirstMatch = false;
        },
        render: () => {
            return m(ShowcaseLayout, m(ShowcasePage, {
                key: m.route.get(),
            }));
        },
    },
});
