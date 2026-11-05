// What a browser asks the demos, from node: `castor app:smoke` runs it, and
// nothing is deployed if a demo does not answer. PHP runs as it does in the
// page, with the same build. What is not here is what only a browser does: the
// service worker, IndexedDB and the JavaScript of the applications.

import assert from 'node:assert/strict';
import { test } from 'node:test';

const origin = 'https://example.test';

/**
 * What a page asks PHP, with its cookies. The application is under a path, as
 * on GitHub Pages: a redirection that forgets it, or adds it twice, is a 404.
 */
function browser(php, { keepCookies, withCookies }) {
    const cookies = new Map();
    const options = { documentRoot: '/app/public', basePath: '/app' };

    const request = async (path, init = {}) => {
        const url = `${origin}/app${path}`;
        const headers = [...withCookies(init.headers ?? [], cookies), ['Referer', url]];
        const response = await php.request({ url, ...init, headers }, options);
        keepCookies(cookies, response.headers);

        return {
            status: response.status,
            body: new TextDecoder().decode(response.body),
            location: response.headers.find(([name]) => 'location' === name.toLowerCase())?.[1],
        };
    };

    return {
        get: (path) => request(path),
        post: (path, fields) => request(path, {
            method: 'POST',
            headers: [['Content-Type', 'application/x-www-form-urlencoded'], ['Origin', origin]],
            body: new URLSearchParams(fields).toString(),
        }),
    };
}

function csrfToken(html) {
    const input = /<input[^>]*name="_csrf_token"[^>]*>/.exec(html)?.[0] ?? '';
    const token = /value="([^"]*)"/.exec(input)?.[1];
    assert.notEqual(token, undefined, 'The form has no CSRF token.');

    return token;
}

async function assertPages(page, paths) {
    for (const path of paths) {
        const { status } = await page.get(path);
        assert.equal(status, 200, `${path} answers ${status}.`);
    }
}

test('the Symfony Demo answers', async () => {
    const build = await import('../public/symfony-demo/build/php-web.mjs');
    const php = await build.default();
    php.FS.mkdirTree('/symfony-demo');
    // What public/symfony-demo/index.html does on the first visit
    await php.runCode(`copy('/app/data/database.sqlite', '/symfony-demo/database.sqlite');`);
    const page = browser(php, build);

    await assertPages(page, ['/en', '/en/blog/', '/en/blog/search', '/en/blog/rss.xml', '/ja/blog/', '/ar/blog/']);

    const { body } = await page.get('/fr/blog/');
    assert.match(body, /février 2023/, 'The dates are not in French: intl has no data.');

    const login = await page.get('/en/login');
    const redirection = await page.post('/en/login', {
        _csrf_token: csrfToken(login.body),
        _password: 'kitten',
        _username: 'jane_admin',
    });
    assert.equal(redirection.location, `${origin}/app/en/admin/post/`);

    await assertPages(page, ['/en/admin/post/', '/en/admin/post/new']);
});

test('EasyAdmin answers', async () => {
    const build = await import('../public/easyadmin/build/php-web.mjs');
    const php = await build.default();
    php.FS.mkdirTree('/easyadmin');
    // What public/easyadmin/index.html does on the first visit
    for (const args of [['doctrine:schema:update', '--force'], ['doctrine:fixtures:load']]) {
        const { exitCode, stdout } = await php.runFile('/app/bin/console', { args: [...args, '--no-interaction'] });
        assert.equal(exitCode, 0, stdout);
    }
    const page = browser(php, build);

    const login = await page.get('/login');
    const redirection = await page.post('/login', {
        _csrf_token: csrfToken(login.body),
        _password: 'admin',
        _username: 'admin',
    });
    assert.equal(redirection.location, `${origin}/app/admin`);

    await assertPages(page, [
        '/admin',
        '/admin/fr',
        '/admin/en/album',
        '/admin/en/album/1',
        '/admin/en/album/1/edit',
        '/admin/en/album/new',
        '/admin/en/artist',
        '/admin/en/customer',
        '/admin/en/genre',
        '/admin/en/order',
        '/admin/en/order/1',
        '/admin/en/order/new',
        '/admin/en/review',
    ]);

    const { body } = await page.get('/admin/fr');
    assert.match(body, /Tableau de bord/);
});
