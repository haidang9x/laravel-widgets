<?php namespace Arrilot\Widget\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Way\Generators\Commands\GeneratorCommand;

class MakeWidgetCommand extends GeneratorCommand {

	protected $name = 'make:widget';
	protected $description = 'Creates a widget';


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['widgetName', InputArgument::REQUIRED, 'The name of the desired widget.']
		];
	}


	/**
	 * The path where the file will be created
	 *
	 * @return mixed
	 */
	protected function getFileGenerationPath()
	{
		return app_path('Widgets') . '/' . studly_case($this->argument('widgetName')) . '.php';
	}


	/**
	 * Template which is used for generation
	 *
	 * @return string
	 */
	protected function getTemplatePath()
	{
		return "workbench/Arrilot/Widget/src/templates/widget.txt";
	}


	/**
	 * Data which is passed to the template
	 * @return array
	 */
	protected function getTemplateData()
	{
		return [
			'NAME' => studly_case($this->argument('widgetName'))
		];
	}

}
