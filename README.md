# MigrateToFlarum Lab, the health scanner for Flarum

[![Build status](https://travis-ci.org/migratetoflarum/lab.migratetoflarum.com.svg?branch=master)](https://travis-ci.org/migratetoflarum/lab.migratetoflarum.com) [![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/migratetoflarum/lab.migratetoflarum.com/blob/master/LICENSE.txt) [![Donate](https://img.shields.io/badge/paypal-donate-yellow.svg)](https://www.paypal.me/clarkwinkelmann)

This is a free and online tool to check the health of your Flarum install.
Visit https://lab.migratetoflarum.com/ to get started.

## Screenshots

For screenshots, [Spatie Browsershot](https://github.com/spatie/browsershot#requirements) is used.
Follow the requirements to install Puppeteer and Chrome Headless.

Additionally on Ubuntu server install the package `fonts-noto-cjk` so Chrome can render websites in almost any language.

## Showcase url

If you run the lab locally, the showcase will be available at `/showcase`.

To use a secondary domain locally or on production, set the env variable `SHOWCASE_DOMAIN` to the absolute url of the showcase homepage without trailing slash.

Then setup the webserver to redirect requests from that domain to the same app as the lab.

### Nginx

(The lab uses nginx in production)

```
proxy_set_header Host lab.migratetoflarum.com;

location ~ ^/(api|css|fonts|images|js|storage) {
  proxy_pass https://127.0.0.1;
}

location / {
  proxy_pass https://127.0.0.1/showcase/;
}
```

### Apache

(I used this locally for tests)

```
<LocationMatch "^/(api|css|fonts|images|js|storage)">
  ProxyPassMatch http://127.0.0.1:8000
</LocationMatch>
ProxyPass / http://127.0.0.1:8000/showcase/
```

## A MigrateToFlarum service

This is a free service by MigrateToFlarum, an online forum migration tool (launching soon).
Follow us on Twitter for updates https://twitter.com/MigrateToFlarum

Need a custom Flarum extension ? [Contact Clark Winkelmann !](https://clarkwinkelmann.com/flarum)

## Links

- [Thread on the Flarum Community](https://discuss.flarum.org/d/10056-migratetoflarum-lab-the-health-scanner-for-flarum)
- [Report an issue](https://github.com/migratetoflarum/lab.migratetoflarum.com/issues)
