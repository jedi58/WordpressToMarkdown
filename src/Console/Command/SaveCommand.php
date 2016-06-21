<?php

namespace Jedi58\WordpressToMarkdown\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Jedi58\WordpressToMarkdown\WordpressToMarkdown;

class SaveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('save')
            ->setDescription('Saves one or more Wordpress posts to a .md file')
            ->addArgument('url', InputArgument::REQUIRED, 'The URL to process Wordpress post(s) from');
    }
    /**
     * Creates the Jira ticket and outputs the issue-key
     * @param InputInterface $input The console input object
     * @param OutputInterface $output The console output object
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        WordpressToMarkdown::save($url);
    }
}
