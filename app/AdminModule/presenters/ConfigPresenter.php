<?php

namespace App\AdminModule\Presenters;


use App;
use Tracy\Debugger;


class ConfigPresenter extends BasePresenter
{

    protected function beforeRender()
    {
        $this->template->config = $this->context->parameters;
        Debugger::barDump( 'blaaaaaaaaa');
    }

}
