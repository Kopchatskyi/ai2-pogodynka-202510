<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Forecast;
use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\ForecastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WeatherUtil
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly ForecastRepository $forecastRepository,
    )
    {
    }
    /**
     * @return Forecast[]
     */
    public function getWeatherForLocation(Location $location): array
    {
        $forecasts = $this->forecastRepository->findBy(['location' => $location]);

        return $forecasts;
    }


    /**
     * @return Forecast[]
     */
    public function getWeatherForCountryAndCity(string $countryCode, string $city): array
    {
        //location by city and country
        $location = $this->locationRepository->findOneByCityAndCountry($city, $countryCode);

        if (!$location) {
            throw new NotFoundHttpException("City '$city' ($countryCode) not found.");
        }
        return $this->getWeatherForLocation($location);
    }
}


#php -S localhost:53861 -t .\public\
#http://pogodynka.localhost:53861/location
