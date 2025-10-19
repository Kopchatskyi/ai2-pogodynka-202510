<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\ForecastRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country}', name: 'app_weather', requirements: ['city' => '[A-Za-z]+', 'country' => '[A-Za-z]{2}'])]
    public function city(string $city, LocationRepository $locationRepository, ForecastRepository $forecastRepository, $country): Response
    {
        //location za pomocą city i country
        $location = $locationRepository->findOneBy([
            'city' => $city,
            'country' => ($country)
        ]);
        if (!$location) {
            throw $this->createNotFoundException("City '$city' ($country) not found.");
        }

        //forecast dla location
        $forecasts = $forecastRepository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $forecasts,
        ]);
    }
}


