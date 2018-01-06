<?php

namespace Every8d\Console;

use Every8d\Client;
use Symfony\Component\Console\Input\InputInterface;

trait ClientTrait
{
    public function createClient(InputInterface $input): Client
    {
        $username = $input->getOption('username');
        $password = $input->getOption('password');

        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('The --username and --password are required');
        }

        return new Client($username, $password);
    }
}
