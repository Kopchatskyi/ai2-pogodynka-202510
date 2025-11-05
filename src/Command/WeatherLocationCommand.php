<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Location;

#[AsCommand(
    name: 'weather:location',
    description: 'Get the weather forecast for a specific location.',
)]
class WeatherLocationCommand extends Command
{
    private LocationRepository $locationRepository;
    private WeatherUtil $weatherUtil;

    //wstrzykujemy z zewnatrz:)
    public function __construct(LocationRepository $locationRepository, WeatherUtil $weatherUtil)
    {
        $this->locationRepository = $locationRepository;
        $this->weatherUtil = $weatherUtil;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the location');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locationId = $input->getArgument('id');

        // Pobranie lokalizacji na podstawie ID
        $location = $this->locationRepository->find($locationId);

        if (!$location) {
            $io->error('Location not found!');
            return Command::FAILURE;
        }

        //pobranie
        $forecasts = $this->weatherUtil->getWeatherForLocation($location);

        //console
        $io->title(sprintf('Weather forecast for %s', $location->getCity()));
        $io->writeln(sprintf('Location: %s', $location->getCity()));

        //tabela z prognozami
        $io->table(
            ['Date', 'Temperature'],
            array_map(
                fn($forecast) => [
                    $forecast->getDate()->format('Y-m-d'),
                    $forecast->getCelsius()
                ],
                $forecasts
            )
        );

        return Command::SUCCESS;
    }
}
