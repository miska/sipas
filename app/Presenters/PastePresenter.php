<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use GeSHi;

use App\Model\PasteCollection;

class PastePresenter extends Nette\Application\UI\Presenter {
    /** @var Nette\Database\Context */
    private $database;
    private $geshi;
    private $pasteCollection;

    public function __construct(PasteCollection $pasteCollection) {
        $this->pasteCollection = $pasteCollection;
    }

    public function startup() {
        parent::startup();
        $this->geshi = new \GeSHi();
        $this->geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
    }

    public function beforeRender() {
        $this->template->addFilter('ago', function ($tme) {
	    $diff = time() - $tme;
	    if($diff < 120) {
		return "$diff seconds ago";
	    }
	    $diff = round($diff / 60);
	    if($diff < 120) {
		return "$diff minutes ago";
	    }
	    $diff = round($diff / 60);
	    if($diff < 48) {
		return "$diff hours ago";
	    }
	    $diff = round($diff / 24);
	    if($diff < 400) {
		return "$diff days ago";
	    }
	    $diff = round($diff / 365.25);
	    return "$diff years ago";
        });
    }

    private function bootstrapForm(Form $form): void {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
        $renderer->wrappers['pair']['.error'] = 'has-danger';
        $renderer->wrappers['control']['container'] = 'div class="col-sm-9"';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 col-form-label"';
        $renderer->wrappers['control']['description'] = 'span class=form-text';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=form-control-feedback';
        $renderer->wrappers['control']['.error'] = 'is-invalid';
        $renderer->wrappers['control']['.error'] = 'is-invalid';

        foreach ($form->getControls() as $control) {
            $type = $control->getOption('type');
            if ($type === 'button') {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary float-right' : 'btn btn-secondary');
                $usedPrimary = true;

            } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                $control->getControlPrototype()->addClass('form-control w-100');
            } elseif ($type === 'file') {
                $control->getControlPrototype()->addClass('form-control-file w-100');

            } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                if ($control instanceof Nette\Forms\Controls\Checkbox) {
                    $control->getLabelPrototype()->addClass('form-check-label');
                } else {
                    $control->getItemLabelPrototype()->addClass('form-check-label');
                }
                $control->getControlPrototype()->addClass('form-check-input');
                $control->getSeparatorPrototype()->setName('div')->addClass('form-check');
            }
        }
    }

    public function createPasteFormSucceeded(Nette\Application\UI\Form $form, \stdClass $values): void {
        $pid = null;
        try {
            $pid = $this->pasteCollection->createPaste($values);
        } catch (\Exception $e) {
            $this->flashMessage($e->getMessage(), 'warning');
        }
        if($pid) {
            $this->flashMessage('Your paste was successfully created!','success');
            $this->redirect('Paste:Show', $pid);
        } else {
            $this->redirect('Paste:Create');
        }
    }

    protected function createComponentPasteForm(): Form {
        $form = new Form;

        $form->addText('title', 'Title:')
             ->setHtmlAttribute('placeholder', 'Name your paste')
		     ->addRule(Form::MAX_LENGTH, 'Your post title has to be shorter than 48 characters!', 48);
        $form->addText('author', 'Author:')
             ->setHtmlAttribute('placeholder', 'Name yourself')
		     ->addRule(Form::MAX_LENGTH, 'Your name has to be shorter than 48 characters!', 48);
        $form->addSelect('lang', 'Language:', $this->geshi->get_supported_languages(true))->setDefaultValue('text');
        $form->addTextArea('paste', 'Paste:')
             ->setRequired()->getControlPrototype()->setRows('12');
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
        $form->addProtection('Time limit run out, please refresh the page first!');
        $form->addSubmit('send', 'Paste');
        $this->bootstrapForm($form);
        $form->onSuccess[] = [$this, 'createPasteFormSucceeded'];

        return $form;
    }

    public function renderCron(): void {
        $paste = $this->pasteCollection->cleanup();
        $this->sendJson(['run' => True]);
    }

    public function renderShowRaw(string $id): void {
        $paste = $this->pasteCollection->getRawPaste($id);
        if (!$paste) {
            $this->error('Paste not found!');
        }
        $this->sendResponse(new Nette\Application\Responses\TextResponse($paste));
    }

    public function renderShow(string $id): void {
        try {
            $paste = $this->pasteCollection->getPaste($id);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $geshi = new GeSHi($paste['data'], $paste['lang']);
        $paste['geshi'] = $geshi->parse_code();
        $this->template->extra_css = $geshi->get_stylesheet();
        $this->template->paste = $paste;
    }

    public function renderList(int $page = 1): void {
        $pastesCount = $this->pasteCollection->getPublicPastesCount();

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($pastesCount); // total articles count
        $paginator->setItemsPerPage(30); // items per page
        $paginator->setPage($page); // actual page number

        $this->template->pastes = $this->pasteCollection->findPublicPastes($paginator->getLength(), $paginator->getOffset());
        $this->template->paginator = $paginator;
        $this->template->langs = $this->geshi->get_supported_languages(true);
    }

    public function renderCreate(): void {
        $this->template->languages = $this->geshi->get_supported_languages(true);
    }
}
