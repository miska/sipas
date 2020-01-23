<?php

namespace App\Presenters;

use Nette;

class PastePresenter extends Nette\Application\UI\Presenter {
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
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
}
