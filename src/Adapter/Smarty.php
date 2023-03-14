<?php

declare(strict_types=1);

namespace Mollie\Adapter;

class Smarty
{
    public function assign($tpl_var, $value = null, $nocache = false)
    {
        return \Context::getContext()->smarty->assign($tpl_var, $value, $nocache);
    }
}
