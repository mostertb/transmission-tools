<?php
namespace Mostertb\TransmissionTools\Command;

use Mostertb\TransmissionTools\Helper\Config\Config;
use Mostertb\TransmissionTools\Helper\Humanizer;
use Mostertb\TransmissionTools\Helper\TranmissionApi\TransmissionApiClientFactory;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Transmission\Transmission;

class SessionInformationCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('session-information')
            ->setDescription('Retrieve session information from all Transmission clients');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->acquireLock();
        parent::initialize($input, $output);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientConfigs = Config::getInstance()->getClientConfigs(true);
        $clients = [];
        foreach ($clientConfigs as $clientConfig){
            $clients[$clientConfig['name']] = TransmissionApiClientFactory::makeApiClient(
              $clientConfig['host'],
              $clientConfig['port'],
              $clientConfig['username'],
              $clientConfig['password']
            );
        }

        $tableRows = [];
        $totalRow = [
            'name' => 'TOTAL',
            'total' => 0,
            'active' => 0,
            'paused' => 0,
            'up' => 0,
            'down' => 0
        ];
        /** @var Transmission $client */
        foreach ($clients as $clientName => $client){
            $output->writeln('Retrieving session information from '.$clientName.' ('.$clientConfigs[$clientName]['host'].
                ':'.$clientConfigs[$clientName]['port'].')');
            $sessionData = $client->getSessionStats();
            $tableRows[] = [
                $clientName,
                $sessionData->getTorrentCount(),
                $sessionData->getActiveTorrentCount(),
                $sessionData->getPausedTorrentCount(),
                Humanizer::dataRate($sessionData->getUploadSpeed()),
                Humanizer::dataRate($sessionData->getDownloadSpeed())
            ];
            $totalRow['total'] += $sessionData->getTorrentCount();
            $totalRow['active'] += $sessionData->getActiveTorrentCount();
            $totalRow['paused'] += $sessionData->getPausedTorrentCount();
            $totalRow['up'] += $sessionData->getUploadSpeed();
            $totalRow['down'] += $sessionData->getDownloadSpeed();
        }

        $totalRow['up'] = Humanizer::dataRate($totalRow['up']);
        $totalRow['down'] = Humanizer::dataRate($totalRow['down']);

        $table = new Table($output);
        $table->setHeaders(['Client', 'Total', 'Active', 'Paused', 'Up Speed', 'Down Speed'])
            ->setRows($tableRows)
            ->addRow(new TableSeparator())
            ->addRow($totalRow);
        $table->render();

        return 0;
    }

}