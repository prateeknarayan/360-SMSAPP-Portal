<?php 
/**
 * Decodes a string - returns null if failed
 *
 * @param  mixed  $input;
 * @return bool
 * If object then return true
 */
function objectOrStringInput(&$input): bool
{
    if (is_array($input)) {
        $input = json_encode($input);

        return false;
    } elseif (is_object($input)) {
        return true;
    } elseif (! is_string($input)) {
        $input = (string) $input;

        return false;
    }
    $result = json_decode($input);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $input = (string) $input;

        return false;
    }
    $input = $result;

    return true;
}

/**
 * Converts an array to an object at all depths
 *
 * @param  array  $input
 * @return void
 */
function arrayToObject(&$input)
{
    $input = json_decode(json_encode((object) $input));

}