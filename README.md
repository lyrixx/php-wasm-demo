# PHP WASM demos

Symfony applications that run in the browser, on PHP compiled to WebAssembly by
[PHP WASM Builder](https://github.com/lyrixx/php-wasm-builder). No server runs
them: GitHub Pages serves files, and everything else happens in the tab.

They are on https://lyrixx.github.io/php-wasm-demo/:

- **[The Symfony Demo](https://lyrixx.github.io/php-wasm-demo/symfony-demo/)**,
  [symfony/demo](https://github.com/symfony/demo) as it is: the blog, its admin,
  the login, the search, AssetMapper, Stimulus, a Live Component, 30 languages.
- **[EasyAdmin](https://lyrixx.github.io/php-wasm-demo/easyadmin/)**, the back
  office of Wax Elephant Records, a record shop that never existed, with
  [EasyAdmin](https://github.com/EasyCorp/EasyAdminBundle).

## Run them

It needs Docker, [castor](https://castor.jolicode.com) and node:

    castor app:build
    castor app:serve

The website is on http://127.0.0.1:8000/php-wasm-demo/, under the path where
GitHub Pages serves it. `castor app:smoke` checks that what is built boots, and
that the demos answer. `.github/workflows/pages.yml` deploys `public/`, on each
push to `main`, once they do.

## Where things are

| File | What it is |
| --- | --- |
| `castor.php` | The tasks, and the stacks: the PHP version, the extensions and what each demo embeds. |
| `apps/` | The Symfony applications. `app:build` installs their `vendor/`, and Composer builds their assets. |
| `public/` | The website. `<demo>/index.html` starts PHP, and shows the application in a frame, with `serveInFrame()`. `<demo>/sw.js` hands what the frame asks for to the page. `<demo>/build/` is what `app:build` makes (not in git). |
| `tests/` | What `app:smoke` asks the demos, from node. |

## How it works

Each page runs PHP in a Web Worker, with `createPhpInWorker()`. A service worker
hands what the frame asks for to the page, that runs it with `php.request()`. The
database and the sessions are in a directory that PHP keeps in IndexedDB: they
stay across reloads. "Reset the data" brings the demo back as it was.

The stacks embed the application, with its `vendor/`, in the `.wasm` file. Symfony
makes its cache when they are built (`warmup()`), and opcache compiles the files
as they run (`Opcache::Runtime`). intl comes with its data, for the dates and the
prices in the language of the page: the 32 MB it takes, and what is embedded,
need 256 MB of memory. Both stacks have the same PHP, that is compiled once.

The `.dockerignore` of each application says what is not embedded, so what the
visitor does not download: what only builds the assets, the tests, the fonts and
the data of `symfony/intl` that the application does not use.

Each application is under a path, as on GitHub Pages. Symfony adds the base path
to a target path that starts with a slash, and `generateUrl()` already has it: the
target of the login is an absolute URL, or the redirection is a 404.

## The Symfony Demo

The accounts are the ones of the demo, the login page fills them in: `jane_admin`
and `john_user`, with the password `kitten`. The first visit copies
`data/database.sqlite` to `/symfony-demo`, that stays.

`apps/symfony-demo/` is the tag v3.1.0 of symfony/demo, without what only
develops it: `.devcontainer/`, `.github/`, `tests/`,
`data/database_test.sqlite`, `.env.test`, and the configuration of PHP-CS-Fixer,
PHPStan and PHPUnit. These lines differ:

| File | What changes |
| --- | --- |
| `.env` | `APP_ENV` is `prod`, `APP_SECRET` comes from `.env.local.demo`, that is gone, and the database is in `/symfony-demo`. |
| `composer.json` | `asset-map:compile` runs with the other scripts, and `.env.local.demo` is not renamed: it would change a file of the repository. |
| `config/packages/wasm.yaml` | A new file: the sessions are in `/symfony-demo` too. |
| `.dockerignore` | A new file: what is not embedded. |
| `src/Controller/SecurityController.php` | The target of the login is an absolute URL. |

To move to another version, take its files, and these lines again.

## EasyAdmin

- Sign in as `admin`, who does everything, or as `clerk`, who can not delete a
  thing: EasyAdmin does not show the action, and refuses it.
- The dashboard: the sales of the last weeks, the best sellers, what runs low.
  There is no library: a template, and some CSS.
- Filter the albums with a low stock, and restock one. Each cover is a SVG that
  `src/Twig/CoverArt.php` draws from the title of the album.
- Select the orders that are paid, and ship them all, with a batch action.
- Create an order: the customer and the albums are autocompletes, and the lines
  a collection.
- Switch to French, in the menu of the gear: the labels, the prices and the
  dates change.

The fixtures, `src/DataFixtures/AppFixtures.php`, run in the browser on the first
visit, with `bin/console doctrine:fixtures:load`: they take a quarter of a
second, and the dates of the sales are the ones of the visitor. A seed gives
everyone the same shop.
