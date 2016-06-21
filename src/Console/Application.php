<?php
namespace Jedi58\WordpressToMarkdown\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Jedi58\WordpressToMarkdown\Console\Command\SaveCommand;
use Jedi58\WordpressToMarkdown\Console\Command\OutputCommand;

/**
 * Application class for handling console access to Jira
 */
class Application extends BaseApplication
{
    const NAME = 'WordpressToMarkdown Console';
    const VERSION = '1.0.0';

    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);

        $this->addCommands(array(
            new SaveCommand(),
            new OutputCommand()
        ));
    }
}
