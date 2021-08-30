<?php

namespace Arrilot\Widgets\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Collection;

class WidgetMakeCommand extends GeneratorCommand {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new widget (arrilot/laravel-widgets)';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Widget';

    /**
     * Execute the console command for Laravel >= 5.5.
     *
     * @return void
     */
    public function handle() {
        // hack for Laravel < 5.5
        if (is_callable('parent::handle')) {
            parent::handle();
        } else {
            parent::fire();
        }

        if (!$this->option('plain')) {
            $this->createView();
        }
    }

    /**
     * Execute the console command for Laravel < 5.5.
     *
     * @return void
     */
    public function fire() {
        parent::fire();

        if (!$this->option('plain')) {
            $this->createView();
        }
    }

    /**
     * Build the class with the given name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function buildClass($name) {
        $stub = $this->files->get($this->getStub());

        $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

        if (!$this->option('plain')) {
            $stub = $this->replaceView($stub);
        }

        return $stub;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() {
        $stubName = $this->option('plain') ? 'widget_plain' : 'widget';
        $stubPath = $this->laravel->make('config')->get('laravel-widgets.' . $stubName . '_stub');

        // for BC
        if (is_null($stubPath)) {
            return __DIR__ . '/stubs/' . $stubName . '.stub';
        }

        return $this->laravel->basePath() . '/' . $stubPath;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name) {
        $stub = str_replace(
            '{{namespace}}', $this->getNamespace($name), $stub
        );

        $stub = str_replace(
            '{{rootNamespace}}', $this->laravel->getNamespace(), $stub
        );

        return $this;
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name) {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('{{class}}', $class, $stub);
    }

    /**
     * Replace the view name for the given stub.
     *
     * @param string $stub
     *
     * @return string
     */
    protected function replaceView($stub) {
        $view = 'widgets.' . str_replace('/', '.', $this->makeViewName());

        return str_replace('{{view}}', $view, $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace) {
        $namespace = config('laravel-widgets.default_namespace', $rootNamespace . '\Widgets');

        if (!Str::startsWith($namespace, $rootNamespace)) {
            throw new RuntimeException("You can not use the generator if the default namespace ($namespace) does not start with application namespace ($rootNamespace)");
        }

        return $namespace;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['plain', null, InputOption::VALUE_NONE, 'Use plain stub. No view is being created too.'],
        ];
    }

    /**
     * Create a new view file for the widget.
     *
     * return void
     */
    protected function createView() {
        if ($this->files->exists($path = $this->getViewPath()) || $this->files->exists($pathDefault = $this->getViewPathDefault())) {
            $this->error('View already exists!' . $path. ' | ' . $pathDefault);

            return;
        }

        $this->makeDirectory($path);
        $this->makeDirectory($pathDefault);


        $name = str_replace($this->laravel->getNamespace(), '', $this->argument('name'));
        $widget_name = basename(dirname($path));
        $title_name = ucwords(str_replace('_', ' ', $widget_name));
        $config_json = [
            'title' => $title_name,
            'name' => $widget_name,
            'style' => 'main',
            'run_name' => $name
        ];
        //set config
        $json = Collection::make($config_json)->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $path_config_r = rtrim($path, '.blade.php');
        $path_config_default_r = rtrim($pathDefault, '.blade.php');
        $path_config = $path_config_r . '.json';
        $path_config_default = $path_config_default_r . '.json';
        $path_config_ini = $path_config_r . '_ini.json';
        $path_config_default_ini = $path_config_default_r . '_ini.json';

        //put data config
        $this->files->put($path_config, $json);
        $this->files->put($path_config_default, $json);
        $this->files->put($path_config_ini, $json);
        $this->files->put($path_config_default_ini, $json);
        $this->files->put($path, file_get_contents(__DIR__ . '/stubs/widget_html_blade.blade.php'));
        $this->files->put($pathDefault, file_get_contents(__DIR__ . '/stubs/widget_html_blade.blade.php'));

        //for admin settings
        $widget_blade_setting = file_get_contents(__DIR__ . '/stubs/widget_setting.blade.php');
        $widget_blade_setting = str_replace('[run_name]', $name, $widget_blade_setting);
        $this->files->put(base_path('/Themes/admin/views/pages/interface/settings/widgets/' . $widget_name . '.blade.php'), $widget_blade_setting);

        //controller settings
        $widget_controller_setting = file_get_contents(__DIR__ . '/stubs/settings_controller.php');
        $widget_controller_setting = str_replace('class RunName', "class $name", $widget_controller_setting);
        $this->files->put(app_path('Http/Controllers/Admin/WidgetsSetting/' . $name . '.php'), $widget_controller_setting);

        //controller get data
//        $file_controller_data = app_path('Http/Controllers/Getter.php');
//        $controller_data = file_get_contents($file_controller_data);
//        if (strpos($controller_data, "function $name") === false
//            && strpos($controller_data, $auto_add='//AUTO_ADD=ON') !== false
//        ) {
//            $controller_data = str_replace(
//                $auto_add,
//                "\npublic function $name(\$options=[]) {\n    }\n    $auto_add",
//                $controller_data);
//            file_put_contents($file_controller_data, $controller_data);
//        }

        $this->info('View created successfully.');
    }

    /**
     * Get the destination view path.
     *
     * @return string
     */
    protected function getViewPath() {
        $path = base_path('Themes/' . config('theme.active') . '/views');//base_path('resources/views')
        return $path . '/widgets/' . $this->makeViewName() . '/main.blade.php';
    }

    protected function getViewPathDefault() {
        $path = base_path('Themes/default/views');
        return $path . '/widgets/' . $this->makeViewName() . '/main.blade.php';
    }

    /**
     * Get the destination view name without extensions.
     *
     * @return string
     */
    protected function makeViewName() {
        $name = str_replace($this->laravel->getNamespace(), '', $this->argument('name'));
        $name = str_replace('\\', '/', $name);

        // convert to snake_case part by part to avoid unexpected underscores.
        $nameArray = explode('/', $name);
        array_walk($nameArray, function (&$part) {
            $part = Str::snake($part);
        });

        return implode('/', $nameArray);
    }
}
