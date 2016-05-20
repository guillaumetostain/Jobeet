<?php
namespace Ens\TostainGuillaumeBundle\Utils;
/**
 * Created by PhpStorm.
 * User: gtostain
 * Date: 20/05/2016
 * Time: 15:40
 */
class Jobeet
{
    static public function slugify($text)
    {
        // replace all non letters or digits by -
        $text = preg_replace('/\W+/', '-', $text);

        // trim and lowercase
        $text = strtolower(trim($text, '-'));

        return $text;
    }
}