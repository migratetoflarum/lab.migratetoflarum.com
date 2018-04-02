import m from 'mithril';
import Layout from './pages/Layout';
import HomePage from './pages/HomePage';
import ScanPage from './pages/ScanPage';
import App from './utils/App';
import LoginPage from './pages/LoginPage';
import AccountPage from './pages/AccountPage';
import RegisterPage from './pages/RegisterPage';

let root = document.getElementById('app');

App.init(root);

const routes = {
    '/': HomePage,
    '/scans/:key': ScanPage,
    '/login': LoginPage,
    '/register': RegisterPage,
    '/account': AccountPage,
};

let isFirstMatch = true;

function createResolver(component) {
    return {
        onmatch(args, requestedPath) {
            // On n'effectue pas le tracking de la première url ici
            // Le code analytics directement dans la page s'en charge
            if (!isFirstMatch && window._paq) {
                window._paq.push(['setCustomUrl', requestedPath]);
                window._paq.push(['trackPageView']);
            }

            isFirstMatch = false;
        },
        render: () => {
            return m(Layout, {
                component,
            }, m(component, {
                key: m.route.get(),
            }));
        },
    };
}

let mithrilRoutes = {};

for (let url in routes) {
    if (routes.hasOwnProperty(url)) {
        mithrilRoutes[url] = createResolver(routes[url]);
    }
}

m.route.prefix('');
m.route(root, '/', mithrilRoutes);
