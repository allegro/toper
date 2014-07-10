<?php
/**
 * Created by IntelliJ IDEA.
 * User: horhe
 * Date: 02.07.14
 * Time: 09:56
 */

namespace Toper;


interface ClockInterface
{
    /**
     * @return int - current timestamp
     */
    public function getTime();
} 