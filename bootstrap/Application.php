<?php

use Monolog\Logger;
use Illuminate\Support\Str;
use Illuminate\Support\Composer;
use Monolog\Handler\StreamHandler;
use Illuminate\Container\Container;
use Monolog\Formatter\LineFormatter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Config\Repository as ConfigRepository;

class Application extends Container
{

    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];

    /**
     * A custom callback used to configure Monolog.
     *
     * @var callable|null
     */
    protected $monologConfigurator;

    /**
     * The application namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Create a new Lumen application instance.
     *
     * @param  string|null  $basePath
     * @return void
     */
    public function __construct($basePath = null)
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        $this->basePath = $basePath;

        $this->bootstrapContainer();
    }

    /**
     * Bootstrap the application container.
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance('Application', $this);

        $this->instance('path', $this->path());

        $this->registerContainerAliases();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return 'SoaPhp v1.0.0 Base Lumen and Swoole';
    }

    /**
     * Determine if the application is currently down for maintenance.
     *
     * @return bool
     */
    public function isDownForMaintenance()
    {
        return false;
    }

    /**
     * Get or check the current application environment.
     *
     * @param  mixed
     * @return string
     */
    public function environment()
    {
        $env = env('APP_ENV', 'production');

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }

            return false;
        }

        return $env;
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  array  $options
     * @param  bool   $force
     * @return \Illuminate\Support\ServiceProvider
     */
    public function register($provider, $options = [], $force = false)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = true;

        $provider->register();
        $provider->boot();
    }

    /**
     * Register a deferred provider and service.
     *
     * @param  string  $provider
     * @param  string|null  $service
     * @return void
     */
    public function registerDeferredProvider($provider, $service = null)
    {
        return $this->register($provider);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param  string  $abstract
     * @param  array   $parameters
     * @return mixed
     */
    public function make($abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($this->normalize($abstract));

        if (array_key_exists($abstract, $this->availableBindings) &&
            ! array_key_exists($this->availableBindings[$abstract], $this->ranServiceBinders)) {
            $this->{$method = $this->availableBindings[$abstract]}();

            $this->ranServiceBinders[$method] = true;
        }

        return parent::make($abstract, $parameters);
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerCacheBindings()
    {
        $this->singleton('cache', function () {
            return $this->loadComponent('cache', 'Illuminate\Cache\CacheServiceProvider');
        });
        $this->singleton('cache.store', function () {
            return $this->loadComponent('cache', 'Illuminate\Cache\CacheServiceProvider', 'cache.store');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerComposerBindings()
    {
        $this->singleton('composer', function ($app) {
            return new Composer($app->make('files'), $this->basePath());
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository;
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerDatabaseBindings()
    {
        $this->singleton('db', function () {
            return $this->loadComponent(
                'database', [
                'Illuminate\Database\DatabaseServiceProvider',
                'Illuminate\Pagination\PaginationServiceProvider',
            ], 'db'
            );
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEventBindings()
    {
        $this->singleton('events', function () {
            $this->register('Illuminate\Events\EventServiceProvider');

            return $this->make('events');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerEncrypterBindings()
    {
        $this->singleton('encrypter', function () {
            return $this->loadComponent('app', 'Illuminate\Encryption\EncryptionServiceProvider', 'encrypter');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerFilesBindings()
    {
        $this->singleton('files', function () {
            return new Filesystem;
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerHashBindings()
    {
        $this->singleton('hash', function () {
            $this->register('Illuminate\Hashing\HashServiceProvider');

            return $this->make('hash');
        });
    }

    /**
     * Register container bindings for the application.
     *
     * @return void
     */
    protected function registerLogBindings()
    {
        $this->singleton('Psr\Log\LoggerInterface', function () {
            if ($this->monologConfigurator) {
                return call_user_func($this->monologConfigurator, new Logger('lumen'));
            } else {
                return new Logger('lumen', [$this->getMonologHandler()]);
            }
        });
    }

    /**
     * Define a callback to be used to configure Monolog.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function configureMonologUsing(callable $callback)
    {
        $this->monologConfigurator = $callback;

        return $this;
    }

    /**
     * Get the Monolog handler for the application.
     *
     * @return \Monolog\Handler\AbstractHandler
     */
    protected function getMonologHandler()
    {
        return (new StreamHandler(storage_path('logs/lumen.log'), Logger::DEBUG))
            ->setFormatter(new LineFormatter(null, null, true, true));
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param  string  $config
     * @param  array|string  $providers
     * @param  string|null  $return
     * @return mixed
     */
    public function loadComponent($config, $providers, $return = null)
    {
        $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->register($provider);
        }

        return $this->make($return ?: $config);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param  string  $name
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param  string|null  $name
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->basePath('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }

    /**
     * Register the facades for the application.
     *
     * @return void
     */
    public function withFacades()
    {
        Facade::setFacadeApplication($this);

        if (! static::$aliasesRegistered) {
            static::$aliasesRegistered = true;

            class_alias('Illuminate\Support\Facades\Cache', 'Cache');
            class_alias('Illuminate\Support\Facades\DB', 'DB');
            class_alias('Illuminate\Support\Facades\Log', 'Log');
            class_alias('Illuminate\Support\Facades\Schema', 'Schema');
        }
    }

    /**
     * Load the Eloquent library for the application.
     *
     * @return void
     */
    public function withEloquent()
    {
        $this->make('db');
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'app';
    }

    /**
     * Get the base path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function basePath($path = null)
    {
        if (isset($this->basePath)) {
            return $this->basePath.($path ? '/'.$path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd().'/../');
        }

        return $this->basePath($path);
    }

    /**
     * Get the database path for the application.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->basePath().'/database';
    }

    /**
     * Get the storage path for the application.
     *
     * @param  string|null  $path
     * @return string
     */
    public function storagePath($path = null)
    {
        return $this->basePath().'/storage'.($path ? '/'.$path : $path);
    }

    /**
     * Determine if the application is running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Determine if we are running unit tests.
     *
     * @return bool
     */
    public function runningUnitTests()
    {
        return $this->environment() == 'testing';
    }

    /**
     * Prepare the application to execute a console command.
     *
     * @return void
     */
    public function prepareForConsoleCommand()
    {
        $this->withFacades();

        $this->make('cache');

        $this->configure('database');

        $this->register('Illuminate\Database\MigrationServiceProvider');
        $this->register('Illuminate\Database\SeedServiceProvider');
    }

    /**
     * Register the core container aliases.
     *
     * @return void
     */
    protected function registerContainerAliases()
    {
        $this->aliases = [
            'Illuminate\Contracts\Foundation\Application' => 'app',
            'Illuminate\Contracts\Cache\Factory' => 'cache',
            'Illuminate\Contracts\Cache\Repository' => 'cache.store',
            'Illuminate\Contracts\Config\Repository' => 'config',
            'Illuminate\Container\Container' => 'app',
            'Illuminate\Contracts\Container\Container' => 'app',
            'Illuminate\Database\ConnectionResolverInterface' => 'db',
            'Illuminate\Database\DatabaseManager' => 'db',
            'Illuminate\Contracts\Encryption\Encrypter' => 'encrypter',
            'Illuminate\Contracts\Events\Dispatcher' => 'events',
            'Illuminate\Contracts\Hashing\Hasher' => 'hash',
            'log' => 'Psr\Log\LoggerInterface',
        ];
    }

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        'cache' => 'registerCacheBindings',
        'cache.store' => 'registerCacheBindings',
        'Illuminate\Contracts\Cache\Factory' => 'registerCacheBindings',
        'Illuminate\Contracts\Cache\Repository' => 'registerCacheBindings',
        'composer' => 'registerComposerBindings',
        'config' => 'registerConfigBindings',
        'db' => 'registerDatabaseBindings',
        'Illuminate\Database\Eloquent\Factory' => 'registerDatabaseBindings',
        'encrypter' => 'registerEncrypterBindings',
        'Illuminate\Contracts\Encryption\Encrypter' => 'registerEncrypterBindings',
        'events' => 'registerEventBindings',
        'Illuminate\Contracts\Events\Dispatcher' => 'registerEventBindings',
        'files' => 'registerFilesBindings',
        'hash' => 'registerHashBindings',
        'Illuminate\Contracts\Hashing\Hasher' => 'registerHashBindings',
        'log' => 'registerLogBindings',
        'Psr\Log\LoggerInterface' => 'registerLogBindings'
    ];
}
