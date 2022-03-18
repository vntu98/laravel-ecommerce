<?php

if (!function_exists('formattedPrice')) {
    function formattedPrice($price)
    {
        return money($price);
    }
}