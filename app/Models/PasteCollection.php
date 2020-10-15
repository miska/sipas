<?php

namespace App\Model;

use Nette;

class PasteCollection {
    use Nette\SmartObject;

    /** @var Nette\Database\Connection */
    private $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    public function findPublicPastes(int $limit, int $offset): Nette\Database\ResultSet    {
        return $this->database->query('
            SELECT * FROM pastes
            WHERE private != True
            ORDER BY created DESC
            LIMIT ?
            OFFSET ?',
            $limit, $offset
        );
    }

    public function getPublicPastesCount(): int {
        return $this->database->fetchField('
            SELECT COUNT(*) FROM pastes
            WHERE private != True'
        );
    }
    public function getRawPaste(string $pid) {
        $paste = $this->database->table('paste_datas')->get($pid);
        if($paste) {
            return $paste->data;
        }
        return $paste;
    }

    public function getPaste(string $pid) {
        $paste = $this->database->table('pastes')->get($pid);
        if($paste) {
            $paste = $paste->toArray();
            $paste['data'] = $this->getRawPaste($pid);
            if(!$paste['data'])
                throw new \Exception('Paste data not found!');
            return $paste;
        } else {
            throw new \Exception('Paste not found!');
        }
    }

    public function cleanup() {
        $tme = time();
        $this->database->query('DELETE FROM paste_datas WHERE pid in (SELECT pid FROM pastes WHERE expire < ? AND expire !=0)', $tme);
        $this->database->query('DELETE FROM pastes WHERE expire < ? AND expire != 0', $tme);
    }

    private function getFreePid() {
        $paste = True;
        while($paste) {
            $pid = sprintf("%4x%4x", rand(0,pow(2,32)),time());
            $paste = $this->database->table('pastes')->get($pid);
        }
        return $pid;
    }

    public function isSpam($paste) {
        $len = strlen($paste);
        $links = substr_count($paste, 'http');
        $lines = substr_count($paste, "\n");
        $link_density = $links / ($len + 1);
        $links_per_line = $links / ($lines + 1);
        if(array_key_exists('forbidden_words', SPAM) && SPAM['forbidden_words']) {
            foreach(SPAM['forbidden_words'] as $needle) {
                if(stristr($paste, $needle))
                    return True;
            }
        }
        if((SPAM['density'] != 0 && SPAM['density'] < $link_density) ||
           (SPAM['per_line'] != 0 && SPAM['per_line'] < $links_per_line)) {
            return True;
        }
        return False;
    }

    public function createPaste($data) {
        $lang = $data->lang;
		$title = $data->title;
        if($data->paste_file->hasFile()) {
            $paste = $data->paste_file->getContents();
            if(!$data->title)
                $title = $data->paste_file->getSanitizedName();
            if($lang == 'text' && $data->paste_file->isImage()) {
                $lang = 'image';
                $paste = "data:" . $data->paste_file->getContentType() . ";base64," . base64_encode($paste);
            }
            if($lang == 'text') {
                $lang = get_language_name_from_extension(
                    substr(strrchr($data->paste_file->getName(), '.'), 1));
            }
        }
		if($data->paste_text)
			$paste = $data->paste_text;
        if($this->isSpam($paste))
            throw new \Exception('Your paste looks like a spam!');
        $pid = $this->getFreePid();
        $this->database->table('pastes')->insert([
            'pid' => $pid,
            'title' => $title,
            'author' => $data->author,
            'lang' => $lang,
            'private' => $data->private,
            'created' => time(),
            'expire' => ($data->expire == 0) ? 0 : (time() + $data->expire),
            'ip' => trim($_SERVER['REMOTE_ADDR'])
        ]);
        $this->database->table('paste_datas')->insert([
            'pid' => $pid,
            'data' => $paste
        ]);
        return $pid;
    }

}

