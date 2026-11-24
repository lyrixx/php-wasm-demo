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

function csrfToken(html, name = '_csrf_token') {
    const input = new RegExp(`<input[^>]*name="${name}"[^>]*>`).exec(html)?.[0] ?? '';
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

test('Pop Quiz answers', async () => {
    const build = await import('../public/pop-quiz/build/php-web.mjs');
    const php = await build.default();
    php.FS.mkdirTree('/pop-quiz');
    // What public/pop-quiz/index.html does on the first visit
    await php.runCode(`touch('/pop-quiz/database.sqlite');`);
    const { exitCode, stdout } = await php.runFile('/app/artisan', { args: ['migrate', '--seed', '--force', '--no-interaction'] });
    assert.equal(exitCode, 0, stdout);
    const page = browser(php, build);
    const path = (location) => new URL(location).pathname.slice('/app'.length);

    await assertPages(page, ['/', '/join', '/quizzes/emoji-cinema', '/quizzes/elephant-facts']);
    // The links of the home: its URL without the slash, that PHP redirects
    assert.match((await page.get('/')).body, new RegExp(`href="${origin}/app"`));
    assert.equal((await page.get('')).location, '/app/');

    const join = await page.get('/join');
    const joined = await page.post('/join', { _token: csrfToken(join.body, '_token'), avatar: '🦄', nickname: 'Smoke' });
    assert.equal(joined.location, `${origin}/app`);
    assert.equal((await page.get('/join')).location, `${origin}/app`, 'A player joins again.');

    const missing = await page.get('/quizzes/nope');
    assert.equal(missing.status, 404);
    assert.match(missing.body, /Poof!/);

    // A whole quiz: the first choice of each question, whatever it is
    const quiz = await page.get('/quizzes/emoji-cinema');
    const started = await page.post('/quizzes/emoji-cinema/attempts', { _token: csrfToken(quiz.body, '_token') });
    assert.match(started.location ?? '', /\/app\/attempts\/\d+$/);
    const attempt = path(started.location);
    for (let i = 1; i <= 8; i++) {
        const question = await page.get(attempt);
        assert.match(question.body, new RegExp(`Question ${i}/8`));
        const choice = /name="choice" value="(\d+)"/.exec(question.body)?.[1];
        const answered = await page.post(`${attempt}/answers`, { _token: csrfToken(question.body, '_token'), choice });
        assert.match(answered.location ?? '', /\/answers\/\d+$/, `The answer to question ${i} is refused.`);
        await assertPages(page, [path(answered.location)]);
    }
    const result = await page.get(attempt);
    assert.match(result.body, /on the leaderboard/);
    const token = csrfToken(result.body, '_token');
    const late = await page.post(`${attempt}/answers`, { _token: token, choice: '1' });
    assert.equal(late.status, 403, 'A finished attempt takes an answer.');
    assert.match(late.body, /Nope!/);
    assert.equal((await page.post('/quizzes/emoji-cinema', { _method: 'DELETE', _token: token })).status, 403, 'A quiz of the house is deleted.');

    // A quiz of their own
    const form = await page.get('/quizzes/create');
    const fields = { _token: csrfToken(form.body, '_token'), color: 'cyan', emoji: '💨', title: 'Smoke test' };
    for (let question = 0; question < 3; question++) {
        fields[`questions[${question}][prompt]`] = `Question ${question}?`;
        fields[`questions[${question}][correct]`] = '0';
        for (let choice = 0; choice < 4; choice++) {
            fields[`questions[${question}][choices][${choice}]`] = `Choice ${choice}`;
        }
    }
    const created = await page.post('/quizzes', fields);
    assert.equal(created.location, `${origin}/app/quizzes/smoke-test`);
    await assertPages(page, ['/quizzes/smoke-test']);

    const deleted = await page.post('/quizzes/smoke-test', { _method: 'DELETE', _token: token });
    assert.equal(deleted.location, `${origin}/app`);
    assert.equal((await page.get('/quizzes/smoke-test')).status, 404);
});
