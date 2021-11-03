<?php

namespace Arrilot\Widgets;

use Arrilot\Widgets\Console\WidgetMakeCommand;
use Arrilot\Widgets\Factories\AsyncWidgetFactory;
use Arrilot\Widgets\Factories\WidgetFactory;
use Arrilot\Widgets\Misc\LaravelApplicationWrapper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class ServiceProvider extends \Illuminate\Support\ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php', 'laravel-widgets'
        );

        $this->app->bind('arrilot.widget', function () {
            return new WidgetFactory(new LaravelApplicationWrapper());
        });

        $this->app->bind('arrilot.async-widget', function () {
            return new AsyncWidgetFactory(new LaravelApplicationWrapper());
        });

        $this->app->singleton('arrilot.widget-group-collection', function () {
            return new WidgetGroupCollection(new LaravelApplicationWrapper());
        });

        $this->app->singleton('arrilot.widget-namespaces', function () {
            return new NamespacesRepository();
        });

        $this->app->singleton('command.widget.make', function ($app) {
            return new WidgetMakeCommand($app['files']);
        });

        $this->commands('command.widget.make');

        $this->app->alias('arrilot.widget', 'Arrilot\Widgets\Factories\WidgetFactory');
        $this->app->alias('arrilot.async-widget', 'Arrilot\Widgets\Factories\AsyncWidgetFactory');
        $this->app->alias('arrilot.widget-group-collection', 'Arrilot\Widgets\WidgetGroupCollection');
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('laravel-widgets.php'),
        ]);

        $routeConfig = [
            'namespace' => 'Arrilot\Widgets\Controllers',
            'prefix' => 'arrilot',
            'middleware' => $this->app['config']->get('laravel-widgets.route_middleware', []),
        ];

        if (!$this->app->routesAreCached()) {
            $this->app['router']->group($routeConfig, function ($router) {
                $router->get('load-widget', 'WidgetController@showWidget');
            });
        }

        Blade::directive('widget', function ($expression) {
            return "<?php echo app('arrilot.widget')->run($expression); ?>";
        });
        Blade::directive('widgetInclude', function ($expression) {
//            $args = explode(',', $expression);
            $args = [];
            $expression = preg_replace('/\$([a-zA-Z0-9\_]+)/m', '\'\$%$1\'', $expression);
            eval("\$args = [$expression];");
            $nameCase = $args[0];
            $nameSlug = Str::snake($args[0]);
            $params = [];
            $params['view'] = "widgets.$nameSlug.main";
            $params['page'] = '';
            $theme_path = base_path('Themes/' . config('theme.active') . '/views');
            $path_config = $theme_path . '/widgets/' . $nameSlug . '/main.json';
            if (isset($args[1])) {
                if (isset($args[1]['page'])) {
                    $params['page'] = $args[1]['page'];
                }
            }
            if(!empty($params['page'])) $path_config = $theme_path . '/pages/' . $params['page'] . '/widgets/' . $nameSlug.'/main.json';
            $wg_config = json_decode('{}');
            if (file_exists($path_config)) {
                $params['json'] = file_get_contents($path_config);
                $wg_config = json_decode($params['json']);
            }
            if(!empty($args[1]['config'])) {
                $this->mergeArrayToObject($args[1]['config'], $wg_config);
            }
            $params['json'] = $wg_config;
            if (!empty($wg_config->style)) $params['view'] = "widgets.$nameSlug.{$wg_config->style}";

            $exportParams = var_export($params, true);
            $newExpression = "'$nameCase'," . $exportParams;//var_export($, true);
            $newExpression = preg_replace('/(\'|\")\$\%([a-zA-Z0-9\_]+)(\'|\")/mi', '\$$2', $newExpression);
            return "<?php echo app('arrilot.widget')->run($newExpression); ?>";
        });

        Blade::directive('asyncWidget', function ($expression) {
            return "<?php echo app('arrilot.async-widget')->run($expression); ?>";
        });

        Blade::directive('widgetGroup', function ($expression) {
            return "<?php echo app('arrilot.widget-group-collection')->group($expression)->display(); ?>";
        });
    }
    private function mergeArrayToObject($obj1, &$obj2) {
        if (is_object($obj2)) {
            foreach($obj1 as $k => $v) {
                if(is_string($v)) $obj2->{$k} = $v;
                else {
                    if(!isset($obj2->{$k})) $obj2->{$k} = (object)[];
                    $this->mergeArrayToObject($v, $obj2->{$k});
                }
            }
        }
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['arrilot.widget', 'arrilot.async-widget'];
    }
}
