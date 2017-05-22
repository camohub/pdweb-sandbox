<?php

namespace App\AdminModule\Presenters;

use App\Component\Breadcrumb;
use Nette\Forms\Controls;


class DefaultPresenter extends \App\Presenters\BasePresenter
{
    protected function startup()
    {
        parent::startup();
    }


    protected function beforeRender()
    {
        $this->template->config = $this->context->parameters;
    }

}
