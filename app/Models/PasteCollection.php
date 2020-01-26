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

    public function getPaste(string $pid) {
        $paste = $this->database->table('pastes')->get($pid);
        if($paste) {
            $paste = $paste->toArray();
            $paste['data'] = $this->database->table('paste_datas')->get($pid)->data;
        }
        return $paste;
    }

    private function cleanup() {
        $tme = time();
        $this->database->query('DELETE FROM paste_datas WHERE pid in (SELECT pid FROM pastes WHERE expire < ?)', $tme);
        $this->database->query('DELETE FROM pastes WHERE expire < ?', $tme);
    }

    private function getFreePid() {
        $paste = True;
        while($paste) {
            $pid = sprintf("%4x%4x", rand(0,pow(2,32)),time());
            $paste = $this->database->table('pastes')->get($pid);
        }
        return $pid;
    }

    public function createPaste($data) {
        $pid = $this->getFreePid();
        $this->database->table('pastes')->insert([
            'pid' => $pid,
            'title' => $data->title,
            'author' => $data->author,
            'lang' => $data->lang,
            'private' => $data->private,
            'created' => time(),
            'expire' => ($data->expire == 0) ? 0 : (time() + $data->expire),
            'ip' => trim($_SERVER['REMOTE_ADDR'])
        ]);
        $this->database->table('paste_datas')->insert([
            'pid' => $pid,
            'data' => $data->paste
        ]);
        return $pid;
    }

}

