<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SuggestionsController extends Controller
{
    public function addresses(Request $request)
    {
       $areaId = $request->get('query')['area_id'];
       $districtId = $request->get('query')['district_id'];
       $street = $request->get('query')['street'];

       $area = Area::findOrFail($areaId);
       $district = District::findOrFail($districtId);
       $query = [];
       if (!is_null($area)) {
           $query[] = $area->name;
       }
       if (!is_null($district)) {
           $query[] = $district->name;
       }
       if (!empty($street)) {
           $query[] = $street;
       }
       $query = join(', ', $query);
       $dadata = new \Dadata\DadataClient( config('app.dadata_api_key'), config('app.dadata_api_secret'));
       $res = $dadata->suggest('address', $query, 10, ['from_bound' => ['value' => 'street'], 'to_bound' => ['value' => 'street']]);
       return $res;
    }

    public function coordinates(Request $request)
    {
        $areaId = $request->get('query')['area_id'];
        $districtId = $request->get('query')['district_id'];
        $street = $request->get('query')['street'];
        $houseNumber = $request->get('query')['house_number'];
        $corpusNumber = $request->get('query')['corpus_number'];

        $area = Area::findOrFail($areaId);
        $district = District::findOrFail($districtId);

        $query = [];
        if (!is_null($area)) {
            $query[] = $area->name;
        }
        if (!is_null($district)) {
            $query[] = $district->name;
        }
        if (!empty($street)) {
            $query[] = $street;
        }
        if ($houseNumber > 0) {
            $query[] = $houseNumber;
        }
        if ($corpusNumber > 0) {
            $query[] = 'корп. ' . $corpusNumber;
        }
        $query = join(' ', $query);
        $dadata = new \Dadata\DadataClient( config('app.dadata_api_key'), config('app.dadata_api_secret'));
        $res = $dadata->suggest('address', $query, 10, ['from_bound' => ['value' => 'street'], 'to_bound' => ['value' => 'house']]);
        if (!empty($res)) {
            return [
                'longitude' => $res[0]['data']['geo_lon'],
                'latitude' => $res[0]['data']['geo_lat'],
            ];
        }
        return false;
    }
}
