<?php

namespace Gandung\Promise;

/**
 * @author Paulus Gandung Prakosa <rvn.plvhx@gmail.com>
 */
interface PromisorInterface
{
    /**
     * Return the promise object.
     *
     * @return PromiseInterface
     */
    public function promise();
}
