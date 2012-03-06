<?php

namespace Liip\MonitorBundle\Check;

interface CheckInterface
{
    function check();
    function getName();
}