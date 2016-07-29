<?php

function multi_array_flip($arrayIn, $DesiredKey, $DesiredKey2=false, $OrigKeyName=false)
{
    $ArrayOut = [];

    foreach ($arrayIn as $Key=>$Value)
    {
        // If there is an original key that need to be preserved as data in the new array then do that if requested ($OrigKeyName=true)
        if ($OrigKeyName) $Value[$OrigKeyName] = $Key;
        // Require a string value in the data part of the array that is keyed to $DesiredKey
        if (!is_string($Value[$DesiredKey])) return false;

        // If $DesiredKey2 was specified then assume a multidimensional array is desired and build it
        if (is_string($DesiredKey2))
        {
            // Require a string value in the data part of the array that is keyed to $DesiredKey2
            if (!is_string($Value[$DesiredKey2])) return false;

            // Build NEW multidimensional array
            $ArrayOut[$Value[$DesiredKey]][$Value[$DesiredKey2]] = $Value;
        }

        // Build NEW single dimention array
        else $ArrayOut[$Value[$DesiredKey]] = $Value;
    }

    return $ArrayOut;
}
