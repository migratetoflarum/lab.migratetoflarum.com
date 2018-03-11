export default function (url) {
    const message = url.exception_message || '';
    const curl = message.match(/cURL error ([0-9]+):/);

    if (curl) {
        // https://curl.haxx.se/libcurl/c/libcurl-errors.html
        switch (parseInt(curl[1])) {
            // Couldn't resolve host. The given remote host was not resolved.
            case 6:
                return {
                    description: 'Could not resolve host',
                    suggest: 'Check the hostname can be publicly resolved. For example use the dig command with a public DNS server like Google: `dig {hostname} @8.8.8.8`. Having a www. record is not strictly required if you are using a subdomain for your forum. It is recommended to have one if you are using the bare domain for your forum',
                };
            // Failed to connect() to host or proxy.
            case 7:
                if (message.indexOf('port 443: Connection refused') !== -1) {
                    return {
                        description: 'Host does not accept HTTPS connections',
                        suggest: 'Enable HTTPS in your server dashboard/configuration',
                    };
                }

                return {
                    description: 'Could not connect to host',
                    suggest: 'Check the ports are open and that your server is accepting http connections',
                };
            // A problem occurred somewhere in the SSL/TLS handshake.
            case 35:
                return {
                    description: 'HTTPS handshake failed',
                    suggest: 'Check that your server accepts HTTPS connections',
                };
            // The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
            case 51:
                if (message.indexOf('SSL: no alternative certificate subject name matches target host name') !== -1) {
                    return {
                        description: 'HTTPS certificate does not match hostname',
                        suggest: 'Check that the domain is listed under the certificate\'s alternative subject names',
                    };
                }

                return {
                    description: 'Invalid HTTPS certificate',
                    suggest: 'Check your HTTPS certificate contains valid data and is not expired',
                };
            // Peer certificate cannot be authenticated with known CA certificates.
            case 60:
                return {
                    description: 'Can\'t validate HTTPS certificate with known CA',
                    suggest: 'Your certificate isn\'t signed by any CA we know. Was it self-signed ?',
                };
        }
    }

    return {
        description: 'An error happened while trying to fetch that url',
        suggest: 'Try fetching the url in a browser to check the issue',
    };
}
