<?php

namespace App\AdminModule\Presenters;

use App;
use App\Component\Breadcrumb;
use Nette\Forms\Controls;
use Tracy\Debugger;


class DefaultPresenter extends BasePresenter
{

    protected function beforeRender()
    {
        $this->template->config = $this->context->parameters;
    }

}
