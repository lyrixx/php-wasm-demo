<?php

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Castor\Exception\ProblemException;
use PhpWasm\Opcache;
use PhpWasm\Stack;
use PhpWasm\Target;
use Symfony\Component\Console\Input\InputOption;

use function Castor\context;
use function Castor\fs;
use function Castor\io;
use function Castor\run;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', false);

// Where GitHub Pages serves the website: the name of the repository
const SITE_PATH = '/php-wasm-demo/';

/**
 * @param list<string> $set
 */
#[AsTask(namespace: 'app', description: 'Build the demos in public/: their dependencies, their assets, and PHP with them in it')]
function build(
    #[AsOption(description: 'Override a value of the bake file, as "docker buildx bake --set" does (the CI gives the cache this way), can be repeated', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)]
    array $set = [],
): void {
    io()->title('Building the demos');

    foreach (wasm_stacks() as $stack) {
        // The composer of Docker: Docker is all the build needs. It runs the
        // scripts of the application too, that build its assets.
        run(
            ['docker', 'run', '--rm', '--user', posix_getuid() . ':' . posix_getgid(), '--volume', $stack->embed . ':/app', '--workdir', '/app', 'composer:2', 'install', '--no-dev', '--classmap-authoritative', '--no-interaction', '--no-progress'],
            context: context()->withQuiet(),
        );
    }

    PhpWasm\build(wasm_stacks(), $set);
}

#[AsTask(namespace: 'app', description: 'Check that what is built boots, and that the demos answer')]
function smoke(): void
{
    build_if_missing();

    io()->title('Checking the demos');

    foreach (wasm_stacks() as $stack) {
        PhpWasm\check_stack($stack);
    }

    PhpWasm\test_page('tests/demos.test.mjs');
}

#[AsTask(namespace: 'app', description: 'Serve the website on 127.0.0.1, under the path where GitHub Pages serves it')]
function serve(
    #[AsOption(description: 'The port to listen to')]
    int $port = 8000,
): void {
    // Before the build, that takes minutes: the built-in server only says it
    // once started
    if (is_listened_to($port)) {
        $free = $port + 1;
        while (is_listened_to($free)) {
            ++$free;
        }

        throw new ProblemException(\sprintf('Something already listens to the port %d: give another one, with --port %d for example.', $port, $free));
    }

    build_if_missing();

    // The relative URLs, and the scope of the service workers, must work there
    $root = __DIR__ . '/build/pages';
    fs()->remove($root);
    fs()->symlink(__DIR__ . '/public', $root . rtrim(SITE_PATH, '/'));

    io()->title(\sprintf('Serving on http://127.0.0.1:%d%s', $port, SITE_PATH));

    run(['php', '-S', "127.0.0.1:{$port}", '-t', $root], context: context()->withAllowFailure());
}

function is_listened_to(int $port): bool
{
    $socket = @fsockopen('127.0.0.1', $port, timeout: 1);
    if (false === $socket) {
        return false;
    }

    fclose($socket);

    return true;
}

function build_if_missing(): void
{
    foreach (wasm_stacks() as $stack) {
        if (!is_file($stack->artifact(Target::Web, 'wasm'))) {
            build();

            return;
        }
    }
}

/**
 * PHP, compiled to WASM by lyrixx/php-wasm-builder, with a demo in it.
 *
 * @return list<Stack>
 */
function wasm_stacks(): array
{
    // What both demos need, so PHP is only compiled once: ctype, dom, iconv,
    // mbstring and xml, that their dependencies require, filter, that Symfony
    // calls everywhere, simplexml, that reads the translations, tokenizer,
    // without which the routes of the controllers are not found, pdo_sqlite,
    // session, and intl with its data: the dates and the prices, in the
    // language of the page.
    $symfony = Stack::named('symfony')
        // Keep in sync with config.platform.php in apps/easyadmin/composer.json
        ->php('8.5.12')
        ->extensions('ctype', 'dom', 'filter', 'iconv', 'intl', 'mbstring', 'pdo_sqlite', 'session', 'simplexml', 'tokenizer', 'xml')
        // Symfony makes its cache when the stack is built, with the paths of
        // the WASM filesystem, and opcache compiles the files as they run
        ->warmup('bin/console', 'cache:warmup')
        ->opcache(Opcache::Runtime)
        // What is embedded, and the 32 MB of the data of ICU, stay in the memory
        ->memory('256mb')
        ->targets(Target::Web);

    return [
        // Without what the .dockerignore of each application leaves out
        $symfony->renamed('easyadmin')->embed('apps/easyadmin', at: '/app')->outputDir('public/easyadmin/build'),
        $symfony->renamed('symfony-demo')->embed('apps/symfony-demo', at: '/app')->outputDir('public/symfony-demo/build'),
    ];
}
