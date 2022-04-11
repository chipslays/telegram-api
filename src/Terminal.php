<?php

namespace Telegram;

class Terminal
{
    /**
     * Colorize text.
     *
     * @param string|int $text
     * @return string
     */
    public static function paint($text = '')
    {
        $list = [
            "{reset}" => "\e[0m",
            "{text:black}" => "\e[0;30m",
            "{text:white}" => "\e[1;37m",
            "{text:darkGrey}" => "\e[1;30m",
            "{text:darkGray}" => "\e[1;30m",
            "{text:grey}" => "\e[0;37m",
            "{text:gray}" => "\e[0;37m",
            "{text:darkRed}" => "\e[0;31m",
            "{text:red}" => "\e[1;31m",
            "{text:darkGreen}" => "\e[0;32m",
            "{text:green}" => "\e[1;32m",
            "{text:darkYellow}" => "\e[0;33m",
            "{text:yellow}" => "\e[1;33m",
            "{text:blue}" => "\e[0;34m",
            "{text:darkMagenta}" => "\e[0;35m",
            "{text:magenta}" => "\e[1;35m",
            "{text:darkCyan}" => "\e[0;36m",
            "{text:cyan}" => "\e[1;36m",
            "{bg:black}" => "\e[40m",
            "{bg:red}" => "\e[41m",
            "{bg:green}" => "\e[42m",
            "{bg:yellow}" => "\e[43m",
            "{bg:blue}" => "\e[44m",
            "{bg:magenta}" => "\e[45m",
            "{bg:cyan}" => "\e[46m",
            "{bg:grey}" => "\e[47m",
            "{bg:gray}" => "\e[47m",
        ];

        return strtr($text, $list);
    }

    public static function print($text)
    {
        echo self::paint($text . '{reset}') . PHP_EOL;
    }

    public static function out($text)
    {
        echo self::paint($text) . PHP_EOL;
    }

    /**
     * Ask and get answer.
     *
     * @param string|int $text,
     * @param array $variants Array of variant answers
     * @return string Input text
     */
    public static function ask($text, $variants = ['y', 'N'])
    {
        echo self::print($text . ' {text:yellow}[' . implode('/', $variants) . ']{reset}: ');
        return trim(fgets(STDIN));
    }
}