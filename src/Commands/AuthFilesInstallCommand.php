<?php

namespace Hesto\MultiAuth\Commands;

use Hesto\Core\Commands\InstallFilesCommand;
use Hesto\MultiAuth\Commands\Traits\OverridesCanReplaceKeywords;
use Hesto\MultiAuth\Commands\Traits\OverridesGetArguments;
use Hesto\MultiAuth\Commands\Traits\ParsesServiceInput;
use Symfony\Component\Console\Input\InputOption;


class AuthFilesInstallCommand extends InstallFilesCommand
{
    use OverridesCanReplaceKeywords, OverridesGetArguments, ParsesServiceInput;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'multi-auth:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install multi-auth files';

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        $parentOptions = parent::getOptions();
        return array_merge($parentOptions, [
            ['lucid', false, InputOption::VALUE_NONE, 'Lucid architecture'],
            ['domain', false, InputOption::VALUE_NONE, 'Install in a subdomain'],
        ]);
    }

    /**
     * Get the destination paths.
     *
     * @return string
     */
    public function getFiles()
    {
        $files = $this->getGeneralFiles();

        if ($this->option('domain') && ! $this->option('lucid')) {
            return array_replace_recursive($files, $this->getDomainStubPaths());
        }

        if ($this->option('lucid')) {
            return array_merge($files, $this->getLucidPaths());
        }

        return $files;
    }

    /**
     * Get the Domain specific stubs to override the default ones.
     *
     * @return array
     */
    public function getDomainStubPaths()
    {
        return [
            'login_controller' => [
                'stub' => __DIR__ . '/../stubs/DomainControllers/LoginController.stub'
            ],
        ];
    }

    /**
     * Get the default destination paths.
     *
     * @return string
     */
    public function getGeneralFiles()
    {
        $name = $this->getParsedNameInput();

        return [
            'routes' => [
                'path' => '/routes/' . $name .'.php',
                'stub' => __DIR__ . '/../stubs/routes/routes.stub',
            ],
            'middleware' => [
                'path' => '/app/Http/Middleware/RedirectIfNot' . ucfirst($name) .'.php',
                'stub' => __DIR__ . '/../stubs/Middleware/Middleware.stub',
            ],
            'guest_middleware' => [
                'path' => '/app/Http/Middleware/RedirectIf' . ucfirst($name) .'.php',
                'stub' => __DIR__ . '/../stubs/Middleware/GuestMiddleware.stub',
            ],
            'login_controller' => [
                'path' => '/app/Http/Controllers/' . ucfirst($name) . 'Auth/' . 'LoginController.php',
                'stub' => __DIR__ . '/../stubs/Controllers/LoginController.stub',
            ],
        ];
    }

    /**
     * Get and override the correct domain Lucid specific stubs.
     *
     * @return array
     */
    public function getLucidDomainStubPaths()
    {
        return [
            'middleware' => [
                'stub' => __DIR__ . '/../stubs/Lucid/DomainMiddleware/Middleware.stub',
            ],
            'guest_middleware' => [
                'stub' => __DIR__ . '/../stubs/Lucid/DomainMiddleware/GuestMiddleware.stub',
            ],
            'login_controller' => [
                'stub' => __DIR__ . '/../stubs/Lucid/DomainControllers/LoginController.stub',
            ],
        ];
    }

    /**
     * Get the specific Lucid paths.
     *
     * @return array
     */
    public function getLucidPaths()
    {
        $name = $this->getParsedNameInput();
        $service = $this->getParsedServiceInput();

        $lucidPaths = [
            'routes' => [
                'path' => '/src/Services/' . studly_case($service) . '/Http/' . $name .'-routes.php',
                'stub' => __DIR__ . '/../stubs/Lucid/routes/routes.stub',
            ],
            'middleware' => [
                'path' => '/src/Services/' . studly_case($service) . '/Http/Middleware/RedirectIfNot' . ucfirst($name) . '.php',
                'stub' => __DIR__ . '/../stubs/Lucid/Middleware/Middleware.stub',
            ],
            'guest_middleware' => [
                'path' => '/src/Services/' . studly_case($service) . '/Http/Middleware/RedirectIf' . ucfirst($name) . '.php',
                'stub' => __DIR__ . '/../stubs/Lucid/Middleware/GuestMiddleware.stub',
            ],
            'login_controller' => [
                'path' => '/src/Services/' . studly_case($service) . '/Http/Controllers/' . ucfirst($name) . 'Auth/LoginController.php',
                'stub' => __DIR__ . '/../stubs/Lucid/Controllers/LoginController.stub',
            ],
        ];

        if ($this->option('domain')) {
            return array_replace_recursive($lucidPaths, $this->getLucidDomainStubPaths());
        }

        return $lucidPaths;
    }
}
