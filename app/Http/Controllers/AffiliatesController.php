<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AffiliatesController extends Controller
{
    const AFFILIATES_FILE = 'affiliates.txt';
    const OFFICE_COORD = ['lat' => 53.3340285, 'long' => -6.2535495];
    const MAX_DISTANCE_DEFAULT_KM = 100;

    public function homepage()
    {
        $affiliates = self::getAffiliatesWithinDistance();
        $distanceKm = self::MAX_DISTANCE_DEFAULT_KM;
        return view('welcome', compact('affiliates', 'distanceKm' ));
    }

    public function getAffiliatesWithinDistance($distance = self::MAX_DISTANCE_DEFAULT_KM, $file = self::AFFILIATES_FILE)
    {
        $file_exists = Storage::disk('public')->exists($file);//check file exists
        $array = [];
        if ($file_exists) {
            $content = Storage::disk('public')->get($file); //get file
            $data = explode("\n", $content);
            foreach ($data as $row) {
                $decodeData = json_decode($row, true);
                $distanceAway = $this->getDistanceBetweenTwoPoints(self::OFFICE_COORD['lat'], self::OFFICE_COORD['long'], $decodeData['latitude'], $decodeData['longitude']);

                // add to array if  distance away is below the $distance (default 100km)
                if ($distanceAway <= $distance) {
                    $decodeData['distance'] = $distanceAway; //add total distance away
                    $array[] = $decodeData;
                }
            }

            $array = $this->getSortedAscArray($array, 'affiliate_id');

        } else {
            Log::error('File does not exist: ' . $file);
            return null;
        }
        return $array;
    }

    public function getDistanceBetweenTwoPoints($lat1, $long1, $lat2, $long2)
    {
        $difference = $long1 - $long2;
        $distance = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($difference)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515 * 1.609344;

        return round($distance, 2); //Two Decimal Places
    }

    public static function getSortedAscArray($array, $key)
    {
        usort($array, function ($a, $b)  use ($key) {
            return $a[$key] <=> $b[$key]; //ascending order
        });
        return $array;
    }
}
