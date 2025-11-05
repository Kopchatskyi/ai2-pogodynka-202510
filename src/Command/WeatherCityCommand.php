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

#[AsCommand(
    name: 'weather:city',
    description: 'Get the weather forecast for a specific city and country.',
)]
class WeatherCityCommand extends Command
{
    private LocationRepository $locationRepository;
    private WeatherUtil $weatherUtil;

    // Wstrzykiwanie zależności w konstruktorze
    public function __construct(LocationRepository $locationRepository, WeatherUtil $weatherUtil)
    {
        $this->locationRepository = $locationRepository;
        $this->weatherUtil = $weatherUtil;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('countryCode', InputArgument::REQUIRED, 'The country code ("PL" for Poland)')
            ->addArgument('city', InputArgument::REQUIRED, 'The name of the city');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $countryCode = $input->getArgument('countryCode');
        $city = $input->getArgument('city');

        //na podstawie kodu kraju i nazwy miasta
        $location = $this->locationRepository->findOneByCityAndCountry($city, $countryCode);

        if (!$location) {
            $io->error('Location not found!');
            return Command::FAILURE;
        }

        //pobieramy
        $forecasts = $this->weatherUtil->getWeatherForLocation($location);

        //wyniki
        $io->title(sprintf('Weather forecast for %s, %s', $city, $countryCode));
        $io->writeln(sprintf('Location: %s, %s', $city, $countryCode));

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
