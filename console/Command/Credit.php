<?php

namespace Every8d\Console\Command;

use Every8d\Console\ClientTrait;
use Every8d\Console\IoTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\VarDumper;

class Credit extends Command
{
    use ClientTrait, IoTrait;

    protected function configure()
    {
        $this->setName('credit')
            ->setDescription('餘額查詢');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $client = $this->createClient($input);

            VarDumper::dump($client->getApi()->getCredit());

            return null;
        } catch (\Exception $e) {
            $io->error([$this->getErrorMessage($e)]);

            return 1;
        }
    }
}
