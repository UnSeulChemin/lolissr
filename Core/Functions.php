<?php
namespace App\Core;

class Functions
{
    /**
     * `../` OR `../../`
     * @return string|null
     */
    public static function getPathRedirect(): string|null
    {
        if (str_contains($_GET["p"], "/"))
        {
            $getP = substr_count($_GET["p"], "/");

            if ($getP == 1) { return "../"; }
            else if ($getP == 2) { return "../../"; }
            else { http_response_code(404); self::pathRedirect(); }
        }
        return null;
    }
}