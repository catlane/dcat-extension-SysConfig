<?php

namespace Catlane\DcatSysConfig;

use Dcat\Admin\Extend\Setting as Form;

class Setting extends Form
{
    public function form()
    {
        $this->disableResetButton();
        $this->disableSubmitButton();
    }
}
