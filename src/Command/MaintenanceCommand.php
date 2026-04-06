<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// Enregistrement de la commande sous le nom "app:maintenance"
#[AsCommand(name: 'app:maintenance')]
class MaintenanceCommand extends Command
{
    // Injection du chemin du fichier lock depuis services.yaml
    public function __construct(private string $lockFilePath)
    {
        parent::__construct();
    }

    // Déclaration de l'argument obligatoire : "on" ou "off"
    protected function configure(): void
    {
        $this->addArgument('action', InputArgument::REQUIRED, 'on ou off');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');

        if ($action === 'on') {
            // Création du fichier lock → déclenche la maintenance
            file_put_contents($this->lockFilePath, '');
            $output->writeln('<info>Mode maintenance ACTIVÉ.</info>');

        } elseif ($action === 'off') {
            // Suppression du fichier lock → site accessible à nouveau
            if (file_exists($this->lockFilePath)) {
                unlink($this->lockFilePath);
            }
            $output->writeln('<info>Mode maintenance DÉSACTIVÉ.</info>');

        } else {
            // Action non reconnue → échec propre avec message d'erreur
            $output->writeln('<error>Action invalide. Utilisez "on" ou "off".</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}