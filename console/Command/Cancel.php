<?php

namespace Every8d\Console\Command;

use Every8d\Console\ClientTrait;
use Every8d\Console\HelperTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\VarDumper;

class Cancel extends Command
{
    use ClientTrait, HelperTrait;

    protected function configure()
    {
        $this->setName('cancel')
            ->setDescription('取消預約簡訊')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument(
                        'bid',
                        InputArgument::REQUIRED,
                        'Batch ID'
                    ),
                    new InputOption(
                        'type',
                        't',
                        InputOption::VALUE_REQUIRED,
                        'Message type ("sms"|"mms")',
                        'sms'
                    ),
                ])
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $bid = $input->getArgument('bid');
        $type = $input->getOption('type');

        $client = $this->createClient($input);

        try {
            if ($type === 'sms') {
                $resp = $client->getApi()->cancelSMS($bid);
            } else {
                $resp = $client->getApi()->cancelMMS($bid);
            }

            VarDumper::dump($resp);

            return null;
        } catch (\Exception $e) {
            $io->error([$this->getErrorMessage($e)]);

            return 1;
        }
    }
}
