<?php
/**
 * Print line
 *
 * @param string $line
 * @param mixed ...$args
 * @return void
 */
function printLine(string $line, ...$args)
{
    printf($line . "\n", ...$args);
}

/**
 * @param $file
 * @param $process_sections
 * @param $scanner_mode
 * @return array|false|mixed
 */
function parse_ini_file_multi($file, $process_sections = false, $scanner_mode = INI_SCANNER_NORMAL) {
    $explode_str = '.';
    $escape_char = "'";
    $sequences_char = "\\";

    // load ini file the normal way
    $data = parse_ini_file($file, $process_sections, $scanner_mode);
    if (!$process_sections) {
        $data = array($data);
    }
    foreach ($data as $section_key => $section) {
        // loop inside the section
        foreach ($section as $key => $value) {
            if (strpos($key, $explode_str)) {
                if (substr($key, 0, 1) !== $escape_char) {
                    // key has a dot. Explode on it, then parse each subkeys
                    // and set value at the right place thanks to references
                    $sub_keys = explode($explode_str, $key);
                    $subs =& $data[$section_key];
                    foreach ($sub_keys as $sub_key) {
                        if (!isset($subs[$sub_key])) {
                            $subs[$sub_key] = [];
                        }
                        $subs =& $subs[$sub_key];
                    }
                    // set the value at the right place
                    $subs = $value;
                    // unset the dotted key, we don't need it anymore
                    unset($data[$section_key][$key]);
                }
                // we have escaped the key, so we keep dots as they are
                else {
                    $new_key = trim($key, $escape_char);
                    $data[$section_key][$new_key] = $value;
                    unset($data[$section_key][$key]);
                }
            }
        }
    }
    if (!$process_sections) {
        $data = $data[0];
    }
    return $data;
}