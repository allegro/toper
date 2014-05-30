<?php

namespace Toper;

interface HostPoolInterface {
    public function getNext();

    public function hasNext();
}