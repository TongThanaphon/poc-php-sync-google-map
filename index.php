<?php
    function toRadian($val) {
        return $val * pi() / 180;
    }

    function calcualteDistance($lat1, $lon1, $lat2, $lon2) {
        $dLat = toRadian($lat2 - $lat1);
        $dLon = toRadian($lon2 - $lon1);
        $lat1 = toRadian($lat1);
        $lat2 = toRadian($lat2);

        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2); 
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
        $d = (6371 * $c) / 100;

        return $d;
    }

    function mapData($data) {
        $xml = simplexml_load_file($data);
        $placemarks = $xml->Document->Placemark;

        $result = [];
    
        for ($i=0; $i<sizeof($placemarks); $i++) {
            $item = $placemarks[$i];
            $name = explode(', ', $item->name);

            $geo = explode(',', preg_replace("/\s+/", '', $item->address));
        
            $name_station = $name[0];
            $lat = floatval($geo[0]);
            $long = floatval($geo[1]);
            $zIndex = 0;
            $shopId = explode(', ', $placemarks[$i]->ExtendedData->Data[1]->value);
            $serviceDay = explode(', ', $placemarks[$i]->ExtendedData->Data[2]->value);
            $open = $placemarks[$i]->ExtendedData->Data[3]->value;
            $close = $placemarks[$i]->ExtendedData->Data[4]->value;
            $province = explode(', ', $placemarks[$i]->ExtendedData->Data[5]->value);
            $district = explode(', ', $placemarks[$i]->ExtendedData->Data[6]->value);
            $subDistrict = explode(', ', $placemarks[$i]->ExtendedData->Data[7]->value);
            $description = explode(', ', $placemarks[$i]->ExtendedData->Data[9]->value);
            $distance = calcualteDistance(13.839276, 100.5690375, $lat, $long);

            $array = [
                "name_station" => $name_station,
                "description" => $description,
                "lat" => $lat,
                "long" => $long,
                "phone" => '-',
                "service_day" => $serviceDay,
                "weekday_service" => $open.'-'.$close,
                "sat_service" => $open.'-'.$close,
                "sun_service" => $open.'-'.$close,
                "holidy_service" => $open.'-'.$close,
                "sub_holidy_service" => $open.'-'.$close,
                "id" => $shopId,
                "distance" => $distance,
                "sub_district" => $subDistrict,
                "district" => $district,
                "province" => $province
            ];

            array_push($result, $array);
        }

        return $result;
    }

    $kmlShopFile = getenv('SHOP_URL');
    $kmlPopFile = getenv('POP_URL');

    $shop = mapData($kmlShopFile);
    $pop = mapData($kmlPopFile);

    $result = array_merge($shop, $pop);
    usort($result, function ($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });

    echo "<h3>POP และ SHOP ทั้งหมด ".sizeof($result)." ร้าน (เรียงจากระยะใกล้ไปไกล)</h3>";

    var_dump($result);

?>