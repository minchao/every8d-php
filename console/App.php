<?php

namespace Every8d\Console;

use Every8d\Client;
use Every8d\Console\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    public function __construct()
    {
        parent::__construct('Developer Tools', Client::LIBRARY_VERSION);

        $this->getDefinition()
            ->addOptions([
                new InputOption(
                    'username',
                    'u',
                    InputOption::VALUE_REQUIRED,
                    'EVERY8D Username'
                ),
                new InputOption(
                    'password',
                    'p',
                    InputOption::VALUE_REQUIRED,
                    'EVERY8D Password'
                ),
            ]);

        $this->addCommands([
            new Command\Credit(),
            new Command\Send(),
            new Command\Cancel(),
            new Command\DeliveryStatus(),
        ]);
    }
}
