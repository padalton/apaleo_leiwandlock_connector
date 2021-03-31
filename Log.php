<?php


class Log
{

    function fatal($message)
    {
        error_log($message);
    }

}