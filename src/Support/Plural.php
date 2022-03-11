<?php

namespace Telegram\Support;

class Plural
{
    /**
     * Russian, Ukrainian (not tested), Belorussian (not tested) pluralization.
     *
     * Example: plural(10, ['арбуз', 'арбуза', 'арбузов'])
     * Returns: арбузов
     *
     * @param string|int $n
     * @param array $forms
     * @return string Value from $forms
     */
    public static function rus($n, array $forms)
    {
        return is_float($n)
            ? $forms[1]
            : ($n % 10 == 1 && $n % 100 != 11
                ? $forms[0]
                : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20)
                    ? $forms[1]
                    : $forms[2]));
    }

    /**
     * English pluralization.
     *
     * @param string|int $value
     * @param string $word
     * @return string
     */
    public static function eng($value, string $word)
    {
        $plural = '';
        if ($value > 1) {
            for ($i = 0; $i < strlen($word); $i++) {
                if ($i == strlen($word) - 1) {
                    $plural .= ($word[$i] == 'y') ? 'ies' : (($word[$i] == 's' || $word[$i] == 'x' || $word[$i] == 'z' || $word[$i] == 'ch' || $word[$i] == 'sh') ? $word[$i] . 'es' : $word[$i] . 's');
                } else {
                    $plural .= $word[$i];
                }
            }
            return $plural;
        }
        return $word;
    }
}