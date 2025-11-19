<?php
function formatIndianNumber($number, $decimals = 2) {
    $number = number_format($number, $decimals, '.', '');
    $parts = explode('.', $number);
    $integerPart = $parts[0];
    $decimalPart = isset($parts[1]) ? $parts[1] : str_repeat('0', $decimals);
    
    // Handle negative numbers
    $isNegative = false;
    if ($integerPart[0] === '-') {
        $isNegative = true;
        $integerPart = substr($integerPart, 1);
    }
    
    // Indian system: last 3 digits, then groups of 2
    $lastThree = substr($integerPart, -3);
    $otherNumbers = substr($integerPart, 0, -3);
    
    if ($otherNumbers != '') {
        $lastThree = ',' . $lastThree;
    }
    
    $result = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $otherNumbers) . $lastThree;
    
    if ($isNegative) {
        $result = '-' . $result;
    }
    
    return $result . ($decimals > 0 ? '.' . $decimalPart : '');
}

function formatIndianCurrency($number, $decimals = 2) {
    return '₹' . formatIndianNumber($number, $decimals);
}
?>