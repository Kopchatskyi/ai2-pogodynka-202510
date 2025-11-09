<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Service\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class WeatherApiController extends AbstractController
{
    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        #[MapQueryParameter('country')] string $country,
        #[MapQueryParameter('city')] string $city,
        #[MapQueryParameter('format')] ?string $format,
        #[MapQueryParameter('twig')] bool $twig = false,
        WeatherUtil $util
    ): Response
    {
        $forecasts = $util->getWeatherForCountryAndCity($country, $city);

        //TWIG
        if ($twig) {
            if ($format === 'json') {
                return $this->render('weather_api/index.json.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $forecasts,
                ]);
            }

            if ($format === 'csv') {
                $response = $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $forecasts,
                ]);
                $response->headers->set('Content-Type', 'text/csv');
                return $response;
            }
        }

        //JSON
        if ($format === 'json') {
            return $this->json([
                'city' => $city,
                'country' => $country,
                'measurements' => array_map(fn(Forecast $m) => [
                    'date' => $m->getDate()->format('Y-m-d'),
                    'celsius' => $m->getCelsius(),
                    'fahrenheit' => $m->getFahrenheit(),
                    'humidity' => $m->getHumidity(),
                    'feels_like' => $m->getFeelsLike(),
                ], $forecasts),
            ]);
        }

        //CSV
        if ($format === 'csv') {
            $csv = "city,country,date,celsius,fahrenheit,humidity,feels_like\n"; // <-- nagłówek z fahrenheit
            foreach ($forecasts as $forecast) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s\n",
                    $city,
                    $country,
                    $forecast->getDate()->format('Y-m-d'),
                    $forecast->getCelsius(),
                    $forecast->getFahrenheit(),
                    $forecast->getHumidity(),
                    $forecast->getFeelsLike()
                );
            }

            $response = new Response($csv);
            $response->headers->set('Content-Type', 'text/csv');
            return $response;
        }


        return new JsonResponse(['error' => 'Invalid format. Allowed: json, csv'], Response::HTTP_BAD_REQUEST);
    }
}
