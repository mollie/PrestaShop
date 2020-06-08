<?php

namespace _PhpScoper5eddef0da618a\Symfony\Component\Cache\Tests\Fixtures;

use _PhpScoper5eddef0da618a\Doctrine\Common\Cache\CacheProvider;
class ArrayCache extends \_PhpScoper5eddef0da618a\Doctrine\Common\Cache\CacheProvider
{
    private $data = [];
    protected function doFetch($id)
    {
        return $this->doContains($id) ? $this->data[$id][0] : \false;
    }
    protected function doContains($id)
    {
        if (!isset($this->data[$id])) {
            return \false;
        }
        $expiry = $this->data[$id][1];
        return !$expiry || \time() < $expiry || !$this->doDelete($id);
    }
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $this->data[$id] = [$data, $lifeTime ? \time() + $lifeTime : \false];
        return \true;
    }
    protected function doDelete($id)
    {
        unset($this->data[$id]);
        return \true;
    }
    protected function doFlush()
    {
        $this->data = [];
        return \true;
    }
    protected function doGetStats()
    {
        return null;
    }
}
