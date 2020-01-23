<?php

namespace App\Presenters;

use Nette;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;
use GeSHi;

class PastePresenter extends Nette\Application\UI\Presenter {
    /** @var Nette\Database\Context */
    private $database;
    private $geshi;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    public function startup() {
        parent::startup();
        $this->geshi = new \GeSHi();
    }

    protected function createComponentPasteForm(): Form {
        $form = new Form;
        $form->setRenderer(new BootstrapRenderer);

        $form->addText('title', 'Title:')
             ->setHtmlAttribute('placeholder', 'Name your paste')
		     ->addRule(Form::MAX_LENGTH, 'Your post title has to be shorter than 48 characters!', 48);
        $form->addText('name', 'Author:')
             ->setHtmlAttribute('placeholder', 'Name yourself')
		     ->addRule(Form::MAX_LENGTH, 'Your name has to be shorter than 48 characters!', 48);
        $form->addSelect('lang', 'Language:', $this->geshi->get_supported_languages(true))->setDefaultValue('text');
        $form->addTextArea('paste', 'Paste:')
             ->setRequired();
        $form->addSelect('expire', 'Expire in', [
                30 => "30 Minutes",
                60 => "1 hour",
                360 => "6 Hours",
                720 => "12 Hours",
                1440 => "1 Day",
                10080 => "1 Week",
                40320 => "1 Month",
                151200 => "3 Monts",
                604800 => "1 Year",
                1209600 => "2 Years",
                1814400 => "3 Years",
                0 => "Never"
               ])->setDefaultValue(10080);
        $form->addCheckbox('private', 'Private paste');
        $form->addSubmit('send', 'Paste');

        return $form;
    }


    public function renderShow(int $id): void {
        $paste = $this->database->table('pastes')->get($id);
        $paste_data = $this->database->table('paste_datas')->get($id);
        if (!$paste) {
            $this->error('Paste not found');
        }
        $this->template->paste = $paste;
        $this->template->paste_data = $paste_data;
    }

    public function renderCreate(): void {
        $this->template->languages = $this->geshi->get_supported_languages(true);
    }
}
