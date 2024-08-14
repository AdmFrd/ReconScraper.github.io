<?php
set_time_limit(0); 

function Get_data($Raw_URL) {
    // Combine the common headers for both requests
    $headers = [
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36"
    ];
    
    $Unit_and_low_price = trim($Raw_URL) . "&sort=price.asc";
    $High_price = trim($Raw_URL) . "&sort=price.desc";

    // Use cURL for improved performance and flexibility
    $page1 = fetchURL($Unit_and_low_price, $headers);
    $page2 = fetchURL($High_price, $headers);

    $soup1 = new DOMDocument();
    @$soup1->loadHTML($page1);
    $soup2 = new DOMDocument();
    @$soup2->loadHTML($page2);

    $Headline_search_results = $soup1->getElementsByTagName("h1");
    $header_result = $Headline_search_results->item(0)->nodeValue;

    if (stripos($header_result, "No Results Found") !== false) {
        $model_year = extractModelYear($Raw_URL);

        $date_taken = get_date();

        return array($model_year, "0", "0", "0", "0", $date_taken);
    } else {
        $match_text = $Headline_search_results[0]->nodeValue;
        $model_year = extractModelYearFromText($match_text);

        $quantity = explode(" ", $match_text);
        $quantity = array_diff($quantity, ['2019', '2020', '2021', '2022']);

        $units = findUnits($quantity);

        $low_car_price = findCarPrice($soup1);
        $top_car_price = findCarPrice($soup2);

        $low_price = str_replace(',', '', price_strip($low_car_price));
        $top_price = str_replace(',', '', price_strip($top_car_price));

        $avrge_price = (floatval($low_price) + floatval($top_price)) / 2;

        $date_taken = get_date();

        if ($units == 0 || $units == "0"|| $low_price == 0|| $low_price == "0"|| $top_price == 0|| $top_price == "0")
        {
            $low_price = 0;
            $top_price = 0;
            $avrge_price = 0;
        }

        return array($model_year, intval($units), $low_price, $top_price, $avrge_price, $date_taken);
    }
}

function fetchURL($url, $headers) {
    // Initialize cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the cURL session and return the result
    $result = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    return $result;
}

function extractModelYear($url) {
    $model_years = ['2019', '2020', '2021', '2022'];

    foreach ($model_years as $year) {
        if (strpos($url, $year) !== false) {
            return $year;
        }
    }

    return "0";
}

function extractModelYearFromText($text) {
    $model_years = ['2019', '2020', '2021', '2022'];

    foreach ($model_years as $year) {
        if (stripos($text, $year) !== false) {
            return $year;
        }
    }

    return "0";
}

function findUnits($quantity) {
    $units = " ";
    foreach ($quantity as $y) {
        if (ctype_digit(str_replace(',','',$y))) {
            $units = $y;
        }
    }

    return str_replace(',','',$units);
}

function findCarPrice($soup) {
    $price_elements = $soup->getElementsByTagName("div");
    $car_price = "Element not found";
    
    foreach ($price_elements as $element) {
        $classAttribute = trim($element->getAttribute("class"));

        if ($classAttribute === "listing__price delta weight--bold" || strpos($classAttribute, "listing__price") !== false) {
            $car_price = $element->nodeValue;
            break;
        }
    }

    return $car_price;
}

function price_strip($price_text) {
    $target_word = "RM";

    // Define a regular expression pattern to find the target word followed by a sequence of digits
    $pattern = '/\b' . preg_quote($target_word, '/') . '\s+([\d,]+)/';

    // Search for the pattern in the text
    preg_match($pattern, $price_text, $matches);

    if (isset($matches[1])) {
        return $matches[1];
    }

    return "0"; // Modify this as needed if you want to handle cases where no match is found
}

function get_date() {
    // Get the current date
    $current_date = new DateTime();

    $formatted_month = $current_date->format('j M y');

    return $formatted_month;
}



?>
