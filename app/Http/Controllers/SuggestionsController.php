<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SuggestionsController extends Controller
{
    public function clean(Request $request)
    {
       $areaId = $request->get('query')['area_id'];
       $districtId = $request->get('query')['district_id'];
       $street = $request->get('query')['street'];
       if (is_null($areaId) && is_null($districtId)) {
           return [];
       }
       $area = Area::find($areaId);
       $district = District::find($districtId);
       $query = [];
       if (!is_null($area)) {
           $query[] = $area->name;
       }
       if (!is_null($district)) {
           $query[] = $district->name;
       }
       if (!empty($street)) {{
           $query[] = $street;
       }}
       $query = join(', ', $query);
       $dadata = new \Dadata\DadataClient( config('app.dadata_api_key'), config('app.dadata_api_secret'));
       $res = $dadata->suggest('address', $query, 10, ['from_bound' => ['value' => 'street'], 'to_bound' => ['value' => 'street']]);
       return $res;
    }
}
